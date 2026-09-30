<?php

namespace Tests\Feature;

use App\Models\LearningActivity;
use App\Models\LearningCategory;
use App\Models\LearningItem;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PlatformTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $user = User::factory()->create(['role' => 'parent']);
        $child = $user->children()->create(['name' => 'Mia', 'learning_level' => 'level-2']);
        $category = LearningCategory::create(['name' => 'Numbers', 'slug' => 'numbers']);
        $lesson = $category->lessons()->create(['title' => 'Count', 'slug' => 'count']);
        $item = $lesson->items()->create(['title' => 'One', 'display_value' => '1']);

        return [$user, $child, $category, $lesson, $item];
    }

    public function test_parent_login_and_duplicate_registration(): void
    {
        $user = User::factory()->create(['password' => 'password123']);
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $user->email, 'password' => 'password123'])->assertRedirect('/parent/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/');
        $this->post('/register', ['name' => 'Other', 'email' => $user->email, 'password' => 'password123', 'password_confirmation' => 'password123'])->assertSessionHasErrors('email');
    }

    public function test_child_creation_edit_validation_and_ownership(): void
    {
        [$user,$child] = $this->fixture();
        $this->actingAs($user)->post('/parent/children', ['name' => 'Ava', 'learning_level' => 'level-1', 'date_of_birth' => '2023-01-01', 'avatar' => 'fox'])->assertRedirect('/parent/dashboard');
        $this->assertDatabaseHas('child_profiles', ['name' => 'Ava', 'user_id' => $user->id]);
        $this->put('/parent/children/'.$child->id, ['name' => 'Mia', 'learning_level' => 'level-3'])->assertRedirect();
        $this->post('/parent/children', ['name' => 'Ava', 'learning_level' => 'invalid'])->assertSessionHasErrors('learning_level');
        $other = User::factory()->create();
        $this->actingAs($other)->get('/parent/children/'.$child->id)->assertForbidden();
        $this->put('/parent/children/'.$child->id, ['name' => 'Stolen', 'learning_level' => 'level-1'])->assertForbidden();
        $this->get('/parent/children/'.$child->id.'/progress')->assertForbidden();
    }

    public function test_lessons_cannot_be_accessed_or_completed_by_another_parent(): void
    {
        [$user,$child,$category,$lesson] = $this->fixture();
        $this->actingAs(User::factory()->create())->get("/learn/$child->id/lessons/$lesson->id")->assertForbidden();
        $this->post("/learn/$child->id/lessons/$lesson->id/complete")->assertForbidden();
        $this->assertDatabaseCount('child_progress', 0);
    }

    public function test_opening_does_not_complete_and_completion_is_idempotent(): void
    {
        [$user,$child,$category,$lesson] = $this->fixture();
        $this->actingAs($user)->get("/learn/$child->id/lessons/$lesson->id")->assertOk();
        $this->assertDatabaseCount('child_progress', 0);
        for ($i = 0; $i < 2; $i++) {
            $this->post("/learn/$child->id/lessons/$lesson->id/complete")->assertRedirect();
        }
        $this->assertDatabaseCount('child_progress', 1);
        $this->assertDatabaseCount('child_stars', 1);
        $this->assertDatabaseHas('child_progress', ['child_profile_id' => $child->id, 'completion_percentage' => 100]);
        $this->assertDatabaseHas('learning_streaks', ['child_profile_id' => $child->id, 'current_streak' => 1]);
        $this->get('/parent/dashboard')->assertOk()->assertSee('Mia');
        $this->get("/parent/children/$child->id/progress")->assertOk()->assertSee('Count');
    }

    public function test_bookmark_does_not_award_stars_or_overwrite_completion(): void
    {
        [$user,$child,$category,$lesson] = $this->fixture();
        $this->actingAs($user)->postJson("/learn/$child->id/lessons/$lesson->id/bookmark", ['index' => 0])->assertOk();
        $this->assertDatabaseCount('child_stars', 0);
        $this->post("/learn/$child->id/lessons/$lesson->id/complete");
        $this->postJson("/learn/$child->id/lessons/$lesson->id/bookmark", ['index' => 0])->assertOk();
        $this->assertDatabaseHas('child_progress', ['status' => 'completed', 'completion_percentage' => 100]);
        $this->postJson("/learn/$child->id/lessons/$lesson->id/bookmark", ['index' => 5])->assertUnprocessable();
    }

    public function test_disabled_content_and_accounts_are_protected(): void
    {
        [$user,$child,$category,$lesson] = $this->fixture();
        $category->update(['is_active' => false]);
        $this->actingAs($user)->get("/learn/$child->id/categories/$category->id")->assertNotFound();
        $this->get("/learn/$child->id/lessons/$lesson->id")->assertNotFound();
        $this->post("/learn/$child->id/lessons/$lesson->id/complete")->assertNotFound();
        $user->is_active = false;
        $user->save();
        $this->get('/parent/dashboard')->assertForbidden();
    }

    public function test_game_scoring_is_computed_on_server_and_nonce_is_single_use(): void
    {
        [$user,$child,$category,$lesson] = $this->fixture();
        $activity = $lesson->activities()->create(['title' => 'Pick one', 'activity_type' => 'matching', 'configuration' => ['questions' => [['prompt' => 'One?', 'options' => ['1', '2'], 'answer' => '1', 'mode' => 'choice']]]]);
        $response = $this->actingAs($user)->get("/learn/$child->id/activities/$activity->id")->assertOk();
        $token = $response->viewData('token');
        $this->postJson("/learn/$child->id/activities/$activity->id/complete", ['token' => $token, 'answers' => ['2'], 'score' => 100])->assertOk()->assertJson(['score' => 0, 'correct' => 0]);
        $this->assertDatabaseHas('activity_attempts', ['score' => 0, 'correct_answers' => 0]);
        $this->postJson("/learn/$child->id/activities/$activity->id/complete", ['token' => $token, 'answers' => ['1']])->assertStatus(419);
        $this->assertDatabaseCount('activity_attempts', 1);
    }

    public function test_game_session_is_bound_to_child(): void
    {
        [$user,$child,$category,$lesson] = $this->fixture();
        $activity = $lesson->activities()->create(['title' => 'Pick', 'activity_type' => 'matching']);
        $token = $this->actingAs($user)->get("/learn/$child->id/activities/$activity->id")->viewData('token');
        $other = $user->children()->create(['name' => 'Other']);
        $this->postJson("/learn/$other->id/activities/$activity->id/complete", ['token' => $token, 'answers' => ['One']])->assertStatus(419);
    }

    public function test_tracing_needs_drawing_and_matching_item(): void
    {
        [$user,$child,$category,$lesson,$item] = $this->fixture();
        $activity = $lesson->activities()->create(['title' => 'Trace', 'activity_type' => 'tracing']);
        $url = "/learn/$child->id/activities/$activity->id/complete";
        $this->actingAs($user)->postJson($url, ['item_id' => $item->id, 'points' => []])->assertUnprocessable();
        $this->postJson($url, ['item_id' => $item->id, 'points' => array_fill(0, 10, [0.1, 0.1])])->assertUnprocessable();
        $points = [];
        for ($i = 0; $i < 10; $i++) {
            $points[] = [$i / 10, 0.5];
        }
        $this->postJson($url, ['item_id' => $item->id, 'points' => $points])->assertOk();
        $this->postJson($url, ['item_id' => $item->id, 'points' => $points])->assertOk();
        $this->assertDatabaseCount('child_stars', 1);
    }

    public function test_reset_requires_parent_password(): void
    {
        [$user,$child,$category,$lesson] = $this->fixture();
        $user->password = 'password123';
        $user->save();
        $this->actingAs($user)->post("/learn/$child->id/lessons/$lesson->id/complete");
        $this->delete("/parent/children/$child->id/progress", ['password' => 'wrong'])->assertSessionHasErrors('password');
        $this->assertDatabaseCount('child_stars', 1);
        $this->delete("/parent/children/$child->id/progress", ['password' => 'password123'])->assertRedirect();
        $this->assertDatabaseCount('child_stars', 0);
        $this->assertDatabaseCount('child_progress', 0);
    }

    public function test_admin_category_and_lesson_management_and_authorization(): void
    {
        [$user,$child,$category,$lesson] = $this->fixture();
        $this->actingAs($user)->get('/admin/categories')->assertForbidden();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post('/admin/categories', ['name' => 'New category', 'slug' => 'new-category', 'sort_order' => 1, 'is_active' => 1])->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('learning_categories', ['slug' => 'new-category']);
        $this->post('/admin/lessons', ['title' => 'New lesson', 'slug' => 'new-lesson', 'learning_category_id' => $category->id, 'content_type' => 'learning', 'age_min' => 1, 'age_max' => 5, 'sort_order' => 1, 'is_active' => 1])->assertRedirect('/admin/lessons');
        $this->assertDatabaseHas('lessons', ['slug' => 'new-lesson']);
        $this->get('/admin/dashboard')->assertOk();
        foreach (['categories', 'lessons', 'learning-items', 'games', 'activities', 'users', 'children'] as $resource) {
            $this->get('/admin/'.$resource)->assertOk();
        }
        $this->get('/admin/categories/create')->assertOk();
        $this->get('/admin/games/create')->assertOk();
        $this->delete('/admin/lessons/'.$lesson->id)->assertRedirect();
        $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);
    }

    public function test_admin_can_deactivate_parent_but_cannot_escalate_role(): void
    {
        $parent = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->put('/admin/users/'.$parent->id, ['name' => $parent->name, 'email' => $parent->email, 'is_active' => 0, 'role' => 'admin'])->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $parent->id, 'is_active' => false, 'role' => 'parent']);
    }

    public function test_unsafe_uploads_and_invalid_game_answers_are_rejected(): void
    {
        [$user,$child,$category,$lesson] = $this->fixture();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post('/admin/categories', ['name' => 'Bad', 'slug' => 'bad', 'sort_order' => 1, 'is_active' => 1, 'image' => UploadedFile::fake()->create('payload.php', 1, 'application/x-php')])->assertSessionHasErrors('image');
        $this->post('/admin/games', ['title' => 'Bad game', 'lesson_id' => $lesson->id, 'activity_type' => 'matching', 'instructions' => 'Choose', 'difficulty' => 'easy', 'is_active' => 1, 'configuration' => ['questions' => [['prompt' => 'Pick', 'mode' => 'choice', 'options' => ['A', 'B'], 'answer' => 'C']]]])->assertSessionHasErrors('configuration');
    }

    public function test_seed_catalog_is_complete_and_repeatable(): void
    {
        $this->seed();
        $counts = [LearningCategory::count(), Lesson::count(), LearningItem::count(), LearningActivity::count()];
        $this->seed();
        $this->assertSame($counts, [LearningCategory::count(), Lesson::count(), LearningItem::count(), LearningActivity::count()]);
        $this->assertSame(26, Lesson::where('slug', 'english-alphabet-lesson')->first()->items()->count());
        $this->assertSame(100, Lesson::where('slug', 'numbers-lesson')->first()->items()->count());
        $this->assertSame(12, LearningCategory::count());
        $this->assertDatabaseCount('users', 0);
        $user = User::factory()->create();
        $child = $user->children()->create(['name' => 'Explorer']);
        $this->actingAs($user);
        foreach (Lesson::all() as $lesson) {
            $this->get("/learn/$child->id/lessons/$lesson->id")->assertOk();
        }
        $this->get("/learn/$child->id")->assertOk();
        $this->get("/learn/$child->id/games")->assertOk();
    }
}

<?php

namespace Tests\Feature;

use App\Models\ChildProfile;
use App\Models\LearningCategory;
use App\Models\LearningItem;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningAppTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Let\'s Learn and Play!');
    }

    public function test_parent_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Parent',
            'email' => 'parent@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/parent/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => 'parent@example.com',
        ]);
    }

    public function test_parent_dashboard_requires_authentication(): void
    {
        $this->get('/parent/dashboard')
            ->assertRedirect('/login');
    }

    public function test_child_lesson_renders_ordered_traceable_learning_items(): void
    {
        $user = User::factory()->create();
        $child = ChildProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Sam',
        ]);
        $category = LearningCategory::query()->create([
            'name' => 'Numbers',
            'slug' => 'numbers-test',
        ]);
        $lesson = Lesson::query()->create([
            'learning_category_id' => $category->id,
            'title' => 'Number Fun',
            'slug' => 'number-fun-test',
        ]);

        LearningItem::query()->create([
            'lesson_id' => $lesson->id,
            'title' => 'Two',
            'display_value' => '2',
            'sort_order' => 2,
        ]);
        LearningItem::query()->create([
            'lesson_id' => $lesson->id,
            'title' => 'One',
            'display_value' => '1',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('child.lesson', [$child, $lesson]));

        $response->assertOk()
            ->assertSee('id="traceGuide"', false)
            ->assertSee('id="drawingCanvas"', false)
            ->assertSee('data-practice-value="1"', false)
            ->assertSee('data-practice-value="2"', false)
            ->assertSeeInOrder(['data-practice-value="1"', 'data-practice-value="2"'], false);
    }
}

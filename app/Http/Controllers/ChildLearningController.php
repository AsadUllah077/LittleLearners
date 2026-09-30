<?php

namespace App\Http\Controllers;

use App\Models\ChildProfile;
use App\Models\ChildProgress;
use App\Models\LearningActivity;
use App\Models\LearningCategory;
use App\Models\Lesson;
use App\Services\LearningProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChildLearningController extends Controller
{
    private function available(Lesson $lesson): void
    {
        abort_unless($lesson->is_active && $lesson->category?->is_active, 404);
    }

    public function index(ChildProfile $child)
    {
        $child->load(['streak'])->loadSum('stars', 'stars')->loadCount(['progresses' => fn ($q) => $q->where('status', 'completed')]);
        $categories = LearningCategory::where('is_active', true)->withCount(['lessons' => fn ($q) => $q->where('is_active', true)])->orderBy('sort_order')->get();
        $completed = $child->progresses()->where('status', 'completed')->whereHas('lesson', fn ($q) => $q->where('is_active', true))->with('lesson')->get()->groupBy('lesson.learning_category_id')->map->count();
        $resume = $child->progresses()->whereHas('lesson', fn ($q) => $q->where('is_active', true)->whereHas('category', fn ($c) => $c->where('is_active', true)))->with('lesson')->latest('updated_at')->first();

        return view('child.dashboard', compact('child', 'categories', 'completed', 'resume'));
    }

    public function categories(ChildProfile $child)
    {
        return $this->index($child);
    }

    public function category(ChildProfile $child, LearningCategory $category)
    {
        abort_unless($category->is_active, 404);
        $lessons = $category->lessons()->where('is_active', true)->withCount('items')->with(['activities' => fn ($q) => $q->where('is_active', true)])->orderBy('sort_order')->get();

        return view('child.category', compact('child', 'category', 'lessons'));
    }

    public function lesson(ChildProfile $child, Lesson $lesson)
    {
        $this->available($lesson);
        $lesson->load(['items' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'), 'activities' => fn ($q) => $q->where('is_active', true)]);
        $position = $child->progresses()->where('lesson_id', $lesson->id)->value('last_item_index') ?? 0;

        return view('child.lesson', compact('child', 'lesson', 'position'));
    }

    public function bookmark(Request $request, ChildProfile $child, Lesson $lesson)
    {
        $this->available($lesson);
        $data = $request->validate(['index' => 'required|integer|min:0|max:'.max(0, $lesson->items()->count() - 1)]);
        DB::transaction(function () use ($child, $lesson, $data) {
            ChildProfile::lockForUpdate()->findOrFail($child->id);
            $progress = ChildProgress::firstOrNew(['child_profile_id' => $child->id, 'lesson_id' => $lesson->id]);
            $progress->last_item_index = $data['index'];
            if ($progress->status !== 'completed') {
                $progress->status = 'in_progress';
                $progress->completion_percentage = min(99, round(100 * $data['index'] / max(1, $lesson->items()->count())));
            }
            $progress->save();
        });

        return response()->json(['saved' => true]);
    }

    public function completeLesson(ChildProfile $child, Lesson $lesson, LearningProgressService $service)
    {
        $this->available($lesson);
        $service->complete($child, $lesson);

        return back()->with('success', 'Wonderful! Your lesson star is saved.');
    }

    public function games(ChildProfile $child)
    {
        $activities = LearningActivity::where('is_active', true)->whereHas('lesson', fn ($q) => $q->where('is_active', true)->whereHas('category', fn ($c) => $c->where('is_active', true)))->with('lesson.category')->paginate(18);

        return view('games.index', compact('child', 'activities'));
    }
}

<?php

namespace App\Services;

use App\Models\ActivityAttempt;
use App\Models\ChildProfile;
use App\Models\ChildProgress;
use App\Models\LearningActivity;
use App\Models\Lesson;
use Illuminate\Support\Facades\DB;

class LearningProgressService
{
    public function complete(ChildProfile $child, Lesson $lesson): void
    {
        DB::transaction(function () use ($child, $lesson) {
            $child = ChildProfile::lockForUpdate()->findOrFail($child->id);
            $progress = ChildProgress::firstOrNew(['child_profile_id' => $child->id, 'lesson_id' => $lesson->id]);
            if ($progress->status !== 'completed') {
                $progress->fill(['status' => 'completed', 'completion_percentage' => 100, 'completed_at' => now()])->save();
            }
            app(RewardService::class)->award($child, 'lesson', $lesson->id, 1, 'Completed '.$lesson->title);
        });
    }

    public function attempt(ChildProfile $child, LearningActivity $activity, int $correct, int $total): ActivityAttempt
    {
        return DB::transaction(function () use ($child, $activity, $correct, $total) {
            $child = ChildProfile::lockForUpdate()->findOrFail($child->id);
            $attempt = ActivityAttempt::create(['child_profile_id' => $child->id, 'learning_activity_id' => $activity->id, 'score' => round(100 * $correct / $total), 'total_questions' => $total, 'correct_answers' => $correct, 'completed_at' => now()]);
            // Replays earn one practice reward per activity per day, not per request.
            app(RewardService::class)->award($child, 'activity:'.today()->toDateString(), $activity->id, 1, 'Practiced '.$activity->title);

            return $attempt;
        });
    }
}

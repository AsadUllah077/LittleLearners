<?php

namespace App\Services;

use App\Models\ChildProfile;
use App\Models\ChildReward;
use App\Models\ChildStar;
use App\Models\LearningStreak;
use App\Models\Lesson;
use App\Models\Reward;

class RewardService
{
    // Caller holds the child row lock inside a transaction, serializing all rewards.
    public function award(ChildProfile $child, string $type, int $id, int $stars, string $reason): void
    {
        ChildStar::firstOrCreate(['child_profile_id' => $child->id, 'reference_type' => $type, 'reference_id' => $id], ['stars' => $stars, 'reason' => $reason]);
        $total = $child->stars()->sum('stars');
        foreach (Reward::where('star_requirement', '<=', $total)->get() as $reward) {
            if ($reward->name === 'First Lesson' && ! $child->progresses()->where('status', 'completed')->exists()) {
                continue;
            }
            ChildReward::firstOrCreate(['child_profile_id' => $child->id, 'reward_id' => $reward->id], ['earned_at' => now()]);
        }
        foreach (['english-alphabet' => 'Alphabet Explorer', 'numbers' => 'Number Explorer', 'colors' => 'Color Master', 'shapes' => 'Shape Explorer'] as $slug => $name) {
            $lessons = Lesson::where('is_active', true)->whereHas('category', fn ($q) => $q->where('slug', $slug)->where('is_active', true))->pluck('id');
            if ($lessons->isNotEmpty() && $child->progresses()->where('status', 'completed')->whereIn('lesson_id', $lessons)->count() === $lessons->count()) {
                $reward = Reward::firstOrCreate(['name' => $name], ['description' => 'Explored all '.$slug.' lessons.', 'icon' => '⭐', 'star_requirement' => 1000000]);
                ChildReward::firstOrCreate(['child_profile_id' => $child->id, 'reward_id' => $reward->id], ['earned_at' => now()]);
            }
        }
        $streak = LearningStreak::firstOrNew(['child_profile_id' => $child->id]);
        if (! $streak->last_activity_date?->isToday()) {
            $streak->current_streak = $streak->last_activity_date?->isYesterday() ? $streak->current_streak + 1 : 1;
            $streak->longest_streak = max($streak->longest_streak ?? 0, $streak->current_streak);
            $streak->last_activity_date = today();
            $streak->save();
        }
    }
}

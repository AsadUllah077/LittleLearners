<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningStreak extends Model
{
    protected $fillable = [
        'child_profile_id',
        'current_streak',
        'longest_streak',
        'last_activity_date',
    ];

    protected $casts = [
        'last_activity_date' => 'date',
    ];

    public function child(): BelongsTo
    {
        return $this->belongsTo(ChildProfile::class, 'child_profile_id');
    }
}

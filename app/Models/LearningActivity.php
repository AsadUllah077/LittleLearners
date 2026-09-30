<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningActivity extends Model
{
    protected $fillable = [
        'lesson_id',
        'title',
        'activity_type',
        'instructions',
        'difficulty',
        'configuration',
        'is_active',
    ];

    protected $casts = [
        'configuration' => 'array',
    ];

    public function attempts()
    {
        return $this->hasMany(ActivityAttempt::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}

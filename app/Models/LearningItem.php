<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningItem extends Model
{
    protected $fillable = [
        'lesson_id',
        'title',
        'display_value',
        'lowercase_value',
        'description',
        'image',
        'audio',
        'example_text',
        'sort_order',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}

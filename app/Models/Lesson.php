<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = [
        'learning_category_id',
        'title',
        'slug',
        'description',
        'content_type',
        'age_min',
        'age_max',
        'image',
        'audio',
        'sort_order',
        'is_active',
    ];

    public function progresses()
    {
        return $this->hasMany(ChildProgress::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LearningCategory::class, 'learning_category_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LearningItem::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LearningActivity::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'icon',
        'sort_order',
        'is_active',
    ];

    public function completedProgress()
    {
        return $this->hasManyThrough(ChildProgress::class, Lesson::class)->where('status', 'completed');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }
}

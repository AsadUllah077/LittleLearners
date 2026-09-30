<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChildProfile extends Model
{
    protected $fillable = ['user_id', 'name', 'date_of_birth', 'avatar', 'learning_level'];

    protected $casts = ['date_of_birth' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function progresses()
    {
        return $this->hasMany(ChildProgress::class);
    }

    public function stars()
    {
        return $this->hasMany(ChildStar::class);
    }

    public function attempts()
    {
        return $this->hasMany(ActivityAttempt::class);
    }

    public function earnedRewards()
    {
        return $this->hasMany(ChildReward::class);
    }

    public function streak()
    {
        return $this->hasOne(LearningStreak::class);
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth?->age;
    }

    public function getCurrentStreakAttribute()
    {
        return $this->streak?->last_activity_date?->gte(today()->subDay()) ? $this->streak->current_streak : 0;
    }
}

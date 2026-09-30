<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildReward extends Model
{
    protected $fillable = [
        'child_profile_id',
        'reward_id',
        'earned_at',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
    ];

    public function child(): BelongsTo
    {
        return $this->belongsTo(ChildProfile::class, 'child_profile_id');
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    public function earnedBy()
    {
        return $this->hasMany(ChildReward::class);
    }

    protected $fillable = [
        'name',
        'description',
        'icon',
        'star_requirement',
    ];
}

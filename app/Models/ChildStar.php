<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildStar extends Model
{
    protected $fillable = [
        'child_profile_id',
        'stars',
        'reason',
        'reference_type',
        'reference_id',
    ];

    public function child(): BelongsTo
    {
        return $this->belongsTo(ChildProfile::class, 'child_profile_id');
    }
}

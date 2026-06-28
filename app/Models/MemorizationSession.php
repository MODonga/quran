<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemorizationSession extends Model
{
    protected $fillable = [
        'user_id',
        'session_date',
        'review_completed',
        'memorization_unlocked',
    ];

    protected $casts = [
        'session_date' => 'date',
        'review_completed' => 'boolean',
        'memorization_unlocked' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

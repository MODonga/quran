<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProgress extends Model
{
    protected $table = 'user_progress';

    protected $fillable = [
        'user_id',
        'ayah_id',
        'status',
        'last_review',
        'next_review',
        'success_count',
        'fail_count',
    ];

    protected $casts = [
        'last_review' => 'datetime',
        'next_review' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ayah(): BelongsTo
    {
        return $this->belongsTo(Ayah::class);
    }
}

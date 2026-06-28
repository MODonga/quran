<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewSchedule extends Model
{
    protected $table = 'review_schedule';

    protected $fillable = [
        'user_id',
        'ayah_id',
        'scheduled_at',
        'interval',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ayah(): BelongsTo
    {
        return $this->belongsTo(Ayah::class);
    }

    /**
     * Reschedule the review based on success or failure.
     */
    public function reschedule(bool $success)
    {
        if ($success) {
            // Success: Double the interval
            $currentInterval = $this->interval < 1 ? 1 : $this->interval;
            $nextInterval = $currentInterval * 2;

            $this->update([
                'interval' => $nextInterval,
                'scheduled_at' => now()->addDays($nextInterval),
                'status' => 'pending', // Pending for the future date
            ]);
        } else {
            // Failure: Reset to 1 day
            $this->update([
                'interval' => 1,
                'scheduled_at' => now()->addDays(1),
                'status' => 'pending',
            ]);
        }
    }
}

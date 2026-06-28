<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HadithReviewSchedule extends Model
{
    protected $fillable = [
        'user_id',
        'hadith_id',
        'scheduled_at',
        'interval',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hadith()
    {
        return $this->belongsTo(Hadith::class);
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
                'status' => 'pending', 
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

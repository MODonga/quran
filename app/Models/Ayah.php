<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ayah extends Model
{
    protected $fillable = [
        'surah_id',
        'ayah_number',
        'text_uthmani',
        'text_simple',
        'juz',
        'hizb',
        'page',
    ];

    public function surah(): BelongsTo
    {
        return $this->belongsTo(Surah::class);
    }

    /**
     * Scope for ayahs due for review according to spaced repetition logic.
     */
    public function scopeDueForReview($query, $userId)
    {
        return $query->whereHas('userProgress', function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->where('next_review', '<=', now());
        });
    }

    /**
     * Relationship to user progress.
     */
    public function userProgress(): HasMany
    {
        return $this->hasMany(UserProgress::class);
    }
}

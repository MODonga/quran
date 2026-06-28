<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = [
        'ayah_id',
        'prompt',
        'options',
        'correct_answer',
        'type',
    ];

    public function ayah(): BelongsTo
    {
        return $this->belongsTo(Ayah::class);
    }
}

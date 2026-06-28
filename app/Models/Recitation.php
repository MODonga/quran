<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reciter_id',
        'surah_id',
        'ayah_number',
        'audio_url',
    ];

    public function reciter()
    {
        return $this->belongsTo(Reciter::class);
    }

    public function surah()
    {
        return $this->belongsTo(Surah::class);
    }
}

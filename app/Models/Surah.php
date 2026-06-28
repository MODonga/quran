<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Surah extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'ayah_count',
        'revelation_type',
        'revelation_order',
    ];

    public function ayahs(): HasMany
    {
        return $this->hasMany(Ayah::class);
    }
}

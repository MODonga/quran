<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HadithUserAnswer extends Model
{
    protected $fillable = ['user_id', 'hadith_id', 'is_correct'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hadith()
    {
        return $this->belongsTo(Hadith::class);
    }
}

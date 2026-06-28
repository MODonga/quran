<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HadithChapter extends Model
{
    protected $fillable = ['book_id', 'name'];

    public function book()
    {
        return $this->belongsTo(HadithBook::class, 'book_id');
    }

    public function hadiths()
    {
        return $this->hasMany(Hadith::class, 'chapter_id');
    }
}

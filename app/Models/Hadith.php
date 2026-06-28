<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hadith extends Model
{
    protected $fillable = [
        'id',
        'book_id', 
        'chapter_id', 
        'number_in_book', 
        'text', 
        'text_en', 
        'narrator_en', 
        'grade'
    ];

    public function book()
    {
        return $this->belongsTo(HadithBook::class, 'book_id');
    }

    public function chapter()
    {
        return $this->belongsTo(HadithChapter::class, 'chapter_id');
    }
}

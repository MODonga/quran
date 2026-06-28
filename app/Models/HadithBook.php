<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HadithBook extends Model
{
    protected $fillable = ['name', 'author', 'description'];

    public function chapters()
    {
        return $this->hasMany(HadithChapter::class, 'book_id');
    }

    public function hadiths()
    {
        return $this->hasMany(Hadith::class, 'book_id');
    }
}

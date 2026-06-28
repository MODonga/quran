<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reciter extends Model
{
    protected $fillable = [
        'name',
        'server_url',
        'rewaya',
    ];
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'current_level',
        'last_ayah_id',
        'quran_streak',
        'quran_total_days',
        'hadith_streak',
        'hadith_total_days',
        'streak', // Keeping for backward compatibility/reference if needed
        'total_days',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['last_ayah_text', 'last_surah_name'];

    public function lastAyah()
    {
        return $this->belongsTo(Ayah::class, 'last_ayah_id');
    }

    public function getLastAyahTextAttribute()
    {
        return $this->lastAyah ? $this->lastAyah->text_uthmani : null;
    }

    public function getLastSurahNameAttribute()
    {
        return $this->lastAyah && $this->lastAyah->surah ? $this->lastAyah->surah->name_ar : null;
    }
}

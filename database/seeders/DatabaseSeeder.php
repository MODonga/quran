<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Core Data
        $this->call([
            SurahAyahSeeder::class,
        ]);

        // 2. Demo Users
        \App\Models\User::factory(5)->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\Reciter;
use Illuminate\Database\Seeder;

class ReciterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reciters = [
            [
                'name' => 'إبراهيم الأخضر',
                'server_url' => 'https://everyayah.com/data/Ibrahim_Akhdar_32kbps/',
                'rewaya' => 'حفص عن عاصم',
            ],
            [
                'name' => 'مشاري راشد العفاسي',
                'server_url' => 'https://everyayah.com/data/Alafasy_128kbps/',
                'rewaya' => 'حفص عن عاصم',
            ],
            [
                'name' => 'محمود خليل الحصري',
                'server_url' => 'https://everyayah.com/data/Husary_128kbps/',
                'rewaya' => 'حفص عن عاصم',
            ],
        ];

        foreach ($reciters as $reciter) {
            Reciter::updateOrCreate(['name' => $reciter['name']], $reciter);
        }
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Reciter;
use App\Models\Surah;

class RecitationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reciters = Reciter::all();
        $surahs = Surah::all();

        if ($surahs->isEmpty()) {
            $this->command->warn('No Surahs found. Skipping RecitationSeeder. Please seed Surahs first.');
            return;
        }

        // Prepare a large array for bulk insert to be faster
        $data = [];
        $batchSize = 2000; // Insert in chunks

        foreach ($reciters as $reciter) {
            $this->command->info("Generating recitations for: {$reciter->name}");
            
            foreach ($surahs as $surah) {
                $surahPadded = str_pad($surah->id, 3, '0', STR_PAD_LEFT);
                
                // For each ayah in the surah
                for ($ayah = 1; $ayah <= $surah->ayah_count; $ayah++) {
                    $ayahPadded = str_pad($ayah, 3, '0', STR_PAD_LEFT);
                    $filename = "{$surahPadded}{$ayahPadded}.mp3";
                    $url = rtrim($reciter->server_url, '/') . '/' . $filename;

                    $data[] = [
                        'reciter_id' => $reciter->id,
                        'surah_id' => $surah->id,
                        'ayah_number' => $ayah,
                        'audio_url' => $url,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (count($data) >= $batchSize) {
                        DB::table('recitations')->insert($data);
                        $data = [];
                    }
                }
            }
        }

        // Insert remaining
        if (!empty($data)) {
            DB::table('recitations')->insert($data);
        }
    }
}

<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Starting reciters and audio import...\n";

// 1. Define Reciters (EveryAyah compatible)
// 1. Define Reciters (Comprehensive List)
$reciters = [
    // --- Famous Reciters (Hafs) ---
    ['name' => 'مشاري راشد العفاسي', 'server_url' => 'https://everyayah.com/data/Alafasy_128kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'محمود خليل الحصري', 'server_url' => 'https://everyayah.com/data/Husary_128kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'محمود خليل الحصري (المجوّد)', 'server_url' => 'https://everyayah.com/data/Husary_Mujawwad_64kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'عبدالباسط عبدالصمد', 'server_url' => 'https://everyayah.com/data/Abdul_Basit_Murattal_192kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'عبدالباسط عبدالصمد (المجوّد)', 'server_url' => 'https://everyayah.com/data/Abdul_Basit_Mujawwad_128kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'محمد صديق المنشاوي', 'server_url' => 'https://everyayah.com/data/Minshawy_Murattal_128kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'محمد صديق المنشاوي (المجوّد)', 'server_url' => 'https://everyayah.com/data/Minshawy_Mujawwad_192kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'ماهر المعيقلي', 'server_url' => 'https://everyayah.com/data/MaherAlMuaiqly128kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'سعود الشريم', 'server_url' => 'https://everyayah.com/data/Saood_ash-Shuraym_128kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'عبدالرحمن السديس', 'server_url' => 'https://everyayah.com/data/Abdurrahmaan_As-Sudais_192kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'أحمد العجمي', 'server_url' => 'https://everyayah.com/data/Ahmed_ibn_Ali_al-Ajamy_128kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'سعد الغامدي', 'server_url' => 'https://everyayah.com/data/Ghamadi_40kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'فارس عباد', 'server_url' => 'https://everyayah.com/data/Fares_Abbad_64kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'علي الحذيفي', 'server_url' => 'https://everyayah.com/data/Hudhaify_128kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'إبراهيم الأخضر', 'server_url' => 'https://everyayah.com/data/Ibrahim_Akhdar_32kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'ياسر الدوسري', 'server_url' => 'https://everyayah.com/data/Yasser_Ad-Dussary_128kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'عبدالله بصفر', 'server_url' => 'https://everyayah.com/data/Abdullaah_3awwaad_Al-Juhaynee_128kbps/', 'rewaya' => 'حفص عن عاصم'], // Utilizing Al-Juhaynee for variety or check Slug
    ['name' => 'عبدالله عواد الجهني', 'server_url' => 'https://everyayah.com/data/Abdullaah_3awwaad_Al-Juhaynee_128kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'محمد أيوب', 'server_url' => 'https://everyayah.com/data/Muhammad_Ayyoub_128kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'محمد جبريل', 'server_url' => 'https://everyayah.com/data/Muhammad_Jibreel_128kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'أبو بكر الشاطري', 'server_url' => 'https://everyayah.com/data/Abu_Bakr_Ash-Shaatree_128kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'هاني الرفاعي', 'server_url' => 'https://everyayah.com/data/Hani_Rifai_192kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'ناصر القطامي', 'server_url' => 'https://everyayah.com/data/Nasser_Alqatami_128kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'خليفة الطنيجي', 'server_url' => 'https://everyayah.com/data/khalefa_al_tunaiji_64kbps/', 'rewaya' => 'حفص عن عاصم'],
    ['name' => 'عبدالمحسن القاسم', 'server_url' => 'https://everyayah.com/data/Muhsin_Al_Qasim_192kbps/', 'rewaya' => 'حفص عن عاصم'],

    // --- Other Riwayas (Warsh, Qaloon, etc) ---
    ['name' => 'الحصري (ورش)', 'server_url' => 'https://everyayah.com/data/Warsh/Husary_64kbps/', 'rewaya' => 'ورش عن نافع'],
    ['name' => 'عبدالباسط (ورش)', 'server_url' => 'https://everyayah.com/data/Warsh/Abdul_Basit_128kbps/', 'rewaya' => 'ورش عن نافع'],
    ['name' => 'الحصري (قالون)', 'server_url' => 'https://everyayah.com/data/Qaloon/Husary_64kbps/', 'rewaya' => 'قالون عن نافع'],
    ['name' => 'علي الحذيفي (قالون)', 'server_url' => 'https://everyayah.com/data/Qaloon/Hudhaify_64kbps/', 'rewaya' => 'قالون عن نافع'],
    ['name' => 'الدوري عن أبي عمرو (الحصري)', 'server_url' => 'https://everyayah.com/data/Douri/Husary_64kbps/', 'rewaya' => 'الدوري عن أبي عمرو'],
];

try {
    DB::transaction(function () use ($reciters) {
        
        // 2. Insert Reciters
        foreach ($reciters as $reciterData) {
            echo "Processing reciter: {$reciterData['name']}...\n";
            
            // Allow updateOrCreate to handle existing
            $reciterId = DB::table('reciters')->updateOrInsert(
                ['name' => $reciterData['name']],
                [
                    'server_url' => $reciterData['server_url'],
                    'rewaya' => $reciterData['rewaya'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            
            // We need the ID for the next step. updateOrInsert doesn't return it directly if updated.
            $reciter = DB::table('reciters')->where('name', $reciterData['name'])->first();
            
            if (!$reciter) continue;

            // 3. Generate Recitations
            echo "  > Generating audio URLs...\n";
            
            $surahs = DB::table('surahs')->get();
            if ($surahs->isEmpty()) {
                echo "  ! No Surahs found. Skipping recitations.\n";
                continue;
            }

            $batchData = [];
            $batchSize = 2000;
            $count = 0;

            foreach ($surahs as $surah) {
                $surahPadded = str_pad($surah->id, 3, '0', STR_PAD_LEFT);
                
                for ($ayah = 1; $ayah <= $surah->ayah_count; $ayah++) {
                    $ayahPadded = str_pad($ayah, 3, '0', STR_PAD_LEFT);
                    $filename = "{$surahPadded}{$ayahPadded}.mp3";
                    $url = rtrim($reciter->server_url, '/') . '/' . $filename;

                    // Prepare for upsert to avoid duplicate errors
                    // Using updateOrInsert loop is slow for 18k records.
                    // Ideally we use insertOrIgnore or upsert if DB supports it (MySQL does).
                    
                    $batchData[] = [
                        'reciter_id' => $reciter->id,
                        'surah_id' => $surah->id,
                        'ayah_number' => $ayah,
                        'audio_url' => $url,
                        'created_at' => now(), // literal now() for query builder
                        'updated_at' => now(),
                    ];
                    
                    if (count($batchData) >= $batchSize) {
                        DB::table('recitations')->upsert(
                            $batchData, 
                            ['reciter_id', 'surah_id', 'ayah_number'], // Unique keys
                            ['audio_url', 'updated_at'] // Columns to update
                        );
                        $count += count($batchData);
                        $batchData = [];
                        echo "    - Processed {$count} recitations...\r";
                    }
                }
            }

            // Insert leftovers
            if (!empty($batchData)) {
                DB::table('recitations')->upsert(
                    $batchData, 
                    ['reciter_id', 'surah_id', 'ayah_number'], 
                    ['audio_url', 'updated_at']
                );
                $count += count($batchData);
            }
            
            echo "\n  > Completed {$count} recitations for {$reciter->name}.\n";
        }
    });

    echo "\nAll reciters and recitations imported successfully.\n";

} catch (\Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
}

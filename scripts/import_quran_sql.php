<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$sqlPath = __DIR__ . '/../quran-uthmani.sql';

if (!file_exists($sqlPath)) {
    die("quran-uthmani.sql not found in project root.\n");
}

echo "Loading and executing Quran SQL line by line...\n";

try {
    Schema::dropIfExists('quran_text');
    
    $handle = fopen($sqlPath, "r");
    if ($handle) {
        $query = "";
        while (($line = fgets($handle)) !== false) {
            $trimmedLine = trim($line);
            // Skip comments and headers
            if (str_starts_with($trimmedLine, '--') || 
                str_starts_with($trimmedLine, '#') || 
                str_starts_with($trimmedLine, 'CREATE DATABASE') || 
                str_starts_with($trimmedLine, 'USE `quran`')) {
                continue;
            }
            
            $query .= $line;
            
            // If line ends with semicolon, it's the end of a statement
            if (str_ends_with(trim($line), ';')) {
                DB::unprepared($query);
                $query = "";
            }
        }
        fclose($handle);
    }
    
    echo "SQL execution completed. Transferring data...\n";
    
    // Now transfer data from quran_text to ayahs
    // Our ayahs table: surah_id, ayah_number, text_uthmani, juz, hizb, page
    // quran_text table: sura, aya, text
    
    DB::transaction(function () {
        // Populate Surahs first (114)
        // We will fetch names from a standard list since the SQL doesn't have a table for it
        $surahNames = [
            1 => "الفاتحة", 2 => "البقرة", 3 => "آل عمران", 4 => "النساء", 5 => "المائدة",
            6 => "الأنعام", 7 => "الأعراف", 8 => "الأنفال", 9 => "التوبة", 10 => "يونس",
            11 => "هود", 12 => "يوسف", 13 => "الرعد", 14 => "إبراهيم", 15 => "الحجر",
            16 => "النحل", 17 => "الإسراء", 18 => "الكهف", 19 => "مريم", 20 => "طه",
            21 => "الأنبياء", 22 => "الحج", 23 => "المؤمنون", 24 => "النور", 25 => "الفرقان",
            26 => "الشعراء", 27 => "النمل", 28 => "القصص", 29 => "العنكبوت", 30 => "الروم",
            31 => "لقمان", 32 => "السجدة", 33 => "الأحزاب", 34 => "سبأ", 35 => "فاطر",
            36 => "يس", 37 => "الصافات", 38 => "ص", 39 => "الزمر", 40 => "غافر",
            41 => "فصلت", 42 => "الشورى", 43 => "الزخرف", 44 => "الدخان", 45 => "الجاثية",
            46 => "الأحقاف", 47 => "محمد", 48 => "الفتح", 49 => "الحجرات", 50 => "ق",
            51 => "الذاريات", 52 => "الطور", 53 => "النجم", 54 => "القمر", 55 => "الرحمن",
            56 => "الواقعة", 57 => "الحديد", 58 => "المجادلة", 59 => "الحشر", 60 => "الممتحنة",
            61 => "الصف", 62 => "الجمعة", 63 => "المنافقون", 64 => "التغابن", 65 => "الطلاق",
            66 => "التحريم", 67 => "الملك", 68 => "القلم", 69 => "الحاقة", 70 => "المعارج",
            71 => "نوح", 72 => "الجن", 73 => "المزمل", 74 => "المدثر", 75 => "القيامة",
            76 => "الإنسان", 77 => "المرسلات", 78 => "النبأ", 79 => "النازعات", 80 => "عبس",
            81 => "التكوير", 82 => "الانفطار", 83 => "المطففين", 84 => "الانشقاق", 85 => "البروج",
            86 => "الطارق", 87 => "الأعلى", 88 => "الغاشية", 89 => "الفجر", 90 => "البلد",
            91 => "الشمس", 92 => "الليل", 93 => "الضحى", 94 => "الشرح", 95 => "التين",
            96 => "العلق", 97 => "القدر", 98 => "البينة", 99 => "الزلزلة", 100 => "العاديات",
            101 => "القارعة", 102 => "التكاثر", 103 => "العصر", 104 => "الهمزة", 105 => "الفيل",
            106 => "قريش", 107 => "الماعون", 108 => "الكوثر", 109 => "الكافرون", 110 => "النصر",
            111 => "المسد", 112 => "الإخلاص", 113 => "الفلق", 114 => "الناس"
        ];
        
        foreach ($surahNames as $id => $name) {
            DB::table('surahs')->updateOrInsert(
                ['id' => $id],
                [
                    'name_ar' => $name,
                    'name_en' => null, // Placeholder
                    'ayah_count' => 0, // Will update next
                    'revelation_type' => 'meccan',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        
        echo "Transferring ayahs...\n";
        
        // Use a SELECT and INSERT to avoid memory issues if quran_text is huge
        // But 6236 rows is small.
        $rows = DB::table('quran_text')->get();
        foreach ($rows as $row) {
            DB::table('ayahs')->insert([
                'surah_id' => $row->sura,
                'ayah_number' => $row->aya,
                'text_uthmani' => $row->text,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Update surah ayah counts
        $counts = DB::table('ayahs')->select('surah_id', DB::raw('count(*) as total'))
                  ->groupBy('surah_id')->get();
        foreach ($counts as $c) {
            DB::table('surahs')->where('id', $c->surah_id)->update(['ayah_count' => $c->total]);
        }
    });

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    // Drop temporary table (commented for debug)
    // Schema::dropIfExists('quran_text');
    if (Schema::hasTable('quran_text')) {
        echo "quran_text count: " . DB::table('quran_text')->count() . "\n";
    } else {
        echo "quran_text table does NOT exist!\n";
    }
}

echo "Quran import completed.\n";

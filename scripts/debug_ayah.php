<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$surahs = \App\Models\Surah::take(5)->get();
foreach ($surahs as $s) {
    echo "ID: {$s->id} | Name: '{$s->name_arabic}'\n";
}

<?php
// Debug Script to check User Status
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- TIMEZONE CHECK ---\n";
echo "Now (App): " . now() . "\n";
echo "Timezone: " . config('app.timezone') . "\n";

$user = \App\Models\User::find(6);
echo "\n=== DEEP DIVE USER 6 ===\n";
$answers = \App\Models\UserAnswer::with('question')->where('user_id', 6)->orderBy('created_at', 'desc')->take(10)->get();
foreach ($answers as $a) {
    $qAyah = $a->question ? $a->question->ayah_id : 'NULL';
    echo "AnsID: {$a->id} | QID: {$a->question_id} | QAyah: {$qAyah} | Correct: {$a->is_correct} | Time: {$a->created_at}\n";
}

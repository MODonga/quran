<?php

use Illuminate\Support\Facades\Config;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Get DB Connection Type
$connection = Config::get('database.default');
$driver = Config::get("database.connections.$connection.driver");

// 2. Prepare Backup Path
$backupDir = base_path('backups');
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$timestamp = date('Y-m-d_H-i-s');
$filename = "quran_backup_{$timestamp}.sql";

if ($driver === 'sqlite') {
    $dbPath = Config::get("database.connections.$connection.database");
    $filename = "quran_backup_{$timestamp}.sqlite";
    $fullPath = $backupDir . '/' . $filename;
    
    echo "Detected SQLite database. Copying file: $dbPath...\n";
    if (copy($dbPath, $fullPath)) {
        echo "Backup completed successfully: $fullPath\n";
    } else {
        echo "Backup failed to copy SQLite file.\n";
    }
} else {
    // MySQL Logic
    $dbHost = Config::get("database.connections.$connection.host");
    $dbName = Config::get("database.connections.$connection.database");
    $dbUser = Config::get("database.connections.$connection.username");
    $dbPass = Config::get("database.connections.$connection.password");
    
    $fullPath = $backupDir . '/' . $filename;
    $command = sprintf(
        'mysqldump --host=%s --user=%s --password=%s %s > %s',
        escapeshellarg($dbHost),
        escapeshellarg($dbUser),
        escapeshellarg($dbPass),
        escapeshellarg($dbName),
        escapeshellarg($fullPath)
    );

    echo "Starting MySQL backup for database: $dbName...\n";

    $result = null;
    $output = [];
    exec($command . ' 2>&1', $output, $result);

    if ($result === 0) {
        echo "Backup completed successfully: $fullPath\n";
    } else {
        echo "Backup failed with error code $result.\n";
        echo "Output: " . implode("\n", $output) . "\n";
    }
}


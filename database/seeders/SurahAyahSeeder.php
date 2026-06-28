<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SurahAyahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $scriptPath = base_path('scripts/import_quran_sql.php');
        
        if (file_exists($scriptPath)) {
            $this->command->info('Starting Quran data import via SQL script...');
            
            // Execute the existing import script
            // We use shell_exec to run it as a separate process to avoid complex inclusion issues
            $output = shell_exec("php \"$scriptPath\"");
            
            $this->command->info($output);
            $this->command->info('Quran data import completed.');
        } else {
            $this->command->error('Import script not found at ' . $scriptPath);
        }
    }
}

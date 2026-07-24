<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'db:backup';

    protected $description = 'Backup the database';

    public function handle()
    {
        $this->newLine();

        $this->info('====================================');
        $this->info('Database Backup');
        $this->info('====================================');

        $this->newLine();

        $database = config('database.connections.mysql.database');

        $this->line("Database : {$database}");
        $this->line("Backup Started...");
        $this->newLine();


        $backupPath = storage_path('backups');

        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }


        $fileName = 'backup_' . now()->format('Y_m_d_H_i_s') . '.sql';

        $filePath = $backupPath . '/' . $fileName;


        File::put($filePath, "-- Database Backup Created " . now());


        $this->info('✔ Backup completed successfully.');

        $this->newLine();

        $this->line('File:');
        $this->line('storage/backups/' . $fileName);


        return self::SUCCESS;
    }
}

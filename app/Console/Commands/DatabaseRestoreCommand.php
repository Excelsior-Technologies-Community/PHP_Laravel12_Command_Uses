<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseRestoreCommand extends Command
{
    protected $signature = 'db:restore 
                            {--file= : Specific backup file name (e.g. backup_2026_10_20.sql or .sql.gz)} 
                            {--force : Force restore without interactive confirmation}';

    protected $description = 'Restore database from an existing SQL or Gzip backup file';

    public function handle(): int
    {
        $this->newLine();
        $this->info('====================================================');
        $this->info('           DATABASE RESTORE MANAGER');
        $this->info('====================================================');
        $this->newLine();

        $backupDir = storage_path('backups');
        if (!File::exists($backupDir)) {
            $this->error('Backup directory does not exist: storage/backups');
            return self::FAILURE;
        }

        $allFiles = File::files($backupDir);
        $backupList = [];

        foreach ($allFiles as $f) {
            $ext = $f->getExtension();
            if ($ext === 'sql' || $ext === 'gz') {
                $backupList[$f->getFilename()] = $f;
            }
        }

        if (empty($backupList)) {
            $this->warn('No backup files (.sql or .sql.gz) found in storage/backups/');
            return self::FAILURE;
        }

        $targetFileName = $this->option('file');

        if (!$targetFileName) {
            if (!$this->input->isInteractive()) {
                $targetFileName = array_key_first($backupList);
                $this->comment("Non-interactive mode: auto-selected latest backup: {$targetFileName}");
            } else {
                $choices = array_keys($backupList);
                $targetFileName = $this->choice('Select backup file to restore:', $choices, 0);
            }
        }

        $targetFile = null;
        if (isset($backupList[$targetFileName])) {
            $targetFile = $backupList[$targetFileName];
        } else {
            // Check direct path or match
            $fullPath = $backupDir . DIRECTORY_SEPARATOR . $targetFileName;
            if (File::exists($fullPath)) {
                $targetFile = new \SplFileInfo($fullPath);
            }
        }

        if (!$targetFile || !File::exists($targetFile->getRealPath())) {
            $this->error("Selected backup file [{$targetFileName}] not found.");
            return self::FAILURE;
        }

        $database = config('database.connections.' . config('database.default') . '.database');
        $this->warn("CAUTION: Restoring will overwrite existing tables in database [{$database}]!");

        if (!$this->option('force') && $this->input->isInteractive()) {
            if (!$this->confirm('Are you sure you want to proceed with database restore?', false)) {
                $this->info('Database restore cancelled.');
                return self::SUCCESS;
            }
        }

        $this->line("Reading backup file: <fg=cyan>{$targetFile->getFilename()}</>...");
        $startTime = microtime(true);

        $filePath = $targetFile->getRealPath();
        $isGz = str_ends_with($targetFile->getFilename(), '.gz');

        if ($isGz) {
            $compressedData = File::get($filePath);
            $sqlContent = gzdecode($compressedData);
            if ($sqlContent === false) {
                $this->error('Failed to decompress gzip backup file.');
                return self::FAILURE;
            }
        } else {
            $sqlContent = File::get($filePath);
        }

        $driver = config('database.connections.' . config('database.default') . '.driver');

        $this->info('Executing SQL restore script...');

        try {
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            }

            DB::unprepared($sqlContent);

            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            $duration = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->info("✔ Database restored successfully in {$duration} seconds!");
            $this->line("Restored from: storage/backups/{$targetFile->getFilename()}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error('✘ Database restore failed: ' . $e->getMessage());

            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            return self::FAILURE;
        }
    }
}

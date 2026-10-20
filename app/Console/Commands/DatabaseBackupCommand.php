<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'db:backup 
                            {--compress : Compress the backup file with gzip (.sql.gz)} 
                            {--filename= : Specify a custom backup filename} 
                            {--keep=7 : Number of latest backup files to retain}';

    protected $description = 'Create a real full database dump with optional gzip compression and automatic rotation';

    public function handle(): int
    {
        $startTime = microtime(true);

        $this->newLine();
        $this->info('====================================================');
        $this->info('           ENTERPRISE DATABASE BACKUP');
        $this->info('====================================================');
        $this->newLine();

        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");
        $database = config("database.connections.{$connection}.database");

        $this->line("• Connection : <fg=cyan>{$connection}</>");
        $this->line("• Driver     : <fg=cyan>{$driver}</>");
        $this->line("• Database   : <fg=cyan>{$database}</>");
        $this->newLine();

        $backupDir = storage_path('backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $tables = $this->getTables($driver);
        if (empty($tables)) {
            $this->warn('No tables found in the database to backup.');
            return self::SUCCESS;
        }

        $this->info("Found " . count($tables) . " tables. Starting export...");
        $this->output->progressStart(count($tables));

        $sqlContent = "-- ====================================================\n";
        $sqlContent .= "-- Enterprise Database Backup\n";
        $sqlContent .= "-- Generated at : " . now()->toDateTimeString() . "\n";
        $sqlContent .= "-- Driver       : {$driver}\n";
        $sqlContent .= "-- Database     : {$database}\n";
        $sqlContent .= "-- ====================================================\n\n";

        if ($driver === 'mysql') {
            $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n";
            $sqlContent .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $sqlContent .= "SET NAMES utf8mb4;\n\n";
        }

        $totalRecords = 0;
        $tableStats = [];

        foreach ($tables as $table) {
            $createTableSql = $this->getCreateTableSql($driver, $table);
            if ($createTableSql) {
                $sqlContent .= "-- ----------------------------------------------------\n";
                $sqlContent .= "-- Table structure for `{$table}`\n";
                $sqlContent .= "-- ----------------------------------------------------\n";
                $sqlContent .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sqlContent .= "{$createTableSql};\n\n";
            }

            // Dump Rows
            $rowCount = DB::table($table)->count();
            $totalRecords += $rowCount;
            $tableStats[] = [$table, $rowCount];

            if ($rowCount > 0) {
                $sqlContent .= "-- Dumping data for table `{$table}` ({$rowCount} rows)\n";

                DB::table($table)->orderBy(DB::raw('1'))->chunk(500, function ($rows) use (&$sqlContent, $table) {
                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        $columns = array_keys($rowArray);
                        $escapedColumns = array_map(fn($col) => "`{$col}`", $columns);

                        $escapedValues = array_map(function ($val) {
                            if (is_null($val)) {
                                return 'NULL';
                            }
                            return DB::getPdo()->quote($val);
                        }, array_values($rowArray));

                        $sqlContent .= "INSERT INTO `{$table}` (" . implode(', ', $escapedColumns) . ") VALUES (" . implode(', ', $escapedValues) . ");\n";
                    }
                });
                $sqlContent .= "\n";
            }

            $this->output->progressAdvance();
        }

        if ($driver === 'mysql') {
            $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";
        }

        $this->output->progressFinish();
        $this->newLine();

        // Determine filename
        $customName = $this->option('filename');
        $baseName = $customName ? pathinfo($customName, PATHINFO_FILENAME) : 'backup_' . now()->format('Y_m_d_His');
        $isCompress = $this->option('compress');

        if ($isCompress) {
            $fileName = $baseName . '.sql.gz';
            $filePath = $backupDir . DIRECTORY_SEPARATOR . $fileName;
            $gzData = gzencode($sqlContent, 9);
            File::put($filePath, $gzData);
        } else {
            $fileName = $baseName . '.sql';
            $filePath = $backupDir . DIRECTORY_SEPARATOR . $fileName;
            File::put($filePath, $sqlContent);
        }

        $fileSize = File::size($filePath);
        $fileSizeFormatted = $this->formatBytes($fileSize);
        $duration = round(microtime(true) - $startTime, 2);

        $this->info("✔ Backup successfully generated!");
        $this->table(
            ['Property', 'Details'],
            [
                ['File Name', $fileName],
                ['Path', 'storage/backups/' . $fileName],
                ['Size', $fileSizeFormatted],
                ['Tables Exported', count($tables)],
                ['Total Records', number_format($totalRecords)],
                ['Compressed', $isCompress ? 'Yes (gzip)' : 'No'],
                ['Duration', "{$duration} seconds"],
            ]
        );

        // Auto Rotation
        $keep = (int) $this->option('keep') ?: 7;
        $this->rotateBackups($backupDir, $keep);

        return self::SUCCESS;
    }

    protected function getTables(string $driver): array
    {
        $tables = [];

        if ($driver === 'mysql') {
            $database = config('database.connections.mysql.database');
            $rows = DB::select('SHOW TABLES');
            $key = "Tables_in_{$database}";
            foreach ($rows as $row) {
                if (isset($row->$key)) {
                    $tables[] = $row->$key;
                } else {
                    $arr = (array) $row;
                    $tables[] = reset($arr);
                }
            }
        } elseif ($driver === 'sqlite') {
            $rows = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            foreach ($rows as $row) {
                $tables[] = $row->name;
            }
        }

        return $tables;
    }

    protected function getCreateTableSql(string $driver, string $table): ?string
    {
        if ($driver === 'mysql') {
            $result = DB::select("SHOW CREATE TABLE `{$table}`");
            if (!empty($result)) {
                $arr = (array) $result[0];
                return $arr['Create Table'] ?? null;
            }
        } elseif ($driver === 'sqlite') {
            $result = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$table]);
            if (!empty($result)) {
                return $result[0]->sql;
            }
        }

        return null;
    }

    protected function rotateBackups(string $backupDir, int $keep): void
    {
        $files = File::files($backupDir);
        $backupFiles = [];

        foreach ($files as $file) {
            $ext = $file->getExtension();
            if ($ext === 'sql' || $ext === 'gz') {
                $backupFiles[] = [
                    'file' => $file,
                    'time' => $file->getMTime(),
                ];
            }
        }

        usort($backupFiles, fn($a, $b) => $b['time'] <=> $a['time']);

        if (count($backupFiles) > $keep) {
            $this->newLine();
            $this->comment("Applying retention policy (keeping {$keep} most recent backups)...");

            $toDelete = array_slice($backupFiles, $keep);
            foreach ($toDelete as $item) {
                File::delete($item['file']->getRealPath());
                $this->line("  - Removed older backup: {$item['file']->getFilename()}");
            }
        }
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}

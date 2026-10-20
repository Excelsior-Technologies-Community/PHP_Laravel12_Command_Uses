<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseOptimizeCommand extends Command
{
    protected $signature = 'db:optimize 
                            {--prune-days=30 : Purge records older than specified days} 
                            {--force : Execute without confirmation}';

    protected $description = 'Clean expired sessions, tokens, old logs, and defragment database tables';

    public function handle(): int
    {
        $startTime = microtime(true);

        $this->newLine();
        $this->info('====================================================');
        $this->info('        DATABASE OPTIMIZATION & CLEANUP');
        $this->info('====================================================');
        $this->newLine();

        $pruneDays = (int) $this->option('prune-days') ?: 30;
        $pruneThreshold = now()->subDays($pruneDays);
        $summary = [];

        // 1. Clean Expired Sessions
        if (Schema::hasTable('sessions')) {
            $lifetimeSecs = config('session.lifetime', 120) * 60;
            $expiredActivity = time() - $lifetimeSecs;
            $deletedSessions = DB::table('sessions')->where('last_activity', '<', $expiredActivity)->delete();
            $summary[] = ['sessions', 'Expired sessions cleaned', "{$deletedSessions} deleted"];
            $this->line("• Cleaned expired sessions: <fg=green>{$deletedSessions}</> records");
        }

        // 2. Clean Password Reset Tokens
        if (Schema::hasTable('password_reset_tokens')) {
            $tokenExpireSecs = (int) config('auth.passwords.users.expire', 60) * 60;
            $tokenThreshold = now()->subSeconds($tokenExpireSecs);
            $deletedTokens = DB::table('password_reset_tokens')->where('created_at', '<', $tokenThreshold)->delete();
            $summary[] = ['password_reset_tokens', 'Expired tokens cleared', "{$deletedTokens} deleted"];
            $this->line("• Cleared expired password tokens: <fg=green>{$deletedTokens}</> records");
        }

        // 3. Clean Expired Cache Records
        if (Schema::hasTable('cache')) {
            $deletedCache = DB::table('cache')->where('expiration', '<', time())->delete();
            $summary[] = ['cache', 'Expired database cache items', "{$deletedCache} deleted"];
            $this->line("• Cleared expired cache items: <fg=green>{$deletedCache}</> records");
        }

        // 4. Prune Old Failed Jobs
        if (Schema::hasTable('failed_jobs')) {
            $deletedFailedJobs = DB::table('failed_jobs')->where('failed_at', '<', $pruneThreshold)->delete();
            $summary[] = ['failed_jobs', "Failed jobs older than {$pruneDays}d", "{$deletedFailedJobs} deleted"];
            $this->line("• Pruned old failed jobs: <fg=green>{$deletedFailedJobs}</> records");
        }

        // 5. Prune Old Command Audit Logs
        if (Schema::hasTable('command_logs')) {
            $deletedLogs = DB::table('command_logs')->where('created_at', '<', $pruneThreshold)->delete();
            $summary[] = ['command_logs', "Command logs older than {$pruneDays}d", "{$deletedLogs} deleted"];
            $this->line("• Pruned historical command logs: <fg=green>{$deletedLogs}</> records");
        }

        $this->newLine();
        $this->info("Defragmenting and optimizing tables...");

        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");
        $optimizedCount = 0;

        if ($driver === 'mysql') {
            $database = config("database.connections.{$connection}.database");
            $tables = DB::select('SHOW TABLES');
            $key = "Tables_in_{$database}";

            foreach ($tables as $row) {
                $tableName = isset($row->$key) ? $row->$key : reset($row);
                try {
                    DB::statement("OPTIMIZE TABLE `{$tableName}`");
                    $summary[] = [$tableName, 'Table Defragmentation', 'OPTIMIZE OK'];
                    $optimizedCount++;
                } catch (\Throwable $e) {
                    $summary[] = [$tableName, 'Table Defragmentation', 'SKIPPED'];
                }
            }
        } elseif ($driver === 'sqlite') {
            try {
                DB::statement('VACUUM');
                DB::statement('ANALYZE');
                $summary[] = ['sqlite_database', 'VACUUM & ANALYZE', 'SUCCESS'];
                $optimizedCount++;
            } catch (\Throwable $e) {
                $summary[] = ['sqlite_database', 'VACUUM & ANALYZE', 'FAILED'];
            }
        }

        $duration = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->table(['Target Table', 'Optimization Operation', 'Result'], $summary);
        $this->info("✔ Database optimization complete in {$duration}s ({$optimizedCount} tables optimized).");

        return self::SUCCESS;
    }
}

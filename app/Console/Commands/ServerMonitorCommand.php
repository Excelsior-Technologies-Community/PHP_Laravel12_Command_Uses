<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ServerMonitorCommand extends Command
{
    protected $signature = 'app:monitor 
                            {--notify : Send webhook notification if issues detected} 
                            {--disk-threshold=15 : Minimum free disk space percentage before warning} 
                            {--webhook= : Custom Discord or Slack webhook URL}';

    protected $description = 'Monitor real-time server resources, database latency, queues, and dispatch alerts';

    public function handle(): int
    {
        $this->newLine();
        $this->info('====================================================');
        $this->info('      SERVER & APPLICATION RESOURCE MONITOR');
        $this->info('====================================================');
        $this->newLine();

        $metrics = $this->collectMetrics();
        $issues = [];

        // Display Metrics Table
        $tableRows = [
            ['Environment', $metrics['environment'], 'INFO'],
            ['PHP Version', $metrics['php_version'], 'OK'],
            ['Laravel Version', $metrics['laravel_version'], 'OK'],
            ['Memory Current', $metrics['memory_current'], 'OK'],
            ['Memory Peak', $metrics['memory_peak'], 'OK'],
            ['Disk Space Total', $metrics['disk_total'], 'INFO'],
            ['Disk Space Free', "{$metrics['disk_free']} ({$metrics['disk_free_percent']}%)", $metrics['disk_status']],
            ['Database Status', $metrics['db_status'], $metrics['db_status'] === 'CONNECTED' ? 'OK' : 'ERROR'],
            ['Database Latency', $metrics['db_latency'], $metrics['db_latency_status']],
            ['Pending Queue Jobs', $metrics['queue_pending'], $metrics['queue_pending'] > 100 ? 'WARN' : 'OK'],
            ['Failed Queue Jobs', $metrics['queue_failed'], $metrics['queue_failed'] > 0 ? 'WARN' : 'OK'],
            ['Log Directory Size', $metrics['logs_size'], 'INFO'],
            ['Backup Files Count', $metrics['backups_count'] . " ({$metrics['backups_size']})", 'INFO'],
        ];

        $formattedRows = array_map(function ($row) use (&$issues) {
            $status = $row[2];
            $coloredStatus = match ($status) {
                'OK' => '<fg=green>✔ OK</>',
                'INFO' => '<fg=cyan>ℹ INFO</>',
                'WARN' => '<fg=yellow>⚠ WARN</>',
                'ERROR' => '<fg=red>✘ ERROR</>',
                default => $status,
            };

            if (in_array($status, ['WARN', 'ERROR'])) {
                $issues[] = "{$row[0]}: {$row[1]} ({$status})";
            }

            return [$row[0], $row[1], $coloredStatus];
        }, $tableRows);

        $this->table(['Resource / Component', 'Current Metric', 'Status'], $formattedRows);
        $this->newLine();

        if (empty($issues)) {
            $this->info('✔ ALL SYSTEMS OPERATIONAL: No resource bottlenecks detected.');
        } else {
            $this->warn('⚠ WARNING: The following issues were detected:');
            foreach ($issues as $issue) {
                $this->line("  • {$issue}");
            }

            if ($this->option('notify')) {
                $this->sendAlertNotification($issues, $metrics);
            }
        }

        return empty($issues) ? self::SUCCESS : self::FAILURE;
    }

    public function collectMetrics(): array
    {
        // Memory
        $memCurrent = memory_get_usage(true);
        $memPeak = memory_get_peak_usage(true);

        // Disk
        $diskPath = storage_path();
        $totalDisk = @disk_total_space($diskPath) ?: 1;
        $freeDisk = @disk_free_space($diskPath) ?: 0;
        $freePercent = round(($freeDisk / $totalDisk) * 100, 1);
        $diskThreshold = (int) $this->option('disk-threshold') ?: 15;
        $diskStatus = $freePercent < $diskThreshold ? 'WARN' : 'OK';

        // Database & Latency
        $dbStatus = 'DISCONNECTED';
        $dbLatency = 'N/A';
        $dbLatencyStatus = 'ERROR';

        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $ms = round((microtime(true) - $start) * 1000, 2);
            $dbStatus = 'CONNECTED';
            $dbLatency = "{$ms} ms";
            $dbLatencyStatus = $ms < 50 ? 'OK' : ($ms < 200 ? 'WARN' : 'ERROR');
        } catch (\Throwable $e) {
            $dbStatus = 'FAILED: ' . $e->getMessage();
        }

        // Queues
        $pendingJobs = 0;
        $failedJobs = 0;
        try {
            $pendingJobs = DB::table('jobs')->count();
        } catch (\Throwable $e) {}
        try {
            $failedJobs = DB::table('failed_jobs')->count();
        } catch (\Throwable $e) {}

        // Storage
        $logsDir = storage_path('logs');
        $logsSize = $this->getDirectorySize($logsDir);

        $backupsDir = storage_path('backups');
        $backupsSize = $this->getDirectorySize($backupsDir);
        $backupCount = File::exists($backupsDir) ? count(File::files($backupsDir)) : 0;

        return [
            'environment' => config('app.env') . (config('app.debug') ? ' (Debug Enabled)' : ''),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_current' => $this->formatBytes($memCurrent),
            'memory_peak' => $this->formatBytes($memPeak),
            'disk_total' => $this->formatBytes($totalDisk),
            'disk_free' => $this->formatBytes($freeDisk),
            'disk_free_percent' => $freePercent,
            'disk_status' => $diskStatus,
            'db_status' => $dbStatus,
            'db_latency' => $dbLatency,
            'db_latency_status' => $dbLatencyStatus,
            'queue_pending' => $pendingJobs,
            'queue_failed' => $failedJobs,
            'logs_size' => $this->formatBytes($logsSize),
            'backups_count' => $backupCount,
            'backups_size' => $this->formatBytes($backupsSize),
        ];
    }

    protected function sendAlertNotification(array $issues, array $metrics): void
    {
        $webhook = $this->option('webhook') ?: env('MONITOR_WEBHOOK_URL');

        $this->newLine();
        $this->comment('Dispatching alert notifications...');

        // Always log to Laravel log file
        Log::warning('App Monitor Alert: Resource bottlenecks detected', [
            'issues' => $issues,
            'metrics' => $metrics,
        ]);

        if ($webhook) {
            try {
                $payload = [
                    'content' => "🚨 **[System Health Alert - " . config('app.name') . "]**\n" . implode("\n", array_map(fn($i) => "• " . $i, $issues)),
                    'embeds' => [
                        [
                            'title' => 'Server Health Report',
                            'color' => 15158332, // Red/Amber
                            'fields' => [
                                ['name' => 'Environment', 'value' => $metrics['environment'], 'inline' => true],
                                ['name' => 'Disk Free', 'value' => "{$metrics['disk_free']} ({$metrics['disk_free_percent']}%)", 'inline' => true],
                                ['name' => 'DB Latency', 'value' => $metrics['db_latency'], 'inline' => true],
                            ],
                            'timestamp' => now()->toISOString(),
                        ]
                    ]
                ];

                $response = Http::timeout(5)->post($webhook, $payload);
                if ($response->successful()) {
                    $this->info("✔ Webhook notification delivered successfully to: {$webhook}");
                } else {
                    $this->warn("⚠ Webhook returned status code: {$response->status()}");
                }
            } catch (\Throwable $e) {
                $this->error("Failed to deliver webhook notification: {$e->getMessage()}");
            }
        } else {
            $this->line("ℹ No webhook URL configured. Alert logged to storage/logs/laravel.log.");
            $this->line("  Tip: set MONITOR_WEBHOOK_URL in .env or pass --webhook=https://...");
        }
    }

    protected function getDirectorySize(string $directory): int
    {
        if (!File::exists($directory)) {
            return 0;
        }

        $size = 0;
        foreach (File::allFiles($directory) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}

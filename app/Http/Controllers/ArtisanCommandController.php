<?php

namespace App\Http\Controllers;

use App\Models\CommandLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Output\BufferedOutput;

class ArtisanCommandController extends Controller
{
    /**
     * Define the supported catalog of commands with their category, metadata, and options.
     */
    protected function getCommandCatalog(): array
    {
        return [
            [
                'command' => 'db:backup',
                'name' => 'Database Backup',
                'category' => 'Database',
                'icon' => 'bi-database-down',
                'badge' => 'primary',
                'description' => 'Dump full database schema & table data with optional Gzip compression and retention rotation.',
                'options' => [
                    ['name' => '--compress', 'type' => 'boolean', 'label' => 'Compress with Gzip (.sql.gz)', 'default' => true],
                    ['name' => '--keep', 'type' => 'number', 'label' => 'Retention limit (number of recent backups to keep)', 'default' => 7],
                    ['name' => '--table', 'type' => 'text', 'label' => 'Specific table only (optional)', 'placeholder' => 'e.g. users'],
                ],
            ],
            [
                'command' => 'db:restore',
                'name' => 'Database Restore',
                'category' => 'Database',
                'icon' => 'bi-database-up',
                'badge' => 'danger',
                'description' => 'Restore database from an existing .sql or .sql.gz backup file.',
                'options' => [
                    ['name' => '--file', 'type' => 'select_backup', 'label' => 'Select Backup File', 'required' => true],
                    ['name' => '--force', 'type' => 'boolean', 'label' => 'Force execution without interactive prompt', 'default' => true],
                ],
            ],
            [
                'command' => 'db:optimize',
                'name' => 'Database Optimizer & Cleaner',
                'category' => 'Database',
                'icon' => 'bi-tools',
                'badge' => 'info',
                'description' => 'Purge expired sessions, expired reset tokens, stale logs, and defragment fragmented tables.',
                'options' => [
                    ['name' => '--prune-days', 'type' => 'number', 'label' => 'Purge records older than X days', 'default' => 30],
                    ['name' => '--prune-logs', 'type' => 'number', 'label' => 'Purge command logs older than X days', 'default' => 30],
                ],
            ],
            [
                'command' => 'db:stats',
                'name' => 'Database Statistics',
                'category' => 'Database',
                'icon' => 'bi-pie-chart',
                'badge' => 'secondary',
                'description' => 'Inspect table row counts, storage size, and database engine breakdown.',
                'options' => [],
            ],
            [
                'command' => 'app:monitor',
                'name' => 'Server Health & Resource Monitor',
                'category' => 'Monitoring',
                'icon' => 'bi-activity',
                'badge' => 'success',
                'description' => 'Real-time CPU, RAM, Disk, DB query latency, and optional webhook alerts.',
                'options' => [
                    ['name' => '--threshold-cpu', 'type' => 'number', 'label' => 'CPU Alert Threshold (%)', 'default' => 80],
                    ['name' => '--threshold-ram', 'type' => 'number', 'label' => 'RAM Alert Threshold (%)', 'default' => 85],
                    ['name' => '--threshold-disk', 'type' => 'number', 'label' => 'Disk Alert Threshold (%)', 'default' => 90],
                    ['name' => '--notify', 'type' => 'boolean', 'label' => 'Dispatch Webhook notification if alerts triggered', 'default' => false],
                ],
            ],
            [
                'command' => 'app:health',
                'name' => 'Application Health Check',
                'category' => 'Monitoring',
                'icon' => 'bi-heart-pulse',
                'badge' => 'success',
                'description' => 'Verify core application components, environment settings, and database readiness.',
                'options' => [],
            ],
            [
                'command' => 'app:stats',
                'name' => 'Application Statistics',
                'category' => 'Monitoring',
                'icon' => 'bi-bar-chart-line',
                'badge' => 'secondary',
                'description' => 'View total models, registered routes, console commands, and overall app metrics.',
                'options' => [],
            ],
            [
                'command' => 'system:info',
                'name' => 'System & Environment Info',
                'category' => 'Diagnostics',
                'icon' => 'bi-cpu',
                'badge' => 'dark',
                'description' => 'Display OS details, PHP extensions, server architecture, and memory limits.',
                'options' => [],
            ],
            [
                'command' => 'env:details',
                'name' => 'Environment Configuration',
                'category' => 'Diagnostics',
                'icon' => 'bi-gear-wide-connected',
                'badge' => 'dark',
                'description' => 'Inspect sanitized environment variables, drivers, and runtime configurations.',
                'options' => [],
            ],
            [
                'command' => 'app:cache-manage',
                'name' => 'Application Cache Manager',
                'category' => 'Maintenance',
                'icon' => 'bi-lightning',
                'badge' => 'warning',
                'description' => 'Clear or rebuild config, route, view, and application data cache stores.',
                'options' => [
                    ['name' => 'action', 'type' => 'select', 'label' => 'Action', 'options' => ['clear' => 'Clear All Caches', 'optimize' => 'Optimize & Pre-cache'], 'default' => 'clear'],
                ],
            ],
            [
                'command' => 'logs:clear',
                'name' => 'Clear Log Files',
                'category' => 'Maintenance',
                'icon' => 'bi-journal-x',
                'badge' => 'warning',
                'description' => 'Safely truncate application log files in storage/logs directory.',
                'options' => [],
            ],
            [
                'command' => 'storage:clean',
                'name' => 'Clean Storage Temp Files',
                'category' => 'Maintenance',
                'icon' => 'bi-trash3',
                'badge' => 'warning',
                'description' => 'Remove temporary uploads, cache artifacts, and obsolete sessions.',
                'options' => [],
            ],
            [
                'command' => 'users:report',
                'name' => 'Users Summary Report',
                'category' => 'Reports',
                'icon' => 'bi-people',
                'badge' => 'info',
                'description' => 'Generate formatted summary table of registered user accounts and roles.',
                'options' => [],
            ],
            [
                'command' => 'password:generate',
                'name' => 'Secure Password Generator',
                'category' => 'Security',
                'icon' => 'bi-key',
                'badge' => 'secondary',
                'description' => 'Generate high-entropy cryptographically secure random passwords.',
                'options' => [],
            ],
        ];
    }

    /**
     * Display the Command Dashboard.
     */
    public function index()
    {
        $catalog = $this->getCommandCatalog();
        $categories = collect($catalog)->pluck('category')->unique()->values();

        // Backup files in storage/backups
        $backupDir = storage_path('backups');
        $backups = [];
        if (File::isDirectory($backupDir)) {
            $files = File::files($backupDir);
            foreach ($files as $file) {
                if (in_array(strtolower($file->getExtension()), ['sql', 'gz'])) {
                    $backups[] = [
                        'filename' => $file->getFilename(),
                        'size' => $this->formatBytes($file->getSize()),
                        'raw_size' => $file->getSize(),
                        'is_compressed' => str_ends_with(strtolower($file->getFilename()), '.gz'),
                        'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                        'timestamp' => $file->getMTime(),
                    ];
                }
            }
            // Sort by latest first
            usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
        }

        // Execution History Logs
        $logs = CommandLog::latest()->take(25)->get();

        // Server Health Snapshot
        $health = $this->getQuickHealthSnapshot();

        return view('commands.dashboard', compact('catalog', 'categories', 'backups', 'logs', 'health'));
    }

    /**
     * Execute an artisan command via Web request.
     */
    public function run(Request $request)
    {
        $command = trim($request->input('command', ''));
        $catalog = collect($this->getCommandCatalog())->keyBy('command');

        // Check if command is permitted
        if (!$catalog->has($command)) {
            return response()->json([
                'success' => false,
                'exit_code' => 1,
                'output' => "Error: Command '{$command}' is not in the allowed command catalog.",
                'duration' => '0.00s',
            ], 422);
        }

        $catalogItem = $catalog->get($command);
        $parameters = [];

        // Build command parameters based on catalog options definition
        if (!empty($catalogItem['options'])) {
            foreach ($catalogItem['options'] as $option) {
                $optName = $option['name'];
                $cleanKey = ltrim($optName, '-');

                if ($option['type'] === 'boolean') {
                    if ($request->boolean($cleanKey)) {
                        $parameters[$optName] = true;
                    }
                } elseif ($option['type'] === 'select' && !str_starts_with($optName, '--')) {
                    // Positional argument
                    $val = $request->input($cleanKey);
                    if ($val !== null && $val !== '') {
                        $parameters[$optName] = $val;
                    }
                } else {
                    $val = $request->input($cleanKey);
                    if ($val !== null && $val !== '') {
                        $parameters[$optName] = $val;
                    }
                }
            }
        }

        // Special argument mapping
        if ($command === 'db:restore') {
            $backupFile = $request->input('file');
            if (empty($backupFile)) {
                return response()->json([
                    'success' => false,
                    'exit_code' => 1,
                    'output' => "Error: A backup file must be selected for restore.",
                    'duration' => '0.00s',
                ], 422);
            }
            $parameters['--file'] = $backupFile;
            $parameters['--force'] = true;
        }

        $startTime = microtime(true);
        $outputBuffer = new BufferedOutput();

        try {
            $exitCode = Artisan::call($command, $parameters, $outputBuffer);
            $rawOutput = $outputBuffer->fetch();
        } catch (\Throwable $e) {
            $exitCode = 1;
            $rawOutput = "Execution Exception:\n" . $e->getMessage() . "\n" . $e->getTraceAsString();
        }

        $duration = round(microtime(true) - $startTime, 3);
        $success = ($exitCode === 0);

        // Record execution to CommandLog
        $log = CommandLog::create([
            'command' => $command,
            'arguments' => !empty($parameters) ? $parameters : null,
            'output' => $rawOutput,
            'exit_code' => $exitCode,
            'status' => $success ? 'SUCCESS' : 'FAILED',
            'duration' => $duration,
            'executed_by' => 'Web Administrator',
        ]);

        return response()->json([
            'success' => $success,
            'exit_code' => $exitCode,
            'output' => $rawOutput,
            'duration' => "{$duration}s",
            'log_id' => $log->id,
            'created_at' => $log->created_at->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Download a database backup file.
     */
    public function downloadBackup(string $filename)
    {
        $cleanFilename = basename($filename);
        $filePath = storage_path("backups/{$cleanFilename}");

        if (!File::exists($filePath)) {
            abort(404, 'Backup file not found.');
        }

        return response()->download($filePath, $cleanFilename);
    }

    /**
     * Delete a database backup file.
     */
    public function deleteBackup(string $filename)
    {
        $cleanFilename = basename($filename);
        $filePath = storage_path("backups/{$cleanFilename}");

        if (File::exists($filePath)) {
            File::delete($filePath);
            return redirect()->route('commands.dashboard')->with('success', "Backup '{$cleanFilename}' deleted successfully.");
        }

        return redirect()->route('commands.dashboard')->with('error', "Backup file not found.");
    }

    /**
     * Clear all execution history logs.
     */
    public function clearLogs()
    {
        CommandLog::truncate();
        return redirect()->route('commands.dashboard')->with('success', 'Execution audit history cleared successfully.');
    }

    /**
     * Get a quick snapshot of server health for the top dashboard bar.
     */
    protected function getQuickHealthSnapshot(): array
    {
        // Database connectivity & latency check
        $dbConnected = false;
        $dbLatency = 0;
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $dbLatency = round((microtime(true) - $start) * 1000, 2);
            $dbConnected = true;
        } catch (\Throwable $e) {
            $dbConnected = false;
        }

        // Disk space
        $diskPath = base_path();
        $diskFree = @disk_free_space($diskPath) ?: 0;
        $diskTotal = @disk_total_space($diskPath) ?: 1;
        $diskUsedPercent = round((($diskTotal - $diskFree) / $diskTotal) * 100, 1);

        // Memory usage
        $memUsage = memory_get_usage(true);
        $memPeak = memory_get_peak_usage(true);

        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'db_connected' => $dbConnected,
            'db_latency' => $dbLatency,
            'disk_free' => $this->formatBytes($diskFree),
            'disk_total' => $this->formatBytes($diskTotal),
            'disk_used_percent' => $diskUsedPercent,
            'memory_used' => $this->formatBytes($memUsage),
            'memory_peak' => $this->formatBytes($memPeak),
        ];
    }

    /**
     * Helper to format bytes into readable units.
     */
    protected function formatBytes(int|float $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

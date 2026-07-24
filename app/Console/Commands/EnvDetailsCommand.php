<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class EnvDetailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'env:details';

    /**
     * The console command description.
     */
    protected $description = 'Display Laravel environment and application details';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();

        $this->info(str_repeat('=', 65));
        $this->info('          Laravel Environment Details');
        $this->info(str_repeat('=', 65));

        $databaseVersion = 'Unavailable';

        try {
            $databaseVersion = DB::select('select version() as version')[0]->version;
        } catch (\Exception $e) {
            //
        }

        $storageSize = $this->formatBytes(
            $this->directorySize(storage_path())
        );

        $freeDisk = $this->formatBytes(
            disk_free_space(base_path())
        );

        $rows = [

            ['Application Name', config('app.name')],
            ['Laravel Version', app()->version()],
            ['PHP Version', PHP_VERSION],
            ['Environment', app()->environment()],
            ['Debug Mode', config('app.debug') ? 'Enabled' : 'Disabled'],
            ['App URL', config('app.url')],
            ['Timezone', config('app.timezone')],
            ['Database', config('database.default')],
            ['Database Version', $databaseVersion],
            ['Cache Driver', config('cache.default')],
            ['Session Driver', config('session.driver')],
            ['Queue Driver', config('queue.default')],
            ['Mail Driver', config('mail.default')],
            ['Current Time', now()->toDateTimeString()],
            ['Memory Limit', ini_get('memory_limit')],
            ['Max Execution Time', ini_get('max_execution_time') . ' sec'],
            ['Storage Used', $storageSize],
            ['Free Disk Space', $freeDisk],

        ];

        $this->table(
            ['Setting', 'Value'],
            $rows
        );

        $this->newLine();

        $this->info('Application Status');

        if (app()->isProduction()) {
            $this->warn('Production Environment');
        } else {
            $this->info('Development Environment');
        }

        if (config('app.debug')) {
            $this->warn('Debug Mode is ENABLED');
        } else {
            $this->info('Debug Mode is DISABLED');
        }

        $this->newLine();

        $this->info('Environment report generated successfully.');

        return self::SUCCESS;
    }

    /**
     * Calculate directory size.
     */
    protected function directorySize(string $directory): int
    {
        $size = 0;

        foreach (File::allFiles($directory) as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    /**
     * Format bytes.
     */
    protected function formatBytes($bytes, $precision = 2): string
    {
        if ($bytes <= 0) {
            return '0 Bytes';
        }

        $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];

        $power = floor(log($bytes, 1024));

        return number_format(
            $bytes / pow(1024, $power),
            $precision
        ) . ' ' . $units[$power];
    }
}
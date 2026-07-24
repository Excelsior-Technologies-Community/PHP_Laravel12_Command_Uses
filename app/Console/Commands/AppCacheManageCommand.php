<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AppCacheManageCommand extends Command
{
    /**
     * Command Signature
     */
    protected $signature = 'app:cache-manage
                            {action : clear|optimize|status}';

    /**
     * Command Description
     */
    protected $description = 'Manage Laravel application caches from a single Artisan command';

    /**
     * Execute the command.
     */
    public function handle(): int
    {
        $action = strtolower($this->argument('action'));

        $this->newLine();

        switch ($action) {

            case 'clear':
                $this->clearCaches();
                break;

            case 'optimize':
                $this->optimizeApplication();
                break;

            case 'status':
                $this->showStatus();
                break;

            default:
                $this->error("Invalid action.");

                $this->newLine();

                $this->line("Available actions:");

                $this->line("  php artisan app:cache-manage clear");
                $this->line("  php artisan app:cache-manage optimize");
                $this->line("  php artisan app:cache-manage status");

                return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Clear all Laravel caches
     */
    protected function clearCaches(): void
    {
        $this->info("Clearing Laravel caches...");
        $this->newLine();

        $tasks = [

            'Application Cache' => 'cache:clear',
            'Configuration Cache' => 'config:clear',
            'Route Cache' => 'route:clear',
            'View Cache' => 'view:clear',
            'Event Cache' => 'event:clear',

        ];

        $rows = [];

        foreach ($tasks as $title => $command) {

            try {

                Artisan::call($command);

                $rows[] = [
                    $title,
                    'SUCCESS'
                ];
            } catch (\Exception $e) {

                $rows[] = [
                    $title,
                    'FAILED'
                ];

                $this->error($e->getMessage());
            }
        }

        $this->table(
            ['Cache Type', 'Status'],
            $rows
        );

        $this->newLine();

        $this->info("All cache clear operations completed.");
    }

    /**
     * Optimize application
     */
    protected function optimizeApplication(): void
    {
        $this->info("Optimizing Laravel application...");
        $this->newLine();

        $tasks = [

            'Clear Old Cache' => 'optimize:clear',
            'Optimize Framework' => 'optimize',
            'Configuration Cache' => 'config:cache',
            'Route Cache' => 'route:cache',
            'View Cache' => 'view:cache',

        ];

        $rows = [];

        foreach ($tasks as $title => $command) {

            try {

                Artisan::call($command);

                $rows[] = [
                    $title,
                    'SUCCESS'
                ];
            } catch (\Exception $e) {

                $rows[] = [
                    $title,
                    'FAILED'
                ];
            }
        }

        $this->table(
            ['Operation', 'Status'],
            $rows
        );

        $this->newLine();

        $this->info("Application optimized successfully.");
    }

    /**
     * Display cache status
     */
    protected function showStatus(): void
    {
        $this->info("Laravel Cache Configuration");
        $this->newLine();

        $rows = [

            [
                'Cache Driver',
                config('cache.default')
            ],

            [
                'Config Cached',
                app()->configurationIsCached() ? 'YES' : 'NO'
            ],

            [
                'Routes Cached',
                app()->routesAreCached() ? 'YES' : 'NO'
            ],

            [
                'Events Cached',
                app()->eventsAreCached() ? 'YES' : 'NO'
            ],

            [
                'Environment',
                app()->environment()
            ],

            [
                'Debug Mode',
                config('app.debug') ? 'Enabled' : 'Disabled'
            ],

        ];

        $this->table(
            ['Setting', 'Value'],
            $rows
        );

        $this->newLine();

        $this->comment("Available Commands");

        $this->line("php artisan app:cache-manage clear");
        $this->line("php artisan app:cache-manage optimize");
        $this->line("php artisan app:cache-manage status");
    }
}

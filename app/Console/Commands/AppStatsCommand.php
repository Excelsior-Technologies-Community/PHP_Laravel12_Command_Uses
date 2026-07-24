<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class AppStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:stats';

    /**
     * The console command description.
     */
    protected $description = 'Display Laravel application statistics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();

        $this->info(str_repeat('=', 70));
        $this->info('                Laravel Application Statistics');
        $this->info(str_repeat('=', 70));

        $rows = [

            ['Application Name', config('app.name')],
            ['Laravel Version', app()->version()],
            ['PHP Version', PHP_VERSION],

            ['Controllers', $this->countPhpFiles(app_path('Http/Controllers'))],
            ['Models', $this->countPhpFiles(app_path('Models'))],
            ['Commands', $this->countPhpFiles(app_path('Console/Commands'))],
            ['Middleware', $this->countPhpFiles(app_path('Http/Middleware'))],
            ['Requests', $this->countPhpFiles(app_path('Http/Requests'))],
            ['Jobs', $this->countPhpFiles(app_path('Jobs'))],
            ['Events', $this->countPhpFiles(app_path('Events'))],
            ['Listeners', $this->countPhpFiles(app_path('Listeners'))],
            ['Mail Classes', $this->countPhpFiles(app_path('Mail'))],
            ['Notifications', $this->countPhpFiles(app_path('Notifications'))],
            ['Policies', $this->countPhpFiles(app_path('Policies'))],
            ['Observers', $this->countPhpFiles(app_path('Observers'))],

            ['Migrations', $this->countPhpFiles(database_path('migrations'))],
            ['Seeders', $this->countPhpFiles(database_path('seeders'))],
            ['Factories', $this->countPhpFiles(database_path('factories'))],

            ['Blade Views', $this->countBladeViews(resource_path('views'))],

            ['Registered Routes', count(Route::getRoutes())],

            ['Storage Size', $this->formatBytes(
                $this->directorySize(storage_path())
            )],

            ['Project Size', $this->formatBytes(
                $this->directorySize(base_path())
            )],

            ['Total PHP Files', $this->countAllPhpFiles(base_path())],

        ];

        $this->table(
            ['Component', 'Count / Value'],
            $rows
        );

        $this->newLine();

        $this->info('Statistics generated successfully.');

        return self::SUCCESS;
    }

    /**
     * Count PHP files in directory.
     */
    protected function countPhpFiles(string $path): int
    {
        if (! File::exists($path)) {
            return 0;
        }

        return collect(File::allFiles($path))
            ->filter(function ($file) {
                return $file->getExtension() === 'php';
            })
            ->count();
    }

    /**
     * Count Blade files.
     */
    protected function countBladeViews(string $path): int
    {
        if (! File::exists($path)) {
            return 0;
        }

        return collect(File::allFiles($path))
            ->filter(function ($file) {
                return str_ends_with($file->getFilename(), '.blade.php');
            })
            ->count();
    }

    /**
     * Count all PHP files in project.
     */
    protected function countAllPhpFiles(string $path): int
    {
        return collect(File::allFiles($path))
            ->filter(function ($file) {
                return $file->getExtension() === 'php';
            })
            ->count();
    }

    /**
     * Calculate directory size.
     */
    protected function directorySize(string $directory): int
    {
        if (! File::exists($directory)) {
            return 0;
        }

        $size = 0;

        foreach (File::allFiles($directory) as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    /**
     * Convert bytes into readable format.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
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
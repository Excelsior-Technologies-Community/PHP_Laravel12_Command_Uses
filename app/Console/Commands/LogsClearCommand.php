<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LogsClearCommand extends Command
{
    protected $signature = 'logs:clear';

    protected $description = 'Clear log files';


    public function handle()
    {

        $this->newLine();

        $this->info('Searching Log Files...');


        $logPath = storage_path('logs');


        $files = File::files($logPath);


        $count = 0;


        foreach ($files as $file) {

            File::delete($file);

            $this->info('✔ ' . $file->getFilename() . ' deleted');

            $count++;
        }


        $this->newLine();

        $this->line("Total Files Deleted : {$count}");


        return self::SUCCESS;
    }
}

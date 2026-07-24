<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class StorageCleanCommand extends Command
{
    protected $signature = 'storage:clean';

    protected $description = 'Clean storage';


    public function handle()
    {

        $this->newLine();

        $this->info('Cleaning Storage...');


        $path = storage_path('app/temp');


        if (!File::exists($path)) {

            File::makeDirectory($path, 0755, true);
        }


        $files = File::files($path);


        foreach ($files as $file) {

            File::delete($file);

            $this->info(
                '✔ ' . $file->getFilename() . ' deleted'
            );
        }


        $this->newLine();

        $this->info(
            'Storage Cleaned Successfully.'
        );


        return self::SUCCESS;
    }
}

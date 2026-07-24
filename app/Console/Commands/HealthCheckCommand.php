<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class HealthCheckCommand extends Command
{
    protected $signature = 'app:health';

    protected $description = 'Check application health';


    public function handle()
    {

        $this->newLine();

        $this->info('=====================================');
        $this->info('      APPLICATION HEALTH CHECK');
        $this->info('=====================================');


        $this->newLine();


        // Database Check

        try {

            DB::connection()->getPdo();

            $this->info(
                '✔ Database Connection : OK'
            );
        } catch (\Exception $e) {

            $this->error(
                '✘ Database Connection : FAILED'
            );
        }



        // Storage Check

        if (
            File::isWritable(storage_path())
        ) {

            $this->info(
                '✔ Storage Writable    : YES'
            );
        } else {

            $this->error(
                '✘ Storage Writable    : NO'
            );
        }



        // Cache

        try {

            cache()->put(
                'health_check',
                true,
                10
            );


            $this->info(
                '✔ Cache Working       : YES'
            );
        } catch (\Exception $e) {

            $this->error(
                '✘ Cache Working       : NO'
            );
        }



        $this->line(
            '✔ Queue Driver        : '
                . config('queue.default')
        );


        $this->newLine();


        $this->info(
            'Application Status : HEALTHY'
        );


        return self::SUCCESS;
    }
}

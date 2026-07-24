<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GeneratePasswordCommand extends Command
{
    protected $signature = 'password:generate';

    protected $description = 'Generate random password';


    public function handle()
    {

        $this->newLine();

        $this->info('=====================================');
        $this->info('     RANDOM PASSWORD GENERATOR');
        $this->info('=====================================');


        $this->newLine();


        $password = Str::random(12);


        $this->line(
            "Generated Password:"
        );


        $this->info(
            $password
        );


        $this->newLine();


        $this->info(
            '✔ Password generated successfully.'
        );


        return self::SUCCESS;
    }
}

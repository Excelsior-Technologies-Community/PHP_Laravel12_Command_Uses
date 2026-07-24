<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SystemInfoCommand extends Command
{
    protected $signature = 'system:info';

    protected $description = 'Display system information';


    public function handle()
    {
        $this->newLine();

        $this->info('=====================================');
        $this->info('        SYSTEM INFORMATION');
        $this->info('=====================================');

        $this->newLine();


        $this->line('PHP Version      : ' . phpversion());

        $this->line(
            'Laravel Version  : ' . app()->version()
        );

        $this->line(
            'Environment      : ' . app()->environment()
        );

        $this->line(
            'Timezone         : ' . config('app.timezone')
        );

        $this->line(
            'Memory Limit     : ' . ini_get('memory_limit')
        );


        $this->newLine();


        $this->info(
            '✔ Information displayed successfully.'
        );


        return self::SUCCESS;
    }
}

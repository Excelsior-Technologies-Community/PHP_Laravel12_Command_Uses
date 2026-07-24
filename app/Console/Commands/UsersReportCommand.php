<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class UsersReportCommand extends Command
{
    protected $signature = 'users:report';

    protected $description = 'Display users report';


    public function handle()
    {
        $this->newLine();

        $this->info('=========================');
        $this->info('User Report');
        $this->info('=========================');

        $this->newLine();


        $totalUsers = \App\Models\User::count();


        // Verified users
        $activeUsers = \App\Models\User::whereNotNull('email_verified_at')
            ->count();


        // Not verified users
        $inactiveUsers = $totalUsers - $activeUsers;


        $this->line("Total Users : {$totalUsers}");
        $this->line("Active Users : {$activeUsers}");
        $this->line("Inactive Users : {$inactiveUsers}");


        $this->newLine();


        $this->info('Report Generated Successfully.');


        return self::SUCCESS;
    }
}

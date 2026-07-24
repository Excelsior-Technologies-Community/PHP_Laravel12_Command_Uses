<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseStatsCommand extends Command
{
    protected $signature = 'db:stats';

    protected $description = 'Display database statistics';


    public function handle()
    {
        $this->newLine();

        $this->info('=====================================');
        $this->info('       DATABASE STATISTICS');
        $this->info('=====================================');

        $this->newLine();


        $users = DB::table('users')->count();


        $this->line(
            "Users      : {$users}"
        );


        // Check optional tables

        if (
            DB::getSchemaBuilder()
            ->hasTable('posts')
        ) {

            $posts = DB::table('posts')->count();

            $this->line(
                "Posts      : {$posts}"
            );
        }


        if (
            DB::getSchemaBuilder()
            ->hasTable('products')
        ) {

            $products = DB::table('products')->count();

            $this->line(
                "Products   : {$products}"
            );
        }


        $tables = DB::select('SHOW TABLES');


        $this->newLine();

        $this->line(
            "Total Tables : " . count($tables)
        );


        $this->newLine();


        $this->info(
            '✔ Statistics generated successfully.'
        );


        return self::SUCCESS;
    }
}

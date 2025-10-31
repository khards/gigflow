<?php

use Database\Seeders\CountrySeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use TruncateTable;

    /**
     * Seed the application's database.
     */
    public function run()
    {
//        Model::unguard();
//
//        $this->truncateMultiple([
//            'activity_log',
//            'failed_jobs',
//        ]);
//
//        $this->call(AuthSeeder::class);
//        $this->call(AnnouncementSeeder::class);
//        $this->call(CountrySeeder::class);
//
//        Model::reguard();
    }
}

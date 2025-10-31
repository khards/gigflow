<?php
namespace Database\Seeders;

use Database\Seeders\Traits\TruncateTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Konekt\Address\Seeds\Countries;

class CountrySeeder extends Seeder
{
    use TruncateTable;

    /**
     * Run the database seeds.
     */
    public function run()
    {
        Model::unguard();

        if (env('DB_CONNECTION') == 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;'); //mysql
        }

        $this->truncate('countries');

        $this->call(Countries::class);

        if (env('DB_CONNECTION') == 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;'); //mysql
        }

        Model::reguard();
    }
}

<?php

use Illuminate\Database\Seeder;

class DistrictsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate
        DB::table('districts')->truncate();

        // import from dump.sql
        $path = base_path('database/sql/districts.sql');
        $sql  = file_get_contents($path);

        DB::unprepared($sql);
    }
}

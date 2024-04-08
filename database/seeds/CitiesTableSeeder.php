<?php

use Illuminate\Database\Seeder;

class CitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate
        DB::table('cities')->truncate();

        // import from dump.sql
        $path = base_path('database/sql/cities.sql');
        $sql = file_get_contents($path);

        DB::unprepared($sql);
    }
}

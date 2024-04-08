<?php

use Illuminate\Database\Seeder;

class BOCustomersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate
        DB::table('b_o_customers')->truncate();

        // import from dump.sql
        $path = base_path('database/sql/b_o_customers.sql');
        $sql = file_get_contents($path);

        DB::unprepared($sql);
    }
}

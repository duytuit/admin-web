<?php

use Illuminate\Database\Seeder;

class BoCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate
        DB::table('bo_categories')->truncate();

        // import from dump.sql
        $path = base_path('database/sql/bo_categories.sql');
        $sql = file_get_contents($path);

        DB::unprepared($sql);
    }
}

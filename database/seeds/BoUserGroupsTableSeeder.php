<?php

use Illuminate\Database\Seeder;

class BoUserGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate
        DB::table('bo_user_groups')->truncate();

        // import from dump.sql
        $path = base_path('database/sql/bo_user_groups.sql');
        $sql = file_get_contents($path);

        DB::unprepared($sql);
    }
}

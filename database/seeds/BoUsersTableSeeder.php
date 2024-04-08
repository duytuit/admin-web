<?php

use App\Models\BoUser;
use Illuminate\Database\Seeder;

class BoUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate
        DB::table('bo_users')->truncate();

        // import from dump.sql
        $path = base_path('database/sql/bo_users.sql');
        $sql  = file_get_contents($path);

        DB::unprepared($sql);
    }
}

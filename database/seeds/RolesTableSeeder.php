<?php

use App\Models\BoUser;
use Illuminate\Database\Seeder;
use App\Models\RoleUser;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate
        DB::table('roles')->truncate();
        DB::table('role_users')->truncate();

        // import from dump.sql
        $path = base_path('database/sql/roles.sql');
        $sql  = file_get_contents($path);

        DB::unprepared($sql);

        $user = BoUser::create([
            'ub_id'          => time() + 10,
            'ub_account_tvc' => 'SUPER@DXMB',
            'ub_title'       => 'Super Admin',
            'ub_email'       => 'admin@dxmb.vn',
            'ub_phone'       => '0987654321',
            'ub_status'      => '1',
            'password'       => Hash::make('Admin@123'),
        ]);

        RoleUser::create([
            'role_id'   => 1,
            'user_id'   => $user->id,
            'user_type' => 'user',
        ]);

        $user = BoUser::create([
            'ub_id'          => time() + 20,
            'ub_account_tvc' => 'ADMIN@DXMB',
            'ub_title'       => 'Administrator',
            'ub_email'       => 'admin@dxmb.vn',
            'ub_phone'       => '0987654321',
            'ub_status'      => '1',
            'password'       => Hash::make('Admin@123'),
        ]);

        RoleUser::create([
            'role_id'   => 1,
            'user_id'   => $user->id,
            'user_type' => 'user',
        ]);

        $user = BoUser::create([
            'ub_id'          => time() + 30,
            'ub_account_tvc' => 'TEST01@DXMB',
            'ub_title'       => 'Test 01',
            'ub_email'       => 'test01@dxmb.vn',
            'ub_phone'       => '0987654321',
            'ub_status'      => '1',
            'password'       => Hash::make('Admin@123'),
        ]);

        RoleUser::create([
            'role_id'   => 3,
            'user_id'   => $user->id,
            'user_type' => 'user',
        ]);

        $user = BoUser::create([
            'ub_id'          => time() + 40,
            'ub_account_tvc' => 'TEST02@DXMB',
            'ub_title'       => 'Test 02',
            'ub_email'       => 'test02@dxmb.vn',
            'ub_phone'       => '0987654321',
            'ub_status'      => '1',
            'password'       => Hash::make('Admin@123'),
        ]);

        RoleUser::create([
            'role_id'   => 4,
            'user_id'   => $user->id,
            'user_type' => 'user',
        ]);

        $user = BoUser::create([
            'ub_id'          => time() + 50,
            'ub_account_tvc' => 'TEST03@DXMB',
            'ub_title'       => 'Test 03',
            'ub_email'       => 'test03@dxmb.vn',
            'ub_phone'       => '0987654321',
            'ub_status'      => '1',
            'password'       => Hash::make('Admin@123'),
        ]);

        RoleUser::create([
            'role_id'   => 5,
            'user_id'   => $user->id,
            'user_type' => 'user',
        ]);

        $user = BoUser::create([
            'ub_id'          => time() + 60,
            'ub_account_tvc' => 'TEST04@DXMB',
            'ub_title'       => 'Test 04',
            'ub_email'       => 'test04@dxmb.vn',
            'ub_phone'       => '0987654321',
            'ub_status'      => '1',
            'password'       => Hash::make('Admin@123'),
        ]);

        RoleUser::create([
            'role_id'   => 5,
            'user_id'   => $user->id,
            'user_type' => 'user',
        ]);

        RoleUser::create([
            'role_id'   => 4,
            'user_id'   => $user->id,
            'user_type' => 'user',
        ]);

    }
}

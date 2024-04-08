<?php

use App\Models\PublicUser\Users;
use Illuminate\Database\Seeder;

class UserPublicTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate
        DB::table('pub_users')->truncate();

        Users::create([
            'email'     => 'admin@dxmb.vn',
            'status'    => 1,
            'password'  => Hash::make('123456Qwert'),
        ]);
    }
}

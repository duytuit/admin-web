<?php

use App\Models\UserPartner;
use Illuminate\Database\Seeder;

class UserPartnersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate
        DB::table('user_partners')->truncate();

        UserPartner::create([
            'user_id'   => 1,
            'full_name' => 'Nguyễn Xuân Mạnh',
            'email'     => 'manhnx@dxmb.vn',
            'phone'     => '0987654321',
            'status'    => '1',
            'password'  => Hash::make('Admin@123'),
        ]);
    }
}

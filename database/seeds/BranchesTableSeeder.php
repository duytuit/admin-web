<?php

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Partner;
use App\Models\BoUser;

class BranchesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Branch::truncate();

        $faker = \Faker\Factory::create();

        $partners = Partner::all();

        foreach ($partners as $partner) {
            $number = $faker->numberBetween(1, 5);

            $time = $faker->dateTimeBetween('-365 days', '- 30 days');

            for ($i = 0; $i < $number; $i++) {
                $city = $faker->randomElement(['Hà Nội', 'Sài Gòn', 'Đà Nẵng', 'Hải Phòng', 'Bắc Giang']);
                $title = $partner->name . ' - Chi nhánh ' . $city;

                $user_id = $faker->numberBetween(1, 5);
                $user = BoUser::find($user_id);

                $data = [
                    'user_id' => $user->id,
                    'user_name' => $user->ub_title,
                    'partner_id' => $partner->id,
                    'partner_name' => $partner->name,
                    'title' => $title,
                    'hotline' => $faker->phoneNumber,
                    'city' => '01',
                    'district' => '001',
                    'address' => $faker->address,
                    'info' => $faker->paragraph,
                    'representative' => $faker->name,
                    'status' => random_int(0, 1),
                    'created_at' => $time,
                    'updated_at' => $time,
                ];

                Branch::create($data);
            }
        }
    }
}

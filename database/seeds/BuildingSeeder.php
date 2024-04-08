<?php

use Illuminate\Database\Seeder;
use App\Models\Building\Building;

class BuildingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Building::truncate();
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 5; $i++) {
            Building::create([
                'name' => $faker->unique->name,
                'description' => $faker->text,
                'address' => $faker->address,
                'phone' => '037904257'.$i,
                'email' => $faker->email,
            ]);
        }
    }
}

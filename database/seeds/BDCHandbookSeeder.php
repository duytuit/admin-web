<?php

use Illuminate\Database\Seeder;
use App\Models\BuildingHandbook\BuildingHandbook;
use App\Models\BuildingHandbookCategory\BuildingHandbookCategory;
use App\Models\BuildingHandbookType\BuildingHandbookType;

class BDCHandbookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BuildingHandbook::truncate();
        BuildingHandbookCategory::truncate();
        BuildingHandbookType::truncate();
        $faker = \Faker\Factory::create();

        BuildingHandbookCategory::create([
            'name' => 'Hồ bơi',
            'parent_id' => 0
        ]);

        BuildingHandbookCategory::create([
            'name' => 'Cầu trượt',
            'parent_id' => 0
        ]);

        BuildingHandbookCategory::create([
            'name' => 'Tắm',
            'parent_id' => 1
        ]);


        BuildingHandbookType::create([
            'name' => '1 tháng'
        ]);

        BuildingHandbookType::create([
            'name' => '2 tháng'
        ]);

        BuildingHandbookType::create([
            'name' => '4 tháng'
        ]);


        $limit = 20;
        for ($i = 0; $i < $limit; $i++) {
            BuildingHandbook::create([
                'bdc_building_id' => 1,
                'title' => $faker->name,
                'content' => $faker->text,
                'bdc_handbook_category_id' => $faker->numberBetween(1,3),
                'bdc_handbook_type_id' => $faker->numberBetween(1,3),
                'status' => $faker->numberBetween(1,2),
                'pub_profile_id' => $faker->numberBetween(1,2),
            ]);
        }
    }
}

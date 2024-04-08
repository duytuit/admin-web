<?php

use Illuminate\Database\Seeder;
use App\Models\ToolAppVersions\ToolAppVersions;

class ToolAppVersionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ToolAppVersions::truncate();
        $faker = \Faker\Factory::create();

        $limit = 20;
        for($i = 0; $i < $limit; $i++) {
            ToolAppVersions::create([
                'app_id' => 'buildingcare',
                'versions' => 1,
                'status' => 1,
            ]);
        }
    }
}

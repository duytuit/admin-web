<?php

use Illuminate\Database\Seeder;
use App\Models\WorkDiary\WorkDiary;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WorkDiary::truncate();
        $faker = \Faker\Factory::create();;

        $limit = 20;
        for( $i = 0; $i < $limit; $i++ ) {
            WorkDiary::create([
                'bdc_building_id'      => 1,
                'bdc_department_id'    => 1,
                'title'                => $faker->name,
                'description'          => $faker->text,
                'status'               => $faker->numberBetween(0, 1),
                'bdc_request_id'       => 1,
                'assign_to'            => $faker->numberBetween(1,4),
                'watchs'               => $faker->name,
                'created_by'           => $faker->numberBetween(1,4),
                'updated_by'           => $faker->numberBetween(1,4),
                'start_at'             => date('Y-m-d', strtotime('now -' . $faker->unique()->numberBetween(1, 1000) . ' days')),
                'end_at'               => date('Y-m-d', strtotime('now +' . $faker->unique()->numberBetween(1, 365) . ' days')),
                'related_to'           => 1,
                'bdc_apartment_id'     => 1,
                'logs'                 => $faker->text,
                'review_note'          => $faker->text,
            ]);
        }
    }
}

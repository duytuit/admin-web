<?php

use Illuminate\Database\Seeder;
use App\Models\Asset\Asset;
use App\Models\AssetType\AssetType;
use App\Models\Period\Period;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AssetType::truncate();
        Period::truncate();
        Asset::truncate();
        $faker = \Faker\Factory::create();

        AssetType::create([
            'name' => 'Tài sản',
        ]);

        AssetType::create([
            'name' => 'CCDC',
        ]);

        AssetType::create([
            'name' => 'Tài sản khác',
        ]);


        Period::create([
            'name' => '1 tháng',
            'carbon_fc' => 1,
            'bdc_building_id' => 1,
        ]);

        Period::create([
            'name' => '2 tháng',
            'carbon_fc' => 2,
            'bdc_building_id' => 1,
        ]);

        Period::create([
            'name' => '4 tháng',
            'carbon_fc' => 4,
            'bdc_building_id' => 1,
        ]);

        Period::create([
            'name' => '6 tháng',
            'carbon_fc' => 6,
            'bdc_building_id' => 1,
        ]);

        Period::create([
            'name' => 'Quý',
            'carbon_fc' => 3,
            'bdc_building_id' => 1,
        ]);

        Period::create([
            'name' => 'Năm',
            'carbon_fc' => 12,
            'bdc_building_id' => 1,
        ]);
        // for ($i = 0; $i < 10000; $i++) {
        //     $dataFake = [
        //         'name' => $faker->name,
        //         'bdc_assets_type_id' => $faker->numberBetween(1,3),
        //         'buying_date' => date('Y-m-d', strtotime('now -' . $faker->numberBetween(365, 500) . ' days')),
        //         'using_peroid' => $faker->numberBetween(24, 96),
        //         'bdc_building_id' => 1,
        //         'maintainance_date' => date('Y-m-d', strtotime('now +' . $faker->numberBetween(1, 7) . ' days')),
        //         'bdc_period_id' => $faker->numberBetween(1,6),
        //         'price' => '10000000',
        //         'quantity' => $faker->numberBetween(1,10),
        //         'asset_note' => $faker->text,
        //         'buyer' =>  $faker->name,
        //         'place' =>  $faker->address,
        //     ];
        //     if ($i < 2000) {
        //         Asset::create($dataFake);
        //     } elseif($i >= 2000 && $i < 4000) {
        //         $dataFake['maintainance_date'] = date('Y-m-d', strtotime('now - 365 days +' . $faker->numberBetween(1, 7) . ' days'));
        //         Asset::create($dataFake);
        //     } elseif($i >= 4000 && $i < 6000) {
        //         $dataFake['maintainance_date'] = date('Y-m-d', strtotime('now -' . $faker->numberBetween(1, 100) . ' days'));
        //         Asset::create($dataFake);
        //     } else {
        //         $dataFake['maintainance_date'] = date('Y-m-d', strtotime('now +' . $faker->numberBetween(8, 30) . ' days'));
        //         Asset::create($dataFake);
        //     }

        // }
    }
}

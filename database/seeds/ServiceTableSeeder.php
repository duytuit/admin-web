<?php

use Illuminate\Database\Seeder;
use App\Models\Service\Service;

class ServiceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Service::truncate();
        \App\Models\BdcProgressives\Progressives::truncate();
        \App\Models\BdcProgressivePrice\ProgressivePrice::truncate();
        \App\Models\BdcProgressivePrice\ProgressivePrice::truncate();
        \App\Models\Service\ServicePriceDefault::truncate();
        $faker = \Faker\Factory::create();

        $service1 = Service::create([
            'bdc_building_id' => 0,
            'bdc_period_id' => 1,
            'company_id' => 0,
            'name' => 'Phí dịch vụ',
            'description' => 'Phí dịch vụ',
            'unit' => 'VND',
            'bill_date' => $faker->numberBetween(1, 31),
            'payment_deadline' => $faker->numberBetween(1, 31),
            'service_code' => $faker->uuid,
            'status' => 1,
            'first_time_active' => \Carbon\Carbon::now(),
        ]);
        $progressive1 = \App\Models\BdcProgressives\Progressives::create([
            'name' => $service1->name,
            'building_id' => 0,
            'company_id' => 0,
            'bdc_price_type_id' => 1
        ]);
        if ($progressive1->bdc_price_type_id == 1) {
            \App\Models\BdcProgressivePrice\ProgressivePrice::create([
                'name' => $service1->name,
                'from' => 0,
                'to' => 0,
                'price' => 50000,
                'progressive_id' => $progressive1->id,
            ]);
        } else {
            \App\Models\BdcProgressivePrice\ProgressivePrice::create([
                'name' => $service1->name,
                'from' => 1,
                'to' => 10,
                'price' => 50000,
                'progressive_id' => $progressive1->id
            ]);
            \App\Models\BdcProgressivePrice\ProgressivePrice::create([
                'name' => $service1->name,
                'from' => 11,
                'to' => 20,
                'price' => 55000,
                'progressive_id' => $progressive1->id
            ]);
            \App\Models\BdcProgressivePrice\ProgressivePrice::create([
                'name' => $service1->name,
                'from' => 21,
                'to' => 30,
                'price' => 60000,
                'progressive_id' => $progressive1->id
            ]);
        }
        \App\Models\Service\ServicePriceDefault::create([
            'bdc_building_id' => $service1->bdc_building_id,
            'bdc_service_id' => $service1->id,
            'bdc_price_type_id' => 1,
            'name' => $service1->name,
            'price' => 50000,
            'progressive_id' => $progressive1->id,
        ]);
        $service2 = Service::create([
            'bdc_building_id' => 0,
            'bdc_period_id' => 1,
            'company_id' => 0,
            'name' => 'Phí điện',
            'description' => 'Phí điện',
            'unit' => 'VND',
            'bill_date' => $faker->numberBetween(1, 31),
            'payment_deadline' => $faker->numberBetween(1, 31),
            'service_code' => $faker->uuid,
            'status' => 1,
            'first_time_active' => \Carbon\Carbon::now(),
        ]);
        $progressive2 = \App\Models\BdcProgressives\Progressives::create([
            'name' => $service2->name,
            'building_id' => 0,
            'company_id' => 0,
            'bdc_price_type_id' => 2
        ]);
        if ($progressive2->bdc_price_type_id == 1) {
            \App\Models\BdcProgressivePrice\ProgressivePrice::create([
                'name' => $service2->name,
                'from' => 0,
                'to' => 0,
                'price' => 50000,
                'progressive_id' => $progressive2->id,
            ]);
        } else {
            \App\Models\BdcProgressivePrice\ProgressivePrice::create([
                'name' => $service2->name,
                'from' => 1,
                'to' => 10,
                'price' => 50000,
                'progressive_id' => $progressive2->id
            ]);
            \App\Models\BdcProgressivePrice\ProgressivePrice::create([
                'name' => $service2->name,
                'from' => 11,
                'to' => 20,
                'price' => 55000,
                'progressive_id' => $progressive2->id
            ]);
            \App\Models\BdcProgressivePrice\ProgressivePrice::create([
                'name' => $service2->name,
                'from' => 21,
                'to' => 30,
                'price' => 60000,
                'progressive_id' => $progressive2->id
            ]);
        }
        \App\Models\Service\ServicePriceDefault::create([
            'bdc_building_id' => $service2->bdc_building_id,
            'bdc_service_id' => $service2->id,
            'bdc_price_type_id' => 2,
            'name' => $service2->name,
            'price' => 50000,
            'progressive_id' => $progressive2->id,
        ]);
        $limit = 10;
        for ($i = 0; $i < $limit; $i++) {
            $service = Service::create([
                'bdc_building_id' => 0,
                'bdc_period_id' => 1,
                'name' => $faker->name,
                'description' => $faker->text(500),
                'unit' => 'VND',
                'bill_date' => $faker->numberBetween(1, 31),
                'payment_deadline' => $faker->numberBetween(1, 31),
                'company_id' => 0,
                'service_code' => $faker->uuid,
                'status' => $faker->numberBetween(0, 1),
                'first_time_active' => \Carbon\Carbon::now(),
            ]);
            $progressive = \App\Models\BdcProgressives\Progressives::create([
                'name' => $service->name." Company",
                'building_id' => 0,
                'company_id' => 0,
                'bdc_price_type_id' => 1
            ]);
            \App\Models\BdcProgressivePrice\ProgressivePrice::create([
                'name' => $service->name,
                'from' => 0,
                'to' => 0,
                'price' => 50000,
                'progressive_id' => $progressive->id,
            ]);
            \App\Models\Service\ServicePriceDefault::create([
                'bdc_building_id' => $service->bdc_building_id,
                'bdc_service_id' => $service->id,
                'bdc_price_type_id' => 1,
                'name' => $service->name,
                'price' => 50000,
                'progressive_id' => $progressive->id,
            ]);
        }
    }
}

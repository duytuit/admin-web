<?php

use Illuminate\Database\Seeder;

class BdcServicePriceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('bdc_service_price')->truncate();

        DB::table('bdc_service_price')->create([
            'bdc_service_id' => '1',
            'bdc_price_type_id' => '1',
            'from' => '0',
            'to' => '0',
            'unit_price' => '500000'
        ]);
    }
}

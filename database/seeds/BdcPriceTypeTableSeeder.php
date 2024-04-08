<?php

use Illuminate\Database\Seeder;

class BdcPriceTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('bdc_price_type')->truncate();

        DB::table('bdc_price_type')->insert([
            'name' => 'Một giá'
        ]);

        DB::table('bdc_price_type')->insert([
            'name' => 'Lũy tiến'
        ]);
    }
}

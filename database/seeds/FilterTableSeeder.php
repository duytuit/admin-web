<?php

use App\Models\Filter;
use Illuminate\Database\Seeder;

class FilterTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch.
        Filter::create([
            'key'        => "price",
            'user_id'    => 2087,
            'title'      => "Khoảng giá",
            'value'      => "0 - 500 triệu",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }
}

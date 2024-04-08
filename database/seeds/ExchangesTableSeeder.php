<?php

use App\Models\Exchange;
use Illuminate\Database\Seeder;

class ExchangesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch.
        Exchange::truncate();

        $faker = \Faker\Factory::create();

        $list = [
            "6 Degrees Dink Eat Meat",
            "Crystal Jade Palace",
            "Nhà hàng Skyline HaNoi",
            "Trill Rooftop Café",
            "Nhà hàng RedBean Trendy",
            "Cau Go Vietnamese Cuisine Restaurant",
            "Cosa Nostra",
            "Nhà hàng Phương Quyên",
            "Food House",
            "Hot'n Tasty",
            "Lẩu ếch Trúc Bạch",
            "Lẩu Thái Quang Trung",
            "Quán lẩu hoa quả ở đường Núi Trúc",
            "Lẩu cháo Quán Sứ",
            "Tửu quán Huynh Đệ",
            "Độc Quán",
            "Lương Sơn Quán",
            "Chuỗi Phủi Quán",
            "Quán nướng Long Vũ",
            "Cà Kê Quán",
            "Chị Em Quán",
            "Quán ngói 13",
            "My Way Seafood",
            "Nhà hàng Hương Sen",
            "Nhà hàng biển Đông",
            "Nhà hàng thế giới Hải Sản",
            "Quán phở 10 Lý Quốc Sư",
            "Quán phở Ngọc Vượng - Đội Cấn",
            "Quán phở gà hàng Điếu",
            "Quán phở cuốn Hương Mai",
            "Bún Mọc - Hàng Lược",
            "Bún chả Đắc Kim - Hàng Mành",
            "Bún Thang Cầu Gỗ",
            "Xôi xéo Oanh Oanh",
        ];

        // And now, let's create a few Exchanges in our database:
        foreach ($list as $key => $name) {
            $title = $faker->sentence;

            Exchange::create([
                'cb_id'          => strtotime(date('Y-m-d H:i:s')) + $key,
                'user_id'        => 1536638938,
                'name'           => $name,
                'address'        => $faker->address,
                'hotline'        => $faker->phoneNumber,
                'description'    => $faker->paragraph(),
                'status'         => random_int(0, 1),
                'city'           => '01',
                'district'       => '001',
                'location'       => '{"lat": 21.0196181, "long": 105.726572}',
            ]);
        }
    }
}

<?php

use App\Models\Category;
use App\Models\UrlAlias;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch.
        Category::truncate();

        $faker = \Faker\Factory::create();

        $categories = [
            ['type' => 'article', 'title' => 'Thông báo'],
            ['type' => 'article', 'title' => 'Tin thị trường'],
            ['type' => 'event', 'title' => 'Sự kiện'],
            ['type' => 'voucher', 'title' => 'Khuyến mại'],
        ];

        foreach ($categories as $item) {
            $item['user_id'] = 1;
            $item['content'] = $faker->paragraph;

            $category = Category::create($item);

            $uri  = 'categories/' . $category->id;
            $slug = str_slug($category->title);
            $url  = UrlAlias::saveAlias($uri, $slug, '');

            $category->url_id = $url->id;
            $category->alias  = $url->alias;
            $category->save();
        }
    }
}

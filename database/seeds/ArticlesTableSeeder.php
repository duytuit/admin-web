<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\UrlAlias;
use Illuminate\Database\Seeder;

class ArticlesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch.
        Article::truncate();

        $faker = \Faker\Factory::create();

        $categories = Category::select(['id', 'type'])->get();

        $list = range(5, 200);

        foreach ($categories as $category) {
            $number_article = $faker->numberBetween(30, 50);

            $time = $faker->dateTimeBetween('-365 days', '- 30 days');

            for ($i = 0; $i < $number_article; $i++) {
                $title = $faker->sentence;

                $number_customer = $faker->numberBetween(1, 5);
                $customer_ids    = $faker->randomElements($list, $number_customer);

                $data = [
                    'user_id'      => $faker->numberBetween(1, 5),
                    'customer_ids' => $customer_ids,
                    'type'         => $category->type,
                    'category_id'  => $category->id,
                    'title'        => $title,
                    'summary'      => $faker->paragraph(),
                    'content'      => $faker->paragraphs(3, true),
                    'num_views'    => $faker->numberBetween(300, 1000),
                    'private'      => random_int(0, 1),
                    'status'       => random_int(0, 1),
                    'publish_at'   => $time,
                    'created_at'   => $time,
                    'updated_at'   => $time,
                ];

                if ($category->type == 'event') {
                    $start_at = $faker->dateTimeInInterval($time, '+ 5 days');
                    $end_at   = $faker->dateTimeInInterval($start_at, '+ 15 days');

                    $data['address']  = $faker->address();
                    $data['start_at'] = $start_at;
                    $data['end_at']   = $end_at;
                }

                if ($category->type == 'voucher') {
                    $prefix  = $faker->randomElement(['HOT', 'SPA', 'DEAL', 'SALE']);
                    $percent = $faker->randomElement([10, 15, 20, 25, 30, 35, 40, 45, 50]);

                    $data['number']       = $faker->randomElement([50, 100, 150, 200, 250, 300]);
                    $data['voucher_type'] = $faker->randomElement(['public', 'request']);
                    $data['voucher_code'] = $prefix . '-' . $percent . '-OFF';
                }

                $article = Article::create($data);

                if ($article->type == 'event') {
                    $uri = 'events/' . $article->id;
                } elseif ($article->type == 'voucher') {
                    $uri = 'vouchers/' . $article->id;
                } else {
                    $uri = 'articles/' . $article->id;
                }

                $url = UrlAlias::saveAlias($uri, str_slug($title));

                $article->url_id = $url->id;
                $article->alias  = $url->alias;
                $article->save();
            }
        }
    }
}

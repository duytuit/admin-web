<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\UrlAlias;
use Illuminate\Database\Seeder;

class PostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch.
        Post::truncate();

        $faker = \Faker\Factory::create();

        $categories = Category::select(['id', 'type'])->get();

        $list = range(5, 150);

        foreach ($categories as $category) {
            $number_article = $faker->numberBetween(20, 30);

            $time = $faker->dateTimeBetween('-365 days', '- 30 days');

            for ($i = 0; $i < $number_article; $i++) {
                $title = $faker->sentence;

                $number_customer = $faker->numberBetween(20, 30);
                $customer_ids    = $faker->randomElements($list, $number_customer);

                $data = [
                    'user_id'      => $faker->numberBetween(1, 5),
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
                    'notify'       => [
                        'send_mail'      => 0,
                        'send_sms'       => 0,
                        'send_app'       => 1,
                        'all_selected'   => 1,
                        'group_selected' => [],
                        'customer_ids'   => $customer_ids,
                    ],
                ];

                $adj = $faker->randomElement(['+', '-']);
                $day = $faker->numberBetween(2, 5);

                $start_at = $faker->dateTimeInInterval('0 days', "$adj $day days");
                $end_at   = $faker->dateTimeInInterval($start_at, '+ 15 days');

                if ($category->type == 'event') {
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

                    $data['start_at'] = $start_at;
                    $data['end_at']   = $end_at;
                }

                $post = Post::create($data);

                if ($post->type == 'event') {
                    $uri = 'events/' . $post->id;
                } elseif ($post->type == 'voucher') {
                    $uri = 'vouchers/' . $post->id;
                } else {
                    $uri = 'articles/' . $post->id;
                }

                $url = UrlAlias::saveAlias($uri, str_slug($title));

                $post->url_id = $url->id;
                $post->alias  = $url->alias;
                $post->save();
            }
        }
    }
}

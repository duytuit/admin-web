<?php

use App\Models\Article;
use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VouchersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch.
        Voucher::truncate();

        $faker = \Faker\Factory::create();

        $articles = Article::where('type', 'voucher')->get();

        foreach ($articles as $article) {
            $number = $faker->numberBetween(10, 30);

            for ($i = 0; $i < $number; $i++) {
                $time = $faker->dateTimeInInterval('-30 days', '- 0 days');

                $check_in = null;
                if ($faker->boolean()) {
                    $check_in = $faker->dateTimeInInterval('-15 days', '- 0 days');
                }

                Voucher::create([
                    'article_id' => $article->id,
                    'user_id'    => $faker->numberBetween(10, 200),
                    'user_type'  => 'customer',
                    'check_in'   => $check_in,
                    'created_at' => $time,
                    'updated_at' => $time,
                ]);
            }
        }
    }
}

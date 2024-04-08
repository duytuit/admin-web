<?php

use App\Models\Post;
use App\Models\PostRegister;
use Illuminate\Database\Seeder;

class PostRegistersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PostRegister::truncate();

        $faker = \Faker\Factory::create();

        $posts = Post::select(['id', 'type'])->whereIn('type', ['event', 'voucher'])->get();

        foreach ($posts as $post) {
            $number = $faker->numberBetween(10, 30);

            for ($i = 0; $i < $number; $i++) {
                $time = $faker->dateTimeInInterval('-30 days', '- 0 days');

                $check_in = null;
                if ($faker->boolean()) {
                    $check_in = $faker->dateTimeInInterval('-15 days', '- 0 days');
                }

                PostRegister::create([
                    'post_id'    => $post->id,
                    'post_type'  => $post->type,
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

<?php

use App\Models\Post;
use App\Models\PostFollow;
use Illuminate\Database\Seeder;

class PostFollowsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PostFollow::truncate();

        $faker = \Faker\Factory::create();

        $posts = Post::select(['id', 'type'])->get();

        foreach ($posts as $post) {
            $number = $faker->numberBetween(0, 10);

            for ($i = 0; $i < $number; $i++) {
                $time = $faker->dateTimeInInterval('-30 days', '- 0 days');

                PostFollow::create([
                    'post_id'    => $post->id,
                    'post_type'  => $post->type,
                    'user_id'    => $faker->numberBetween(10, 200),
                    'user_type'  => 'customer',
                    'created_at' => $time,
                    'updated_at' => $time,
                ]);
            }
        }
    }
}

<?php

use App\Models\Post;
use App\Models\PostEmotion;
use Illuminate\Database\Seeder;

class PostEmotionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PostEmotion::truncate();

        $faker = \Faker\Factory::create();

        $posts = Post::select(['id', 'type'])->get();

        foreach ($posts as $post) {
            $number = $faker->numberBetween(0, 20);

            for ($i = 0; $i < $number; $i++) {
                $time = $faker->dateTimeInInterval('-30 days', '- 0 days');

                PostEmotion::create([
                    'post_id'    => $post->id,
                    'post_type'  => $post->type,
                    'user_id'    => $faker->numberBetween(10, 200),
                    'user_type'  => 'customer',
                    'emotion'    => $faker->randomElement(['like', 'love', 'haha', 'wow', 'sad', 'angry']),
                    'created_at' => $time,
                    'updated_at' => $time,
                ]);
            }
        }
    }
}

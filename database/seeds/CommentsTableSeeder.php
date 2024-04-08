<?php

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Seeder;

class CommentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch.
        Comment::truncate();

        $faker = \Faker\Factory::create();

        $posts = Post::select(['id', 'type', 'url_id'])->get();

        foreach ($posts as $post) {
            $level1 = $faker->numberBetween(3, 10);

            // Bình luận level 1
            for ($i = 0; $i < $level1; $i++) {
                $time = $faker->dateTimeInInterval('-2 years', '- 10 days');

                $comment = Comment::create([
                    'url_id'     => $post->url_id,
                    'type'       => $post->type,
                    'post_id'    => $post->id,
                    'parent_id'  => 0,
                    'user_id'    => $faker->numberBetween(10, 15),
                    'user_type'  => 'customer',
                    'content'    => $faker->paragraph,
                    'rating'     => $faker->numberBetween(0, 5),
                    'status'     => 1,
                    'created_at' => $time,
                    'updated_at' => $time,
                ]);

                $level2 = $faker->numberBetween(0, 5);

                for ($j = 0; $j < $level2; $j++) {
                    $time = $faker->dateTimeBetween('-10 days');

                    Comment::create([
                        'url_id'     => $post->url_id,
                        'type'       => $post->type,
                        'post_id'    => $post->id,
                        'parent_id'  => $comment->id,
                        'user_id'    => $faker->numberBetween(10, 15),
                        'user_type'  => 'customer',
                        'content'    => $faker->paragraph,
                        'rating'     => $faker->numberBetween(0, 5),
                        'status'     => $faker->numberBetween(0, 1),
                        'created_at' => $time,
                        'updated_at' => $time,
                    ]);
                }
            }
        }
    }
}

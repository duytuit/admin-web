<?php

use App\Models\Comment;
use App\Models\Feedback;
use Illuminate\Database\Seeder;

class FeedbackTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch.
        Feedback::truncate();

        $faker = \Faker\Factory::create();

        $customer_id = $faker->numberBetween(10, 15);

        // And now, let's create a few Partners in our database:
        for ($i = 0; $i < 25; $i++) {
            $feedback = Feedback::create([
                'pub_user_profile_id' => $customer_id,
                'type'        => $faker->randomElement(['user', 'product', 'service', 'other']),
                'title'       => $faker->sentence,
                'content'     => $faker->paragraph(),
                'rating'      => $faker->numberBetween(1, 5),
                'attached'    => [
                    'images' => [
                        '/attached/images/img-01.jpg',
                        '/attached/images/img-02.jpg',
                        '/attached/images/img-03.jpg',
                    ],
                    'files'  => [
                        '/attached/files/file-01.zip',
                    ],
                ],
                'status'      => $faker->numberBetween(0, 1),
            ]);

            $level1 = $faker->numberBetween(3, 10);

            // Bình luận level 1
            for ($j = 0; $j < $level1; $j++) {
                $time = $faker->dateTimeBetween('-365 days', '- 1 days');

                $user_type = $faker->randomElement(['user', 'customer']);

                if ($user_type == 'user') {
                    $user_id = 1;
                } else {
                    $user_id = $customer_id;
                }

                $comment = Comment::create([
                    'type'       => 'feedback',
                    'post_id'    => $feedback->id,
                    'parent_id'  => 0,
                    'user_id'    => $user_id,
                    'user_type'  => $user_type,
                    'content'    => $faker->paragraph,
                    'rating'     => $faker->numberBetween(0, 5),
                    'status'     => 1,
                    'created_at' => $time,
                    'updated_at' => $time,
                ]);
            }
        }
    }
}

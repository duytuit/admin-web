<?php

use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PostsImageFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        $path = '/media/image/demo';
        $dir  = storage_path() . $path;

        File::deleteDirectory($dir);

        mkdir($dir, 0755, true);

        $posts = Post::select(['id', 'image'])->get();

        foreach ($posts as $post) {
            $post->image = $path . '/' . $faker->image($dir, 720, 480, null, false);
            $post->save();
        }
    }
}

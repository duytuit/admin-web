<?php

use Illuminate\Database\Seeder;

class PostsResponseFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $response = [];

        $this->emotion($response);
        $this->follow($response);
        $this->register($response);
        $this->check_in($response);
        $this->share($response);
        $this->vote($response);
        $this->comment($response);

        // response
        $default = [
            'emotion'  => [
                'like'  => 0,
                'love'  => 0,
                'haha'  => 0,
                'wow'   => 0,
                'sad'   => 0,
                'angry' => 0,
            ],
            'follow'   => 0,
            'register' => 0,
            'check_in' => 0,
            'share'    => 0,
            'vote'     => [
                'rating' => 0,
                'total'  => 0,
            ],
            'comment'  => 0,
        ];

        foreach ($response as $post_id => $item) {
            $input = array_merge($default, $item);

            if (isset($item['emotion'])) {
                $input['emotion'] = array_merge($default['emotion'], $item['emotion']);
            }

            if (isset($item['vote'])) {
                $input['vote'] = array_merge($default['vote'], $item['vote']);
            }

            DB::table('posts')
                ->where('id', $post_id)
                ->update(['response' => json_encode($input)]);
        }
    }

    public function emotion(&$response)
    {
        $sql = "SELECT post_id, emotion, COUNT(*) as total FROM post_emotions
                GROUP BY post_id, emotion ORDER BY post_id";

        $rows = DB::select(DB::raw($sql));

        $emotions = [
            'like'  => 0,
            'love'  => 0,
            'haha'  => 0,
            'wow'   => 0,
            'sad'   => 0,
            'angry' => 0,
        ];

        foreach ($rows as $row) {
            $response[$row->post_id]['emotion'][$row->emotion] = $row->total;
        }
    }

    public function follow(&$response)
    {
        $sql = "SELECT post_id, COUNT(*) as total FROM post_follows
                GROUP BY post_id ORDER BY post_id";

        $rows = DB::select(DB::raw($sql));

        foreach ($rows as $row) {
            $response[$row->post_id]['follow'] = $row->total;
        }
    }

    public function register(&$response)
    {
        $sql = "SELECT post_id, COUNT(*) as total FROM post_registers
                GROUP BY post_id ORDER BY post_id";

        $rows = DB::select(DB::raw($sql));

        foreach ($rows as $row) {
            $response[$row->post_id]['register'] = $row->total;
        }
    }

    public function check_in(&$response)
    {
        $sql = "SELECT post_id, COUNT(*) as total FROM post_registers
                WHERE check_in IS NOT NULL
                GROUP BY post_id ORDER BY post_id";

        $rows = DB::select(DB::raw($sql));

        foreach ($rows as $row) {
            $response[$row->post_id]['check_in'] = $row->total;
        }
    }

    public function share(&$response)
    {
        $sql = "SELECT post_id, COUNT(*) as total FROM post_shares
                GROUP BY post_id ORDER BY post_id";

        $rows = DB::select(DB::raw($sql));

        foreach ($rows as $row) {
            $response[$row->post_id]['share'] = $row->total;
        }
    }

    public function vote(&$response)
    {
        // TODO: post vote
    }

    public function comment(&$response)
    {
        $sql = "SELECT post_id, COUNT(*) as total FROM comments
                GROUP BY post_id ORDER BY post_id";

        $rows = DB::select(DB::raw($sql));

        foreach ($rows as $row) {
            $response[$row->post_id]['comment'] = $row->total;
        }
    }
}

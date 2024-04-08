<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Frontend\Controller;
use App\Models\PostRegister;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;

class EventController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        Carbon::setLocale('vi');
    }

    /**
     * Lưu bản ghi
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request, $id = 0)
    {
        $data            = [];
        $data['article'] = PostRegister::findOrFail($id);

        $data['now']     = Carbon::now();

        try {
            $token = decrypt($request->input('token'));
        } catch (DecryptException $e) {
            $token = '';
        }

        parse_str($token, $input);

        $data['user_id'] = isset($input['user_id']) ? $input['user_id'] : null;

        return view('frontend.events.register', $data);
    }

    public function detail(Request $request, $id = 0)
    {
        $data['request'] = $request->all();
        $data['id'] = $id;

        $data['event'] = Article::findOrFail($id);

        return view('frontend.events.detail', $data);
    }
}

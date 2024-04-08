<?php

namespace App\Repositories\Comments;

//use App\Repositories\Contracts\RepositoryInterface;

use App\Helpers\dBug;
use App\Repositories\Eloquent\Repository;
use App\Util\Debug\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CommentsRespository extends Repository {




    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\Comments\Comments::class;
    }

    public function getOne($colums = 'id', $id)
    {
        return $this->model->where($colums, $id)->first();
    }

    public function getOneSelect(array $select,$colums = 'id', $id)
    {
        return $this->model->select($select)->where($colums, $id)->first();
    }
    public function findIdFB($id)
    {
        return $this->model->findOrFail($id);
    }
    public function findNew($id)
    {
        return $this->model->findOrNew($id);
    }

    public function savePostResponse(&$post)
    {
        $total = $this->model->where([['post_id', $post->id],['type', $post->type]])->count();

        $response = json_decode($post->response,true);

        $response['comment'] = $total;

        $post->response = json_encode($response);
        $post->save();

        return $post;
    }

    public function listUser($where_arr,$select)
    {
        return $this->model->where($where_arr)->select($select)->distinct()->get();
    }

    public function listCommentsFeedback($post_id,$per_page,$parent_id = 0)
    {
        $listserch = $this->model->where('post_id',$post_id)->where('type','feedback')->where('parent_id',$parent_id)->paginate($per_page);
        return $listserch;
    }
    public function countComments($post_id)
    {
        $listserch = $this->model->where('post_id',$post_id)->count();
        return $listserch;
    }
    public function listCommentsFeedbackOrderBy($post_id,$per_page,$parent_id=0)
    {
        Log::info('check_feedback','_2'.json_encode($parent_id).'|'.$post_id);
        $listserch = $this->model->where('post_id',$post_id)->where('type','feedback')->where('parent_id',$parent_id)->orderBy('id','desc')->paginate($per_page);
        return $listserch;
    }
    public function listComments($post_id,$type)
    {
        $listserch = $this->model->where('post_id',$post_id)->where('type',$type)->orderBy('created_at', 'desc')->limit(10)->get();
        return $listserch;
    }
    public function listCommentsByType($type = [],$building_id,$perpage)
    {
        return  $this->model->whereIn('type',$type)
                            ->whereHas('post', function ($query) use ($building_id) {
                                $query->where('bdc_building_id', '=', $building_id);
                            })
                            ->whereHas('userInfo', function ($query) use ($building_id) {
                                $query->where('bdc_building_id', '=', $building_id);
                            })->orderBy('created_at', 'desc')
                        ->orderBy('created_at', 'desc')->with('post','userInfo')->paginate($perpage);
    }

    public function listCommentsPost($post_id,$type,$per_page,$parent_id = 0)
    {
        $listserch = $this->model->where('post_id',$post_id)->where('type',$type)->where('parent_id',$parent_id)->orderBy('created_at','desc')->paginate($per_page);
        return $listserch;
    }

    public function listCommentsById($column,$id)
    {
        $listserch = $this->model->where($column,$id)->get();
        $listserch->load('user','user.profileAll');
//        $listserch->load('user');
        return $listserch->toArray();
    }
    public function listCommentsByIdPerpage($column,$id,$per_page='')
    {
        $listserch = $this->model->where($column,$id)->paginate($per_page);
        $listserch->load('user','user.profileAll');
//        $listserch->load('user');
        if(count($listserch)>0){
            return $listserch->toArray();
        }
        return null;
    }
    public function deleteAt(Request $request)
    {
        $ids = $request->input('ids', []);

        // chuyển sang kiểu array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $list = [];
        foreach ($ids as $id) {
            $list[] = (int) $id;
        }

        $number = $this->model->destroy($list);

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => "Đã xóa $number bản ghi!",
        ];

        if ($request->ajax()) {
            return response()->json($message);
        } else {
            return back()->with('message', $message);
        }
    }
    public function status(Request $request)
    {
        $ids    = $request->input('ids', []);
        $status = $request->input('status', 1);

        // convert to array
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $list = [];
        foreach ($ids as $id) {
            $list[] = (int) $id;
        }

        $this->model->whereIn('id', (array) $list)->update(['status' => (int) $status]);

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => 'Đã cập nhật trạng thái!',
        ];

        if ($request->ajax()) {
            return response()->json($message);
        } else {
            return back()->with('message', $message);
        }
    }
    public function per_page($request)
    {
        $per_page = $request->input('per_page', 20);

        Cookie::queue('per_page', $per_page, 60 * 24 * 30);

        return back();
    }
    public function action($request)
    {
        $method = $request->input('method', '');
        if ($method == 'delete') {
            return $this->deleteAt($request);
        } elseif ($method == 'status') {
            return $this->status($request);
        } elseif ($method == 'per_page') {
            return $this->per_page($request);
        }
        return back();
    }
    public function findFail($id)
    {
        return $this->model->findOrFail($id);
    }
}

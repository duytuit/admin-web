<?php
namespace App\Http\Controllers\Backend;

use App\Commons\clientApi;
use App\Commons\Helper;
use App\Http\Controllers\BuildingController;
use App\Repositories\Posts\PostsRespository;
use Illuminate\Http\Request;
use App\Models\PublicUser\Users;
use App\Repositories\Feedback\FeedbackRespository;
use App\Util\Debug\Log;
use Carbon\Carbon;

class HomeController extends BuildingController
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $modelPost;

    private $_modelFeedBack;

     /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $_client;

    public function __construct(
        Request $request,
        PostsRespository $modelPost,
        FeedbackRespository $modelFeedBack
    )
    {
        $this->modelPost = $modelPost;
        $this->_modelFeedBack = $modelFeedBack;
        parent::__construct($request);
    }

    /**
     * Homepage
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request ,Users $user) {
        $data['meta_title'] = 'Trang quản trị Building Care';
        $data['user']              = $user;
        $getFirstDayOfYear = Carbon::now()->subMonth(12)->format('Y-m-d');
        $getLastDayOfYear = Carbon::now()->format('Y-m-d');
        $request->request->add(['building_id' => $this->building_active_id]);
        $request->request->add(['from_date' => $getFirstDayOfYear]);
        $request->request->add(['to_date' => $getLastDayOfYear]);
        $array_search='';
        $i=0;
        foreach ($request->all() as $key => $value) {
            if($i == 0){
                $param='?'.$key.'='.(string)$value;
            }else{
                $param='&'.$key.'='.(string)$value;
            }
            $i++;
            $array_search.=$param;
        }
        $data['posts']             = $this->modelPost->getMenuPost($this->building_active_id);

        $data['modelFeedBack']     = $this->_modelFeedBack->getDashbroad($this->building_active_id);
       
        $data['array_search'] = $array_search;
        $data['loai_danh_muc_all'] = Helper::loai_danh_muc;
        $data['trang_thai'] = Helper::trang_thai;
        return view('backend.home.index', $data);
    }

}

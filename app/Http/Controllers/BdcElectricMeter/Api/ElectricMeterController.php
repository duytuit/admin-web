<?php

namespace App\Http\Controllers\BdcElectricMeter\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Validator;
use App\Models\BdcElectricMeter\ElectricMeter;
use App\Commons\Helper;
use App\Helpers\Files;
use App\Models\Apartments\Apartments;
use Dotenv\Regex\Success;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Storage;
use App\Repositories\ElectricMeter\ElectricMeterRespository;
use Illuminate\Support\Facades\DB;

class ElectricMeterController extends Controller
{
    use ApiResponse;
    private $model;
    private $_electricMeterRespository;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(Request $request, ElectricMeterRespository $electricMeterRespository)
    {
        $this->_electricMeterRespository = $electricMeterRespository;
        //$this->middleware('jwt.auth');
    }
    public function index(Request $request)
    {
          $filter = $request->all();

          $filter['per_page'] = isset($filter['per_page']) ? $filter['per_page'] : 20;

          $buildingId = $request->building_id;

          $perPage = $request->input('per_page', 20);

          $page = $request->page ? $request->page : 1;

          $offSet = ($page * $perPage) - $perPage;

          $get_lists = $this->_electricMeterRespository->getListApi($buildingId, $request, $perPage, $offSet);

        //   $get_list_electrics = DB::table('bdc_apartments')->leftJoin('bdc_electric_meter', function($join) {
        //                                                 $join->on('bdc_apartments.id', '=', 'bdc_electric_meter.bdc_apartment_id');
        //                                             })
        //                                             ->where(function($query) use ($request){
        //                                                 if(isset($request->type) && $request->type !=null){
        //                                                     $query->where('bdc_electric_meter.type',$request->type)
        //                                                           ->orWhereNull('bdc_electric_meter.type');
        //                                                 }
        //                                                 // if(isset($request->cycle_name) && $request->cycle_name !=null){
        //                                                 //     $query->where('bdc_electric_meter.cycle_name',$request->cycle_name)
        //                                                 //           ->orWhereNull('bdc_electric_meter.cycle_name');
        //                                                 // }
        //                                                 if(isset($request->bdc_apartment_id) && $request->bdc_apartment_id !=null){
        //                                                     $query->where('bdc_electric_meter.bdc_apartment_id',$request->bdc_apartment_id);
        //                                                 }
        //                                                 if(isset($request->search) && $request->search !=null){
        //                                                     $query->where('bdc_apartments.name','like','%'.$request->search.'%');
        //                                                 }
        //                                             })
        //                                             ->where('bdc_electric_meter.cycle_name',$request->cycle_name)->orWhereNull('bdc_electric_meter.cycle_name')
        //                                             ->where('bdc_apartments.building_id',$request->building_id)
        //                                             ->whereNull('bdc_apartments.deleted_at')
        //                                             ->whereNull('bdc_electric_meter.deleted_at')
        //                                             ->select(['bdc_apartments.name','bdc_apartments.code','bdc_apartments.id as bdc_apartment_id', 'bdc_electric_meter.bdc_building_id',
        //                                                     'bdc_electric_meter.id',
        //                                                     'bdc_electric_meter.cycle_name',
        //                                                     'bdc_electric_meter.images',
        //                                                     'bdc_electric_meter.created_at',
        //                                                     'bdc_electric_meter.updated_at',
        //                                                     'bdc_electric_meter.deleted_at',
        //                                                     'bdc_electric_meter.chi_so_dau',
        //                                                     'bdc_electric_meter.chi_so_cuoi',
        //                                                     'bdc_electric_meter.type',
        //                                                     'bdc_electric_meter.user_id',
        //                                             ])
        //                                             ->orderBy('bdc_electric_meter.updated_at','desc')
        //                                             ->paginate($perPage);
          
        //   $result =  DB::table('bdc_apartments')->leftJoinSub($get_list_electrics, 'electric_meter', function($join){
        //                                         $join->on('bdc_apartments.id', '=', 'electric_meter.bdc_apartment_id');
        //                                   })
        //                                   ->where('bdc_apartments.building_id',$request->building_id)
        //                                   ->where(function($query) use ($request){
        //                                         if(isset($request->search) && $request->search !=null){
        //                                             $query->where('bdc_apartments.name','like','%'.$request->search.'%');
        //                                         }
        //                                   })
        //                                   ->whereNull('bdc_apartments.deleted_at')
        //                                   ->select(['bdc_apartments.name','bdc_apartments.code','bdc_apartments.id as bdc_apartment_id', 'electric_meter.bdc_building_id',
        //                                             'electric_meter.id',
        //                                             'electric_meter.cycle_name',
        //                                             'electric_meter.images',
        //                                             'electric_meter.created_at',
        //                                             'electric_meter.updated_at',
        //                                             'electric_meter.deleted_at',
        //                                             'electric_meter.chi_so_dau',
        //                                             'electric_meter.chi_so_cuoi',
        //                                             'electric_meter.type',
        //                                             'electric_meter.user_id',
        //                                    ])
        //                                   ->orderBy('electric_meter.updated_at','desc')
        //                                   ->paginate($perPage);
        if($get_lists){
                foreach ($get_lists as $key => $value) {
                    if(isset($request->cycle_name) && $request->cycle_name != null && isset($request->type) && $request->type != null){
                        $cycle_name = substr($request->cycle_name,0,4).sprintf("%'.02d",substr($request->cycle_name,4,strlen($request->cycle_name)));
                        $ky_truoc = date('Ym', strtotime($cycle_name."01" . "-1 months"));
                        $list_chi_so_ky_truoc = DB::table('bdc_electric_meter')->where(['bdc_apartment_id'=>$value->bdc_apartment_id,'cycle_name'=>$ky_truoc ,'type' => $request->type])->whereNull('deleted_at')->first();
                        $get_lists[$key]->chi_so_dau = isset($list_chi_so_ky_truoc) ? $list_chi_so_ky_truoc->chi_so_cuoi : 0;
                    }
                }
        }
      
        return $this->sendResponse($get_lists,'Thành công');                      
    }
    public function createOrUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bdc_building_id'  => 'required',
            'bdc_apartment_id' => 'required',
            'cycle_name'       => 'required|numeric|digits:6',
            'chi_so_cuoi'       => 'required|numeric',
          ]);
    
        if ($validator->fails()) {
            return response()->json([ 'success' => false,'message' => $validator->errors()->first()], 404);
        }
        $attach_file = $request->get('attach_files');
        if (isset($attach_file) && $attach_file!=null) {
            $files = json_decode($attach_file);

            $fileArr = null;

            foreach ($files as $_file) {
                $file = Files::uploadBase64Version2($_file, 'electric_meter');
                if(!$file) {
                    return $this->sendErrorResponse("Định dạng file không chính xác");
                }

                $fileArr = $file["hash_file"];
            }
        }
        $electric_meter = ElectricMeter::firstOrNew([
            'bdc_building_id'  => $request->bdc_building_id,
            'bdc_apartment_id' => $request->bdc_apartment_id,
            'cycle_name'       => $request->cycle_name,
            'type'       => $request->type,
        ]);
        try {
            $ky_truoc = date('Ym', strtotime((string)$request->cycle_name."01". "-1 months"));
            if(isset($ky_truoc)){
                $lay_chi_so_cuoi_ky_truoc = ElectricMeter::where(['bdc_building_id' =>  $request->bdc_building_id,
                                                                  'bdc_apartment_id' =>  $request->bdc_apartment_id,
                                                                  'cycle_name' =>  $ky_truoc,
                                                                  'type'=>  $request->type
                                                                  ])->first();
            }
            $electric_meter->images = isset($fileArr) ? $fileArr : $electric_meter->images;

            if(!isset($electric_meter->images)){
                return $this->sendErrorResponse("Trường images không được bỏ trống.");
            }

            $electric_meter->chi_so_dau = isset($request->chi_so_dau) ? (int)$request->chi_so_dau : (@$lay_chi_so_cuoi_ky_truoc->chi_so_cuoi ?? 0);
            $electric_meter->chi_so_cuoi = (int)$request->chi_so_cuoi;
            $electric_meter->user_id = auth()->user()->id;

            $electric_meter->save();

            return $this->sendResponse($electric_meter,'Thành công');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage());
        }
    }
    public function upload(Request $request)
    {
        try {

            $files = $request->file('attach_files');
            $pathFiles = [];

            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                $check = in_array(strtolower($extension),Helper::FILE_MIME_TYPES);
                if($check) {
                    $fileName = strtolower($file->getClientOriginalName());
                    $urlFile = 'electric_meter/' . $fileName;
                    $file->move(storage_path('electric_meter/'), $fileName);
                    $localFile = \File::get($urlFile);
                    Storage::disk('ftp')->put('electric_meter/'.$fileName, $localFile);
                    $tempPath = storage_path($urlFile);

                    $pathFile = env('DOMAIN_MEDIA_URL')."images/building_care/electric_meter/".$fileName;
                    array_push($pathFiles, $pathFile);
                    \File::delete($tempPath);
                } 
            }
            if($pathFiles){
                $responseData = [
                    'success' => true,
                    'data' => $pathFiles,
                ];
                return $responseData;
            }else{
                $responseData = [
                    'success' => false,
                    'data' => [],
                ];
                return $responseData;
            }
            
        }
        catch (Exception $exception) {
            $responseData = [
                'success' => false,
                'data' => [],
            ];
            return $responseData;
        }

    }
}

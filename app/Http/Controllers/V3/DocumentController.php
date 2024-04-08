<?php

namespace App\Http\Controllers\V3;

use App\Commons\Api;
use App\Commons\ApiResponse;
use App\Commons\clientApi;
use App\Commons\Helper;
use App\Helpers\dBug;
use App\Http\Controllers\BuildingController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\Building\BuildingRepository;
use Modules\Media\Repositories\DocumentRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class DocumentController extends BuildingController
{
    protected $_documentRepository;
    protected $_buildingRepository;

    public function __construct(
        DocumentRepository $documentRepository,
        BuildingRepository $buildingRepository,
        Request $request
    )
    {
        $this->_documentRepository = $documentRepository;
        $this->_buildingRepository = $buildingRepository;
        parent::__construct($request);
    }
    public function getListdocument(Request $request){
        $building_id = $request->get('building_id');
        $document_type = $request->get('document_type',1);
        $apartment_id = $request->get('apartment_id');
        $apartment_group_id = $request->get('apartment_group_id');

        $filter = [
            'building_id'=>$building_id,
            'apartment_id'=>$apartment_id,
            'apartment_group_id'=>$apartment_group_id
        ];

        $limit = isset($request->limit) ? $request->limit : 10;
        $page = isset($request->page) ? $request->page : 1;

        $documents = null;

        if ($document_type == 1) {
            $documents = $this->_documentRepository->filterByBuildingId($building_id);
        }
        else if ($document_type == 2) {
            $documents = $this->_documentRepository->filterDocument($filter);
        }
        else if ($document_type == 3 && !empty($apartment_id)) {
            $documents = $this->_documentRepository->filterDocumentByApartmentId($apartment_id);
        }

        $offSet = ($page * $limit) - $limit;
        $itemsForCurrentPage = array_slice($documents->toArray(), $offSet, $limit, true);
        $_documents = new LengthAwarePaginator($itemsForCurrentPage, count($documents), $limit, $page, []);
        $paging = [
            'total' => $_documents->total(),
            'currentPage' => $_documents->count(),
            'lastPage' => $_documents->lastPage(),
        ];

        $_documentArr = [];

        foreach ($_documents as $_document) {
            $_document = (object)$_document;
            $_document->attach_file = json_decode($_document->attach_file);
            array_push($_documentArr,$_document);
        }
        $_documentsList = $_documents->values()->toArray();
        if($_documentsList){
            $result = ['data'=>$_documentArr,'page'=>$paging];
        }else{
            $result = ['data'=>[],'page'=>$paging];
        }
        return $result;
    }
    public function index(Request $request)
    {
        $data = [
            'meta_title' => "QL Tài liệu",
        ];

        $request->request->add([
            'building_id'=>$this->building_active_id,
            'document_type'=>1,
            'page'=>1,
            'limit'=>10000
        ]); 
        $response = (object)$this->getListdocument($request);

        if(isset($response->data)){
            $document_buildings = $response->data;
            foreach ($document_buildings as $document_building) {
                    foreach ($document_building->attach_file as $file) {
                        $file_url = 'https://media.dxmb.vn' . $file->hash_file;
                        $file->hash_code = "";
                    }
            }
            $data['document_buildings'] = $document_buildings;
        }

        $request->request->add([
            'building_id'=>$this->building_active_id,
            'document_type'=>2,
            'page'=>1,
            'limit'=>10000
        ]);

        $response_apartments = (object)$this->getListdocument($request);
        // if(isset($response_apartments->data)){
        //     $document_apartments = $response_apartments->data;

        //     foreach ($document_apartments as $key => $document_apartment) {
        //         foreach ($document_apartment->attach_file as $file) {
        //             $file_url = 'https://media.dxmb.vn' . $file->hash_file;
        //             $file->hash_code = "";
        //         }
        //         $_apartmentsArr = [];
        //         foreach ($document_apartment->apartments as $key_1 => $value_1) {
        //             $new_apart = (object)$value_1;
        //             array_push($_apartmentsArr,$new_apart);
        //         }
        //         $_apartmentsGroupArr = [];
        //         foreach ($document_apartment->apartment_groups as $key_2 => $value_2) {
        //             $new_apart_group = (object)$value_2;
        //             array_push($_apartmentsGroupArr,$new_apart_group);
        //         }
        //         $document_apartments[$key]->apartments = $_apartmentsArr;
        //         $document_apartments[$key]->apartment_groups = $_apartmentsGroupArr;
        //     }
        //     $data['document_apartments'] = $document_apartments;
        // }
        
        return view('v3.documents.index', $data);

    }


    public function store(Request $request)
    {
        try {
            $title = $request->get('title');
            $description = $request->get('description');
            $document_type = (int)$request->get('document_type',1);
            $document_type_ids = $request->get('document_type_ids');
            if ($document_type == 1) {
                $document_type_ids = [$this->building_active_id];
                $document_type_ids = \GuzzleHttp\json_encode($document_type_ids);
            }
            $attach_files = $request->attach_files;
            $files = [];
            $directory = '/media/document';
            foreach ($attach_files as $file) {
                $rs_file = Helper::doUpload($file,$file->getClientOriginalName(),$directory);
                $files[]=@$rs_file->origin ? @$rs_file->origin : null;
            }
            $response = null;
            $data = [
                'title' => $title,
                'description' => $description,
                'attach_file' => json_encode($files),
                'document_type' => $document_type,
                'document_type_ids' => $document_type_ids,
                'building_id' => $this->building_active_id,
                'user_id' => auth()->id()
            ];
            $response = clientApi::POST('/api/admin/v1/document/add',$data);
            if ($response->success) {
                return ApiResponse::responseSuccess([]);
            }
            else {
                return ApiResponse::responseError([]);
            }
        } catch (\Exception $e) {
                $responseData = [
                  'data' => (string)$e->getMessage()
                ];
                return ApiResponse::responseError($responseData);
        }
       
    }

    public function get_list_apartment(Request $request)
    {

        $search = $request->get('search');

        if ($search) {
            $where[] = ['name', 'like', '%' . $search . '%'];
            $apartments = DB::table('bdc_apartments')
                ->where('building_id', $this->building_active_id)
                ->where('name', 'like', '%' . $search . '%')
                ->whereNull('deleted_at')
                ->select(["id","name"])
                ->get()
                ->toArray();
            return ApiResponse::responseSuccess([
                'data'=>$apartments
            ]);
        }
        $apartments = DB::table('bdc_apartments')
            ->where('building_id', $this->building_active_id)
            ->select(["id","name"])
            ->whereNull('deleted_at')
            ->get()
            ->toArray();
        return ApiResponse::responseSuccess([
            'data'=>$apartments
        ]);
    }

    public function get_list_apartment_group(Request $request)
    {
        $search = $request->get('search');

        if ($search) {
            $where[] = ['name', 'like', '%' . $search . '%'];
            $apartment_group = DB::table('bdc_apartment_groups')
                ->where('bdc_building_id', $this->building_active_id)
                ->where('name', 'like', '%' . $search . '%')
                ->whereNull('deleted_at')
                ->select(["id","name"])
                ->get()
                ->toArray();
            return ApiResponse::responseSuccess([
                'data'=>$apartment_group
            ]);
        }
        $apartment_group = DB::table('bdc_apartment_groups')
            ->where('bdc_building_id', $this->building_active_id)
            ->select(["id","name"])
            ->get()
            ->toArray();
        return ApiResponse::responseSuccess([
            'data'=>$apartment_group
        ]);
    }

    public function update(Request $request)
    {
        try{
                $id = $request->get('id');
                $title = $request->get('title');
                $description = $request->get('description');
        //        $attach_files = $request->get('attach_files');
                $document_type = $request->get('document_type');
                $document_type_ids = $request->get('document_type_ids');

                if ($document_type == 1) {
                    $document_type_ids = [$this->building_active_id];
                    $document_type_ids = \GuzzleHttp\json_encode($document_type_ids);
                }

                $attach_files = $request->attach_files;

                $files = [];
                $urlFiles = [];

                $path_file = storage_path().'/media/files/';

                foreach ($attach_files as $file) {
                    $name = time().'_'.$file->getClientOriginalName();
                    $file->move($path_file,$name);
                    array_push($urlFiles,'media/files/'.$name);

                    $hash_file = Helper::getExtensionFileBase64($name).base64_encode(file_get_contents($path_file.$name));

                    array_push($files,[
                        'file_name'=>$file->getClientOriginalName(),
                        'hash_file'=>$hash_file
                    ]);
                }

        ////        echo '<pre>',var_dump($files);
        //
        //        die();

                foreach ($urlFiles as $file) {
                    try {
                        unlink($file);
                    } catch (\Exception $e) {
                        $responseData = [
                            'data' => (string)$e->getMessage()
                          ];
                          return ApiResponse::responseError($responseData);
                    }
                }

                $files = \GuzzleHttp\json_encode($files);

                $response = null;

                $response = Api::PUT('/api/admin/v1/document/update',[
                    'id'=>$id,
                    'title'=>$title,
                    'description'=>$description,
                    'attach_file'=>$files,
                    'document_type' =>$document_type,
                    'document_type_ids'=>$document_type_ids,
                    'building_id'=>$this->building_active_id,
                    'user_id'=>auth()->id()
                ],true);


                if ($response->success) {
                    return ApiResponse::responseSuccess([]);
                }
                else {
                    return ApiResponse::responseError([]);
                }

            } catch (\Exception $e) {
                  $responseData = [
                    'data' => (string)$e->getMessage()
                  ];
                  return ApiResponse::responseError($responseData);
            }

    }

    public function delete(Request $request)
    {
        $ids = $request->get('ids');

        $ids = \GuzzleHttp\json_decode($ids);
        $response = null;

        foreach ($ids as $id) {
            $response = Api::DELETE("/api/admin/v1/document/delete",['id'=>$id]);
        }
        if ($response->success) {
            return ApiResponse::responseSuccess([]);
        }
        else {
            return ApiResponse::responseError([]);
        }
    }
    

    public function show(Request $request)
    {
        $id = $request->get('id');

        $document = $this->_documentRepository->filterById($id);

        if ($document) {
            $document->attach_file = json_decode($document->attach_file);
        }

        foreach ($document->attach_file as $file) {
            $file_url = 'https://media.dxmb.vn' . $file->hash_file;

//                if (!getimagesize($file_url)) {
            $file->hash_code = Helper::getExtensionFileBase64($file->file_name).base64_encode(file_get_contents($file_url));
        }

        return ApiResponse::responseSuccess([
            'data'=>$document
        ]);

    }

}

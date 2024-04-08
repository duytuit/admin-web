<?php

namespace Modules\Media\Http\Controllers;

use App\Commons\Helper;
use App\Helpers\dBug;
use App\Helpers\Files;
use App\Http\Controllers\Controller;
use  Modules\Media\Http\Requests\Add;
use  Modules\Media\Entities\Document;
use  App\Repositories\Building\BuildingRepository;
use  Modules\Media\Repositories\DocumentRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{

    protected $documentRepository;
    protected $buildingRepository;

    public function __construct(
        DocumentRepository $documentRepository,
        BuildingRepository $buildingRepository
    )
    {
        $this->documentRepository = $documentRepository;
        $this->buildingRepository = $buildingRepository;
    }

    public function index(Request $request)
    {

        $building_id = $request->get('building_id');
        $document_type = $request->get('document_type',1);
        $apartment_id = $request->get('apartment_id');
        $apartment_group_id = $request->get('apartment_group_id');

        $filter = [
            'building_id'=>$building_id,
            'apartment_id'=>$apartment_id,
            'apartment_group_id'=>$apartment_group_id
        ];

        try {
            $limit = isset($request->limit) ? $request->limit : 10;
            $page = isset($request->page) ? $request->page : 1;

            $documents = null;

            if ($document_type == 1) {
                $documents = $this->documentRepository->filterByBuildingId($building_id);
            }
            else if ($document_type == 2) {
                $documents = $this->documentRepository->filterDocument($filter);
            }
            else if ($document_type == 3 && !empty($apartment_id)) {
                $documents = $this->documentRepository->filterDocumentByApartmentId($apartment_id);
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

            return $this->sendResponsePaging($_documentArr, $paging, 200, 'Lấy thông tin thành công.');

        } catch (Exception $e) {
            Log::channel('asset')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }

    }

    /**
     * @OA\POST(
     *     path="/api/v1/document/add",
     *     tags={"Document"},
     *     summary="Add Document",
     *     description="Add Document",
     *     operationId="document_add",
     *     @OA\Parameter(
     *         description="Building Id",
     *         in="path",
     *         name="building_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Document title",
     *         in="path",
     *         name="title",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Document description",
     *         in="path",
     *         name="description",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="User_id",
     *         in="path",
     *         name="user_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Attach File",
     *         in="path",
     *         name="attach_file",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Kiểu đối tượng",
     *         in="path",
     *         name="document_type",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Danh sách ID đối tượng",
     *         in="path",
     *         name="document_type_ids",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *   @OA\Response(response=200, description="successful operation"),
     *   @OA\Response(response=406, description="not acceptable"),
     *   @OA\Response(response=500, description="internal server error")
     * )
     */
    public function add(Add $request)
    {
        try {

            $attribute = [
                'title' => $request->get('title'),
                'description' => $request->get('description'),
                'bdc_building_id' => $request->get('building_id'),
                "user_id" => $request->get('user_id')
            ];

            $attach_file = $request->get('attach_file');
            $document_type = $request->get('document_type');
            $document_type_ids = $request->get('document_type_ids');

            $attribute["attach_file"] = $attach_file;
            $document = $this->documentRepository->create($attribute);

            if ($document) {

                if ($document_type == Document::TYPE_BUILDING) {
                    $document->buildings()->sync([$attribute['bdc_building_id']]);
                }
                else if ($document_type == Document::TYPE_APARTMENT) {
                    $document_type_ids = json_decode($document_type_ids);
                    $document->apartments()->sync($document_type_ids);
                }
                else if ($document_type == Document::TYPE_APARTMENT_GROUP) {
                    $document_type_ids = json_decode($document_type_ids);
                    $document->apartmentGroups()->sync($document_type_ids);
                }

                DB::commit();
                return $this->sendResponse([], 200, 'Add Document successfully.');
            }
            else {
                DB::rollBack();
                return $this->sendResponse([], 200, 'Add Document failure.');
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::channel('document')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }

    }

    public function show(Request $request)
    {
        try {

            $document = $this->documentRepository->filterById($request->get('id'));

            if ($document) {
                $document->attach_file = json_decode($document->attach_file);
                return $this->sendResponse($document, 200, 'Get Document successfully.');
            }

            return $this->sendResponse([], 200, 'Get Document failure.');

        } catch (Exception $e) {
            Log::channel('document')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();

            $id = $request->get('id');

            $attribute = [
                'title' => $request->get('title'),
                'description' => $request->get('description'),
                'bdc_building_id' => $request->get('building_id'),
                "user_id" => $request->get('user_id')
            ];

            $document_type = $request->get('document_type');
            $document_type_ids = $request->get('document_type_ids');

            $document = $this->documentRepository->update($id, $attribute);

            if ($document) {
                $attach_file = $request->get('attach_file');

                if (isset($attach_file) && $attach_file != null) {

                    $files = json_decode($attach_file);

                    $fileArr = [];

                    foreach ($files as $_file) {
                        $file = Files::uploadBase64Version2($_file, 'documents');
                        if(!$file) {
                            return $this->sendError("Định dạng file không chính xác", [], 500);
                        }

                        $fileItem = (object) [
                            "file_name"=>$_file->file_name,
                            "hash_file"=>$file["hash_file"]
                        ];

                        array_push($fileArr,$fileItem);
                    }
                    $attributes["domain"] = env("DOMAIN_MEDIA_URL");

                    $document->attach_file = json_encode($fileArr);
                    $document->save();

                }

                if ($document_type == Document::TYPE_BUILDING) {
                    $document->buildings()->sync([$attribute['bdc_building_id']]);
                    $document->apartmentGroups()->sync([]);
                    $document->apartments()->sync([]);
                }
                else if ($document_type == Document::TYPE_APARTMENT) {
                    $document_type_ids = json_decode($document_type_ids);
                    $document->apartments()->sync($document_type_ids);
                    $document->apartmentGroups()->sync([]);
                    $document->buildings()->sync([]);
                }
                else if ($document_type == Document::TYPE_APARTMENT_GROUP) {
                    $document_type_ids = json_decode($document_type_ids);
                    $document->apartmentGroups()->sync($document_type_ids);
                    $document->apartments()->sync([]);
                    $document->buildings()->sync([]);
                }

                DB::commit();
                return $this->sendResponse([], 200, 'Update Document successfully.');

            }

            else {
                DB::rollBack();
                return $this->sendResponse([], 200, 'Update Document failure.');
            }

        }
        catch (Exception $e) {
            DB::rollBack();
            Log::channel('document')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $document = $this->documentRepository->find($request->get('id'));
            if ($document) {
                $document->apartments()->sync([]);
                $document->buildings()->sync([]);
                $document->apartmentGroups()->sync([]);
                $this->documentRepository->delete($document->id);
                return $this->sendResponse([], 200, 'Delete document successfully.');
            }
            else {
                return $this->sendResponse([], 501, 'Delete document failure.');
            }
        }
        catch (Exception $e) {
            Log::channel('document')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}

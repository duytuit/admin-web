<?php

namespace Modules\Media\Http\Controllers;

use App\Helpers\Files;
use App\Http\Controllers\Controller;
use Modules\Media\Http\Requests\Add;
use App\Repositories\Building\BuildingRepository;
use Modules\Media\Repositories\DocumentRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class DocumentAppController extends Controller
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

        $apartment_id = $request->get('apartment_id');

        try {
            $limit = isset($request->limit) ? $request->limit : 10;
            $page = isset($request->page) ? $request->page : 1;

            $documents = $this->documentRepository->filterDocumentAppByApartmentId($apartment_id);

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

        }
        catch (Exception $e) {
            Log::channel('asset')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }

    }

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

            $apartment_id = $request->get('apartment_id');

            if (isset($attach_file) && $attach_file!=null) {
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
                $attribute["attach_file"] = json_encode($fileArr);
            }else {
                $attributes["attach_file"] = "[]";
            }

            $document = $this->documentRepository->create($attribute);

            if ($document) {
                $document->apartments()->sync([$apartment_id]);
                DB::commit();
                return $this->sendResponse([], 200, 'Add Document successfully.');
            }
            else {
                DB::rollBack();
                return $this->sendResponse([], 200, 'Add Document failure.');
            }

        }catch (Exception $e) {
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

        } catch (Exception $e) {
            Log::channel('document')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

}

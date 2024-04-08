<?php

namespace App\Http\Controllers\Receipt\V2;

use App\Commons\Api;
use App\Commons\Helper;
use App\Commons\Util\Debug\Log;
use App\Exceptions\QueueRedis;
use App\Helpers\dBug;
use App\Jobs\createPromotionApartment;
use App\Models\BdcBills\Bills;
use App\Models\CronJobManager\CronJobManager;
use App\Models\Promotion\Promotion;
use App\Models\PromotionApartment\PromotionApartment;
use App\Repositories\BdcV2LogCoinDetail\LogCoinDetailRepository;
use App\Repositories\BdcV2PaymentDetail\PaymentDetailRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Repositories\Vehicles\VehiclesRespository;
use App\Services\SendTelegram;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Building\Building;
use Illuminate\Support\Facades\Auth;
use App\Models\PaymentInfo\PaymentInfo;
use App\Http\Controllers\BuildingController;
use App\Http\Requests\Receipt\ReceiptRequest;
use App\Models\Apartments\Apartments;
use App\Models\Apartments\V2\UserApartments;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\Service\ServiceRepository;
use App\Repositories\BdcReceipts\V2\ReceiptRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Models\TransactionPayment\TransactionPayment;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\Building\BuildingPlaceRepository;
use App\Models\BdcAccountingAccounts\AccountingAccounts;
use App\Models\BdcAccountingVouchers\AccountingVouches;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Models\BdcV2PaymentDetail\PaymentDetail;
use App\Models\Category;
use App\Models\Configs\Configs;
use App\Models\Customers\Customers;
use App\Models\HistoryTransactionAccounting\HistoryTransactionAccounting;
use App\Models\Vehicles\Vehicles;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcCoin\BdcCoinRepository;
use App\Repositories\BdcDebitDetail\V2\DebitDetailRepository;
use App\Repositories\BdcLockCyclename\BdcLockCyclenameRepository;
use App\Repositories\BdcReceiptLogs\ReceiptLogsRepository;
use App\Repositories\BdcReceipts\ReceiptRepository as BdcReceiptsReceiptRepository;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository as BdcV2DebitDetailDebitDetailRepository;
use App\Repositories\TransactionPayment\TransactionPaymentRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Util\Debug\Log as DebugLog;
use Exception;

class ReceiptController extends BuildingController
{

    use ApiResponse;

    const TYPE_BILL = 1;
    const TYPE_DEBIT = 2;
    const TYPE_RECETPT_PREVIOUS = 3;

    public $receiptRepo;
    public $apartmentRepo;
    public $billRepo;
    public $debitRepo;
    public $_configRepository;
    public $_modelBuildingPlace;
    public $_transactionPaymentRepository;
    private $model;
    private $model_v1;
    private $_modelUserInfo;

    public function __construct(
        Request                       $request,
        ReceiptRepository             $model,
        BdcReceiptsReceiptRepository  $model_v1,
        ApartmentsRespository         $apartmentRepo,
        BillRepository                $billRepo,
        DebitDetailRepository         $debitRepo,
        ConfigRepository              $configRepository,
        BuildingPlaceRepository       $modelBuildingPlace,
        TransactionPaymentRepository  $transactionPaymentRepository,
        PublicUsersProfileRespository $modelUserInfo
    )
    {
        parent::__construct($request);
        $this->apartmentRepo = $apartmentRepo;
        $this->billRepo = $billRepo;
        $this->debitRepo = $debitRepo;
        $this->model = $model;
        $this->model_v1 = $model_v1;
        $this->_configRepository = $configRepository;
        $this->_modelBuildingPlace = $modelBuildingPlace;
        $this->_transactionPaymentRepository = $transactionPaymentRepository;
        $this->_modelUserInfo = $modelUserInfo;
        // $this->middleware('auth', ['except' => []]);
        // //$this->middleware('route_permision');
        Carbon::setLocale('vi');
    }

    public function index(Request $request)
    {
        try {
            $data['meta_title'] = 'Quản lý phiếu thu';
            $data['per_page'] = Cookie::get('per_page', 10);
            $data['apartments'] = $this->apartmentRepo->getApartmentOfBuilding($this->building_active_id);
            $data['filter'] = $request->all();
            if (isset($data['filter']['bdc_apartment_id'])) {
                $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
            }
            if (isset($data['filter']['ip_place_id'])) {
                $data['get_place_building'] = $this->_modelBuildingPlace->findById($data['filter']['ip_place_id']);
            }

            $data['receipts'] = $this->model->filterReceipt($request->all(), $this->building_active_id)->withTrashed()->paginate($data['per_page']);
            $data['sum_cost'] = $this->model->countFilterReceipt($request->all(), $this->building_active_id);
            $user_create_receipt = $this->model->distinct_user_by_building($this->building_active_id);
            $data['user_info'] = $user_create_receipt ? $this->_modelUserInfo->getInfoByPubuserByBuildingId($user_create_receipt->toArray()) : null;

            $receipt_code_type = $request->receipt_code_type ? array_filter($request->receipt_code_type) : null;

            $type_receipt = Helper::type_receipt;

            $data['filter']['receipt_code_type'] = $receipt_code_type;
            $data['type_receipt'] = $type_receipt;
            $data['get_type_receipt'] = Category::where(['bdc_building_id' => $this->building_active_id, 'type' => 'receipt'])->get();

            return view('receipt.v2.index', $data);
        } catch (Exception $e) {
            // dd($e->getTraceAsString());
        }

    }

    public function kyquy(Request $request)
    {
        $data['meta_title'] = 'Quản lý phiếu thu';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['apartments'] = $this->apartmentRepo->getApartmentOfBuilding($this->building_active_id);
        $data['configs'] = $this->_configRepository->findByMultiKeyByReceiptDeposit($this->building_active_id);
        $data['filter'] = $request->all();
        if (isset($data['filter']['bdc_apartment_id'])) {
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
        }
        if (isset($data['filter']['ip_place_id'])) {
            $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
        }

        $data['receipts'] = $this->model->filterReceiptDeposit($request->all(), $this->building_active_id)->withTrashed()->paginate($data['per_page']);
        $data['sumPrice'] = $data['receipts']->sum('cost');

        $fromDate = isset($request['created_at_from_date']) ? $request['created_at_from_date'] : null;
        $toDate = isset($request['created_at_to_date']) ? $request['created_at_to_date'] : null;
        $daukyPhieuThuTruoc = $fromDate != null ? $this->model->dauKy($this->building_active_id, ReceiptRepository::PHIEUTHU_KYQUY, $fromDate)->sum('cost') : 0;
        $daukyPhieuChiKhac = $fromDate != null ? $this->model->dauKy($this->building_active_id, ReceiptRepository::PHIEUHOAN_KYQUY, $fromDate)->sum('cost') : 0;
        $data['totalPhieuThuTruoc'] = $totalPhieuThuTruoc = $this->model->totalCost($this->building_active_id, ReceiptRepository::PHIEUTHU_KYQUY, $fromDate, $toDate)->sum('cost');
        $data['totalPhieuChiKhac'] = $totalPhieuChiKhac = $this->model->totalCost($this->building_active_id, ReceiptRepository::PHIEUHOAN_KYQUY, $fromDate, $toDate)->sum('cost');
        $data['totalDauKy'] = $totalDauKy = $daukyPhieuThuTruoc - $daukyPhieuChiKhac;
        $data['totalCuoiKy'] = $totalDauKy + $totalPhieuThuTruoc - $totalPhieuChiKhac;
        $data['sumPriceTotal'] = $this->model->getAllReceiptBuildingKyQuy($this->building_active_id)->get()->sum('cost');
        return view('receipt.v2.kyquy', $data);
    }

    public function index_v1(Request $request)
    {
        $data['meta_title'] = 'Quản lý phiếu thu';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['apartments'] = $this->apartmentRepo->getApartmentOfBuilding($this->building_active_id);
        $data['filter'] = $request->all();
        if (isset($data['filter']['bdc_apartment_id'])) {
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
        }
        if (isset($data['filter']['ip_place_id'])) {
            $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
        }
        $data['receipts'] = $this->model_v1->filterReceipt($request->all(), $this->building_active_id)->withTrashed()->paginate($data['per_page']);
        $data['sumPrice'] = $data['receipts']->sum('cost');
        $data['sumPriceTotal'] = $this->model_v1->getAllReceiptBuilding(null, $this->building_active_id)->sum('cost');
        return view('receipt.index_v1', $data);
    }

    public function ajaxGetSelectTypeReceipt(Request $request)
    {
        $type_receipt = Helper::type_receipt;
        $responseData = [
            'success' => true,
            'message' => 'Lấy dữ liệu thành công!',
            'data' => $type_receipt
        ];

        return response()->json($responseData);
    }

    public function filterByApartment(
        Request                         $request,
        Vehicles                        $vehicle,
        CustomersRespository            $customer,
        ServiceRepository               $serviceRepository,
        ApartmentServicePriceRepository $serviceApartmentRepository,
                                        $apartment_id)
    {
        $building_id = $this->building_active_id;
        $_customer = UserApartments::getPurchaser($apartment_id, 0);
        if (!$_customer) {
            return $this->responseError('Không tìm thấy chủ căn hộ.', 404);
        }

        $customerInfo = $_customer->user_info_first;
        if (!$customerInfo) {
            return $this->responseError('Không tìm thấy thông tin chủ hộ.', 404);
        }
        $customer_name = $customerInfo->full_name;

        $view = view("receipt.v2._bill_services_phieu_dieu_chinh", [
            'vehicle' => $vehicle,
            'serviceRepository' => $serviceRepository,
            'serviceApartmentRepository' => $serviceApartmentRepository,
            'buildingId' => $this->building_active_id,
        ])->render();
        return $this->responseSuccess([
            'html' => $view,
            'customer_name' => $customer_name,
            'user_info_id' => $customerInfo->id,
        ]);
    }

    public function save_huyphieuthu(Request $request, ReceiptRepository $receiptRepository)
    {
        $receiptId = $request->post("receiptId", false);
        $note = $request->post("note", "");

        if (!$receiptId) {
            return $this->sendResponse([
                'status' => 1,
                'mess' => "Tham số không hợp lệ || " . $receiptId,
            ], '');
        }

        $data = $receiptRepository->findReceiptById($receiptId);

        if (Auth::user()->isadmin == 1) { // làm tool cho supper xóa lại phiếu thu
            $data = $receiptRepository->findReceiptByIdWithTrashed($receiptId);
        }

        if (!$data) {

            return $this->sendResponse([
                'status' => 2,
                'mess' => "không có dữ liệu || " . $receiptId,
            ], '');
        }
        $cycle = Carbon::parse($data->create_date)->format('Ym');
        $action = Helper::getAction();
        if ($action && Auth::user()->isadmin != 1) {
            $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id, $cycle, $action);
            if ($check_lock_cycle) {
                return $this->sendResponse([
                    'status' => 2,
                    'mess' => "Kỳ $cycle đã được khóa."
                ], '');
            }
        }
        try {
            $_add_queue_stat_payment = null;
            DB::beginTransaction();
            $checkStatus = false;
            if ($data->type == 'phieu_dieu_chinh' || $data->type == 'phieu_chi') {
                $log = json_decode($data->logs);
                $_customer = CustomersRespository::findApartmentIdV2($data->bdc_apartment_id, 0);
                if ($log) {

                    foreach ($log as $key => $value) {
                        $checkStatus = true;

                        if (str_contains($value->service_apartment_id, 'tien_thua_')) { // nếu là tiền thừa
                            $service_apartment_id = explode('tien_thua_', $value->service_apartment_id)[1];
                            BdcCoinRepository::addCoin($data->bdc_building_id, $data->bdc_apartment_id, $service_apartment_id, Carbon::parse($data->create_date)->format('Ym'), $_customer->user_info_id, $value->paid_payment, Auth::user()->id, 5, $data->id, $data->id);

                            $_add_queue_stat_payment[] = [
                                "apartmentId" => $data->bdc_apartment_id,
                                "service_price_id" => $service_apartment_id,
                                "cycle_name" => Carbon::parse($data->create_date)->format('Ym'),
                            ];
                        }
                        if (str_contains($value->service_apartment_id, 'tien_dich_vu_')) { // nếu là tiền thừa

                            $debitId = explode('tien_dich_vu_', $value->service_apartment_id)[1];

                            $debitDetail = DebitDetail::find($debitId);

                            if (!$debitDetail) {
                                DB::rollBack();
                                return $this->sendResponse([
                                    'status' => 2,
                                    'mess' => "không tìm thấy công nợ $debitId"
                                ], '');
                            }

                            $check_coin = BdcCoinRepository::getCoin($data->bdc_apartment_id, $debitDetail->bdc_apartment_service_price_id);
                            if ($check_coin && $check_coin->coin < $value->paid_payment) {
                                $service_apart = ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($debitDetail->bdc_apartment_service_price_id);
                                $vehicle = @$service_apart->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($service_apart->bdc_vehicle_id) : null;
                                DB::rollBack();
                                return $this->sendResponse([
                                    'status' => 2,
                                    'mess' => $service_apart->name . '-' . @$vehicle->number . " không đủ tiền thừa để hủy."
                                ], '');
                            }
                            // giảm coin cho dịch vụ
                            $rsSub = BdcCoinRepository::subCoin($data->bdc_building_id, $data->bdc_apartment_id, $debitDetail->bdc_apartment_service_price_id, Carbon::parse($data->create_date)->format('Ym'), $_customer->user_info_id, $value->paid_payment, Auth::user()->id, 5, $data->id, $data->id);
                            if ($rsSub["status"] !== 0) {
                                DB::rollBack();
                                return $this->sendResponse([
                                    'status' => 5,
                                    'mess' => "Hủy thất bại! ví không đủ tiền",
                                ], '');
                            }
                            $_add_queue_stat_payment[] = [
                                "apartmentId" => $data->bdc_apartment_id,
                                "service_price_id" => $debitDetail->bdc_apartment_service_price_id,
                                "cycle_name" => Carbon::parse($data->create_date)->format('Ym'),
                            ];
                        }

                    }
                    Log::info('log_phieu_dieu_chinh', json_encode($_add_queue_stat_payment));
                }

            }

            // $listLogCoin = LogCoinDetailRepository::getDataByFromtypeFromId($data->bdc_apartment_id, 0, 1, $receiptId); // tìm xem có tiền thừa không chỉ định
            // if ($listLogCoin) foreach ($listLogCoin as $logCoin) {
            //     $rsSub = BdcCoinRepository::subCoin($logCoin->bdc_building_id, $logCoin->bdc_apartment_id, $logCoin->bdc_apartment_service_price_id, $logCoin->cycle_name, $logCoin->user_id, $logCoin->coin, Auth::user()->id, 5, $logCoin->from_id, $logCoin->note);
            //     if ($rsSub["status"] !== 0) {
            //         DB::rollBack();
            //         return $this->sendResponse([
            //             'status' => 3,
            //             'mess' => "Hủy thất bại! ví không đủ tiền1!",
            //         ], '');
            //     }
            //     $checkStatus = true;
            //     $_add_queue_stat_payment[]=[
            //         "apartmentId" => $logCoin->bdc_apartment_id,
            //         "service_price_id" => 0,
            //         "cycle_name" => $logCoin->cycle_name,
            //     ];
            // }

            $listPayment = PaymentDetailRepository::getDataByReceiptId($receiptId);
            if ($listPayment) foreach ($listPayment as $item) {
                $item = (object)$item->toArray();
                if ($item->bdc_log_coin_id != 0) { // có liên quan đến coin
                    $logCoin = LogCoinDetailRepository::getDataById($item->bdc_log_coin_id);
                    if ($logCoin) {
                        if ($logCoin->from_type == 4) { // hạch toán từ ví // cộng lại tiền vào ví
                            BdcCoinRepository::addCoin($logCoin->bdc_building_id, $logCoin->bdc_apartment_id, $logCoin->bdc_apartment_service_price_id, $logCoin->cycle_name, $logCoin->user_id, $logCoin->coin, Auth::user()->id, 5, $logCoin->note);
                        }
                        // elseif ($logCoin->from_type == 1) { // nộp thừa tiền
                        //     $rsSub = BdcCoinRepository::subCoin($logCoin->bdc_building_id, $logCoin->bdc_apartment_id, $logCoin->bdc_apartment_service_price_id, $logCoin->cycle_name, $logCoin->user_id, $logCoin->coin, Auth::user()->id, 5, $logCoin->from_id, $logCoin->note);
                        //     if ($rsSub["status"] !== 0) {
                        //         DB::rollBack();
                        //         return $this->sendResponse([
                        //             'status' => 5,
                        //             'mess' => "Hủy thất bại! ví không đủ tiền",
                        //         ], '');
                        //     }
                        // }
                    }
                }
                // else {
                //     $dataAddCoin = LogCoinDetailRepository::getDataByFromtypeFromId($data->bdc_apartment_id, $item->bdc_apartment_service_price_id, 1, $receiptId);
                //     if ($dataAddCoin) {   // có tiền thừa thì trừ
                //         foreach ($dataAddCoin as $logCoin) {
                //             $rsSub = BdcCoinRepository::subCoin($logCoin->bdc_building_id, $logCoin->bdc_apartment_id, $logCoin->bdc_apartment_service_price_id, $logCoin->cycle_name, $logCoin->user_id, $logCoin->coin, Auth::user()->id, 5, $logCoin->from_id, $logCoin->note);
                //             if ($rsSub["status"] !== 0) {
                //                 DB::rollBack();
                //                 return $this->sendResponse([
                //                     'status' => 4,
                //                     'mess' => "Hủy thất bại! ví không đủ tiền",
                //                 ], '');
                //             }
                //         }
                //     }
                // }
                PaymentDetailRepository::createPayment($item->bdc_building_id, $item->bdc_apartment_id, $item->bdc_apartment_service_price_id, $item->cycle_name, $item->bdc_debit_detail_id, -($item->paid), Carbon::now(), $item->bdc_receipt_id, $item->bdc_log_coin_id);
                $Debit = BdcV2DebitDetailDebitDetailRepository::getInfoDebitById($item->bdc_debit_detail_id);
                if ($Debit && isset($Debit->cycle_name) && $Debit->cycle_name != $item->cycle_name) { // cập nhật lại cycle_name debit

                    $_add_queue_stat_payment[] = [
                        "apartmentId" => $item->bdc_apartment_id,
                        "service_price_id" => $item->bdc_apartment_service_price_id,
                        "cycle_name" => @$Debit->cycle_name,
                    ];
                }
                $checkStatus = true;
                // cập nhật lại cycle_name
                $_add_queue_stat_payment[] = [
                    "apartmentId" => $item->bdc_apartment_id,
                    "service_price_id" => $item->bdc_apartment_service_price_id,
                    "cycle_name" => $item->cycle_name,
                ];
            }

            // nếu ko tìm thấy tiền thừa ko chỉ định và payment nào thì tìm tiền thừa có chỉ định
            $listPayment = LogCoinDetailRepository::getDataByFromId($data->bdc_apartment_id, 1, $receiptId); // tìm xem có tiền thừa chỉ định không

            if ($listPayment) foreach ($listPayment as $logCoin) {
                // dBug::trackingPhpErrorV2($logCoin);
                $rsSub = BdcCoinRepository::subCoin($logCoin->bdc_building_id, $logCoin->bdc_apartment_id, $logCoin->bdc_apartment_service_price_id, $logCoin->cycle_name, $logCoin->user_id, $logCoin->coin, Auth::user()->id, 5, $logCoin->from_id, $logCoin->note);

                if ($rsSub["status"] !== 0) {
                    DB::rollBack();
                    return $this->sendResponse([
                        'status' => 3,
                        'mess' => "Hủy thất bại! ví không đủ tiền2!",
                    ], '');
                }
                $checkStatus = true;
                $_add_queue_stat_payment[] = [
                    "apartmentId" => $logCoin->bdc_apartment_id,
                    "service_price_id" => $logCoin->bdc_apartment_service_price_id,
                    "cycle_name" => $logCoin->cycle_name,
                ];
            }


            if (!$checkStatus && $data->type != 'phieu_thu_truoc' && $data->type != 'phieu_chi_khac') { // nếu ko tìm thấy giao dịch nào thì là lỗi
                DB::rollBack();
                return $this->sendResponse([
                    'status' => 6,
                    'mess' => "Hủy thất bại! không tìm thấy giao dịch nào!" . $data,
                ], '');
            }

            $receiptRepository->update(["description" => $note, 'updated_by' => auth()->user()->id], $receiptId);
            $receiptRepository->delete(["id" => $receiptId]); // update xóa phiếu thu
            Cache::store('redis')->forget(env('REDIS_PREFIX') . 'get_detail_receiptById_' . $receiptId);
            if ($data->payment_transaction) {
                $payment_transaction = $data->payment_transaction;
                $history_transaction = HistoryTransactionAccounting::where(['ngan_hang' => $payment_transaction, 'status' => 'da_hach_toan'])->first();
                if ($history_transaction && $history_transaction->ten_khach_hang == null) {
                    $history_transaction->status = 'cho_hach_toan';
                    $history_transaction->bdc_apartment_id = null;
                    $history_transaction->detail = null;
                    $history_transaction->save();
                } else {   // xác định được căn hộ
                    $history_transaction->status = 'cho_hach_toan';
                    $history_transaction->save();
                }
            }
            DB::commit();
            if ($_add_queue_stat_payment && count($_add_queue_stat_payment) > 0) {
                foreach ($_add_queue_stat_payment as $key => $value) {
                    QueueRedis::setItemForQueue('add_queue_stat_payment_', $value);
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendResponse([
                'status' => 7,
                'mess' => "Hủy thất bại! có lỗi : " . $e->getMessage(),
            ], '');
        }

        return $this->sendResponse([
            'status' => 0,
            'mess' => "Hủy thành công!",
        ], '');
    }

    public function show_huyphieuthu(Request $request, ReceiptRepository $receiptRepository)
    {
        $receiptId = $request->get("receiptId");
        $data = $receiptRepository->findReceiptById($receiptId);

        if (Auth::user()->isadmin == 1) { // làm tool cho supper xóa lại phiếu thu
            $data = $receiptRepository->findReceiptByIdWithTrashed($receiptId);
        }

        if (!$data) {
            return $this->sendResponse([
                'html' => "không có dữ liệu || " . $receiptId,
            ], 'Thành công.');
        }
        $apartment = ApartmentsRespository::getInfoApartmentsById($data->bdc_apartment_id);

        $dataDetail = [];
        $error = false;

        $listPayment = PaymentDetailRepository::getDataByReceiptId($receiptId);
        if ($listPayment) foreach ($listPayment as $item) {
            $item = (object)$item;
            $infoService = ApartmentServicePriceRepository::getInfoServiceApartmentById($item->bdc_apartment_service_price_id);
            $Vehicles = false;
            if ($infoService->bdc_vehicle_id) {
                $Vehicles = Vehicles::get_detail_vehicle_by_id($infoService->bdc_vehicle_id);
            }
            $Debit = BdcV2DebitDetailDebitDetailRepository::getInfoDebitById($item->bdc_debit_detail_id);
            $countCoin = LogCoinDetailRepository::getCountByFromtypeFromId($data->bdc_apartment_id, $item->bdc_apartment_service_price_id, 1, $receiptId);

            $total_so_du = BdcCoinRepository::getCoin($data->bdc_apartment_id, $item->bdc_apartment_service_price_id);

            if ($total_so_du && $total_so_du->coin && $total_so_du->coin < $countCoin) {
                $error = true;
                Log::info('check_huy_phieu', '1_' . json_encode($total_so_du) . '|' . $countCoin);

            }
            $dataDetail[] = [
                "dichvu" => $infoService->name,
                "phuongtien" => $Vehicles ? $Vehicles->number : "",
                "phatsinh" => @$Debit->sumery,
                "thoigian" => @$item->paid_date,
                "thanhtoan" => @$item->paid + $countCoin,
            ];

            if ($item->bdc_log_coin_id) {
                $logCoin = LogCoinDetailRepository::getDataById($item->bdc_log_coin_id);
                if ($logCoin && $logCoin->from_type == 4) { // hạch toán từ ví
                    $data->cost = 0;
                    $infoService = ApartmentServicePriceRepository::getInfoServiceApartmentById($logCoin->bdc_apartment_service_price_id);
                    $Vehicles = false;
                    if (@$infoService->bdc_vehicle_id > 0) {
                        $Vehicles = Vehicles::get_detail_vehicle_by_id($infoService->bdc_vehicle_id);
                    }

                    $dataDetail[] = [
                        "dichvu" => $logCoin->bdc_apartment_service_price_id != 0 ? @$infoService->name : 'Tiền thừa không chỉ định',
                        "phuongtien" => $Vehicles ? $Vehicles->number : "",
                        "phatsinh" => 0,
                        "thoigian" => @$item->paid_date,
                        "thanhtoan" => 0 - (@$item->paid + $countCoin),
                    ];
                }
            }

        }
        $listPayment = LogCoinDetailRepository::getDataByFromtypeFromId($data->bdc_apartment_id, 0, 1, $receiptId);

        if ($listPayment) foreach ($listPayment as $item) {
            $total_so_du = BdcCoinRepository::getCoin($data->bdc_apartment_id, 0);
            if ($total_so_du && $total_so_du->coin && $total_so_du->coin < $item->coin) {
                $error = true;
                Log::info('check_huy_phieu', '2_' . json_encode($total_so_du) . '|' . $item->coin);

            }
            $dataDetail[] = [
                "dichvu" => "Tiền thừa",
                "phuongtien" => "",
                "phatsinh" => "",
                "thoigian" => "",
                "thanhtoan" => @$item->coin,
            ];
        }

        if (!$dataDetail) {
            $listPayment = LogCoinDetailRepository::getDataByFromId($data->bdc_apartment_id, 1, $receiptId);

            if ($listPayment) foreach ($listPayment as $item) {
                $total_so_du = BdcCoinRepository::getCoin($data->bdc_apartment_id, $item->bdc_apartment_service_price_id);
                if ($total_so_du && $total_so_du->coin && $total_so_du->coin < $item->coin) {
                    $error = true;
                    Log::info('check_huy_phieu', '3_' . json_encode($total_so_du) . '|' . $item->coin . $data->bdc_apartment_id . '||' . $receiptId . '|' . json_encode($listPayment));

                }
                $dataDetail[] = [
                    "dichvu" => "Tiền thừa",
                    "phuongtien" => "",
                    "phatsinh" => "",
                    "thoigian" => "",
                    "thanhtoan" => @$item->coin,
                ];
            }
        }

        if (!$dataDetail && $data->config_type_payment != null && Auth::user()->isadmin == 0 && $data->type != 'phieu_thu_truoc' && $data->type != 'phieu_chi_khac') {
            return $this->sendResponse([
                'html' => "Không thể hủy phiếu thu này!",
            ], 'Thành công.');
        }

        if ($error && $data->type != 'phieu_thu_truoc' && $data->type != 'phieu_chi_khac') {
            return $this->sendResponse([
                'html' => "Không thể hủy phiếu thu này! Vì tiền thừa của khách hàng không đủ để hủy!",
            ], 'Thành công.');
        }

        if ($apartment) {
            $view = view("receipt.v2.modal._chi_tiet_huyphieuthu", [
                'apartment' => $apartment,
                'data' => $data,
                'dataDetail' => $dataDetail,
                'receiptId' => $receiptId,
            ])->render();
            return $this->sendResponse([
                'html' => $view, 'status' => $error === false,
            ], $error === false ? 'Thành công.' : "");
        }
        return $this->sendErrorResponse('Thất bại.');
    }

    public function edit($id)
    {
        $data['meta_title'] = 'Sửa phiếu thu';
        $_receipt = $this->model->findReceiptById($id);
        if (!$_receipt) {
            return redirect()->route('admin.v2.receipt.index')->with('warning', "Không tìm thấy dữ liệu.");
        }
        $data['receipt'] = $_receipt;
        $data['typeReceipt'] = Category::where(['type' => 'receipt', 'bdc_building_id' => $this->building_active_id, 'status' => 1])->get();
        return view('receipt.v2.edit', $data);
    }

    public function filterByBill(
        Request                         $request,
        BillRepository                  $billRepository,
        CustomersRespository            $customer,
        ReceiptRepository               $receiptRepository,
        ServiceRepository               $serviceRepository,
        ApartmentServicePriceRepository $serviceApartmentRepository,
                                        $apartment_id,
                                        $type)
    {
        $input = $request->all();
        $provisionalReceipt = $input['provisional_receipt'];
        $building_id = $this->building_active_id;
        $bills = $billRepository->findBuildingApartmentId($building_id, $apartment_id);
        $receipts = $receiptRepository->findBuildingApartmentId($building_id, $apartment_id);
        $provisionalReceipts = $this->model->filterApartmentId($apartment_id);
        $_customer = UserApartments::getPurchaser($apartment_id, 0);
        if (!$_customer) {
            return $this->responseError('Không tìm thấy chủ căn hộ.', 404);
        }

        $customerInfo = $_customer->user_info_first;
        if (!$customerInfo) {
            return $this->responseError('Không tìm thấy thông tin chủ hộ.', 404);
        }
        $customer_name = $customerInfo->full_name;
        $apartment = $_customer->bdcApartment;
        $debitDetails = BdcV2DebitDetailDebitDetailRepository::getDebitByApartmentAndBuilding($building_id, $apartment_id)->toArray();
        $transactionPaymentReceipt = $this->_transactionPaymentRepository->transactionPaymentReceiptAmount($this->building_active_id, $apartment_id);
        $total_so_du = BdcCoinRepository::getCoinTotal($apartment_id);
        $detail_service_so_du = BdcCoinRepository::getCoinByApartment($apartment_id);


        if (count($detail_service_so_du) > 0) {
            foreach ($detail_service_so_du as $key => $value) {

                $detail_service_so_du[$key]->dich_vu = $value->bdc_apartment_service_price_id == 0 ? "Chưa chỉ định" : @$value->apartmentServicePrice->vehicle->number ?? @$value->apartmentServicePrice->service->name;

            }
        }

        if ($provisionalReceipt == 0) {
            $view = view("receipt.v2._bill_services_v2", [
                'bills' => $bills,
                'billRepository' => $billRepository,
                'receipts' => $receipts,
                'debitDetails' => $debitDetails,
                'serviceRepository' => $serviceRepository,
                'serviceApartmentRepository' => $serviceApartmentRepository,
                'buildingId' => $this->building_active_id,
                'detail_service_so_du' => $detail_service_so_du,
            ])->render();
            return $this->responseSuccess([
                'html' => $view,
                'customer_name' => $customer_name,
                'user_info_id' => $customerInfo->id,
                'customer_address' => @$apartment->name,
                'ma_khach_hang' => @$apartment->code_customer,
                'ten_khach_hang' => @$apartment->name_customer,
                'vi_can_ho' => isset($transactionPaymentReceipt[0]) ? (int)$transactionPaymentReceipt[0]->so_du : 0,
                'total_so_du' => $total_so_du,
                'detail_service_so_du' => $detail_service_so_du,
                'provisionalReceipts' => $provisionalReceipts,
                'nghiep_vu' => (int)$type
            ]);
        } else if ($provisionalReceipt > 0) {
            $receipt = $receiptRepository->findByIdIsNotComplete($provisionalReceipt);
            if (!$receipt) {
                return $this->responseError('Mã hóa đơn không hợp lệ.', 404);
            }
            $customer_name = $receipt->customer_name;
            $customer_address = $receipt->customer_address;
            $paid_money = $receipt->cost;
            $view = view("receipt.v2._bill_services_v2", [
                'bills' => $bills,
                'billRepository' => $billRepository,
                'receipts' => $receipts,
                'debitDetails' => $debitDetails,
                'serviceRepository' => $serviceRepository,
                'serviceApartmentRepository' => $serviceApartmentRepository,
                'buildingId' => $this->building_active_id,
            ])->render();
            return $this->responseSuccess([
                'html' => $view,
                'customer_name' => $customer_name,
                'customer_address' => @$apartment->name,
                'user_info_id' => $customerInfo->id,
                'ma_khach_hang' => @$apartment->code_customer,
                'ten_khach_hang' => @$apartment->name_customer,
                'vi_can_ho' => isset($transactionPaymentReceipt[0]) ? (int)$transactionPaymentReceipt[0]->so_du : 0,
                'total_so_du' => $total_so_du,
                'detail_service_so_du' => $detail_service_so_du,
                'paid_money' => $paid_money,
                'provisionalReceipts' => $provisionalReceipts,
                'nghiep_vu' => (int)$type
            ]);
        } else {
            return $this->responseError([
                'html' => ''
            ], 500);
        }
    }

    public function action(Request $request, ReceiptRepository $receiptRepository)
    {
        $method = $request->input('method', '');
        if ($method == 'per_page') {
            $this->per_page($request);
            return back();
        } else if ($method == 'del_receipt') {
            if (count($request->ids)) {
                $_add_queue_stat_payment = null;
                foreach ($request->ids as $key => $id) {
                    $data = $receiptRepository->findReceiptById($id);
                    if (Auth::user()->isadmin == 1) { // làm tool cho supper xóa lại phiếu thu
                        $data = $receiptRepository->findReceiptByIdWithTrashed($id);
                    }
                    if (!$data) {
                        continue;
                    }
                    $cycle = Carbon::parse($data->create_date)->format('Ym');
                    $action = Helper::getAction();
                    if ($action && Auth::user()->isadmin != 1) {
                        $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id, $cycle, $action);
                        if ($check_lock_cycle) {
                            continue;
                        }
                    }
                    try {
                        DB::beginTransaction();
                        $checkStatus = false;
                        if ($data->type == 'phieu_dieu_chinh' || $data->type == 'phieu_chi') {
                            $log = json_decode($data->logs);
                            $_customer = CustomersRespository::findApartmentIdV2($data->bdc_apartment_id, 0);
                            if ($log) {
                                foreach ($log as $key => $value) {
                                    $checkStatus = true;
                                    if (str_contains($value->service_apartment_id, 'tien_thua_')) { // nếu là tiền thừa
                                        $service_apartment_id = explode('tien_thua_', $value->service_apartment_id)[1];
                                        BdcCoinRepository::addCoin($data->bdc_building_id, $data->bdc_apartment_id, $service_apartment_id, Carbon::parse($data->create_date)->format('Ym'), $_customer->user_info_id, $value->paid_payment, Auth::user()->id, 5, $data->id, $data->id);
                                        $_add_queue_stat_payment[] = [
                                            "apartmentId" => $data->bdc_apartment_id,
                                            "service_price_id" => $service_apartment_id,
                                            "cycle_name" => Carbon::parse($data->create_date)->format('Ym'),
                                        ];
                                    }
                                    if (str_contains($value->service_apartment_id, 'tien_dich_vu_')) { // nếu là tiền thừa
                                        $debitId = explode('tien_dich_vu_', $value->service_apartment_id)[1];
                                        $debitDetail = DebitDetail::find($debitId);
                                        if (!$debitDetail) {
                                            DB::rollBack();
                                            continue;
                                        }

                                        $check_coin = BdcCoinRepository::getCoin($data->bdc_apartment_id, $debitDetail->bdc_apartment_service_price_id);
                                        if ($check_coin && $check_coin->coin < $value->paid_payment) {
                                            $service_apart = ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($debitDetail->bdc_apartment_service_price_id);
                                            $vehicle = @$service_apart->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($service_apart->bdc_vehicle_id) : null;
                                            DB::rollBack();
                                            continue;
                                        }
                                        // giảm coin cho dịch vụ
                                        $rsSub = BdcCoinRepository::subCoin($data->bdc_building_id, $data->bdc_apartment_id, $debitDetail->bdc_apartment_service_price_id, Carbon::parse($data->create_date)->format('Ym'), $_customer->user_info_id, $value->paid_payment, Auth::user()->id, 5, $data->id, $data->id);
                                        if ($rsSub["status"] !== 0) {
                                            DB::rollBack();
                                            continue;
                                        }
                                        $_add_queue_stat_payment[] = [
                                            "apartmentId" => $data->bdc_apartment_id,
                                            "service_price_id" => $debitDetail->bdc_apartment_service_price_id,
                                            "cycle_name" => Carbon::parse($data->create_date)->format('Ym'),
                                        ];
                                    }

                                }
                                Log::info('log_phieu_dieu_chinh', json_encode($_add_queue_stat_payment));
                            }

                        }

                        $listPayment = PaymentDetailRepository::getDataByReceiptId($id);
                        if ($listPayment) foreach ($listPayment as $item) {
                            $item = (object)$item->toArray();
                            if ($item->bdc_log_coin_id != 0) { // có liên quan đến coin
                                $logCoin = LogCoinDetailRepository::getDataById($item->bdc_log_coin_id);
                                if ($logCoin) {
                                    if ($logCoin->from_type == 4) { // hạch toán từ ví // cộng lại tiền vào ví
                                        BdcCoinRepository::addCoin($logCoin->bdc_building_id, $logCoin->bdc_apartment_id, $logCoin->bdc_apartment_service_price_id, $logCoin->cycle_name, $logCoin->user_id, $logCoin->coin, Auth::user()->id, 5, $logCoin->note);
                                    }
                                }
                            }
                            PaymentDetailRepository::createPayment($item->bdc_building_id, $item->bdc_apartment_id, $item->bdc_apartment_service_price_id, $item->cycle_name, $item->bdc_debit_detail_id, -($item->paid), Carbon::now(), $item->bdc_receipt_id, $item->bdc_log_coin_id);
                            $Debit = BdcV2DebitDetailDebitDetailRepository::getInfoDebitById($item->bdc_debit_detail_id);
                            if ($Debit && isset($Debit->cycle_name) && $Debit->cycle_name != $item->cycle_name) { // cập nhật lại cycle_name debit

                                $_add_queue_stat_payment[] = [
                                    "apartmentId" => $item->bdc_apartment_id,
                                    "service_price_id" => $item->bdc_apartment_service_price_id,
                                    "cycle_name" => @$Debit->cycle_name,
                                ];
                            }
                            $checkStatus = true;
                            // cập nhật lại cycle_name
                            $_add_queue_stat_payment[] = [
                                "apartmentId" => $item->bdc_apartment_id,
                                "service_price_id" => $item->bdc_apartment_service_price_id,
                                "cycle_name" => $item->cycle_name,
                            ];
                        }

                        // nếu ko tìm thấy tiền thừa ko chỉ định và payment nào thì tìm tiền thừa có chỉ định
                        $listPayment = LogCoinDetailRepository::getDataByFromId($data->bdc_apartment_id, 1, $id); // tìm xem có tiền thừa chỉ định không

                        if ($listPayment) foreach ($listPayment as $logCoin) {
                            // dBug::trackingPhpErrorV2($logCoin);
                            $rsSub = BdcCoinRepository::subCoin($logCoin->bdc_building_id, $logCoin->bdc_apartment_id, $logCoin->bdc_apartment_service_price_id, $logCoin->cycle_name, $logCoin->user_id, $logCoin->coin, Auth::user()->id, 5, $logCoin->from_id, $logCoin->note);

                            if ($rsSub["status"] !== 0) {
                                DB::rollBack();
                                continue;
                            }
                            $checkStatus = true;
                            $_add_queue_stat_payment[] = [
                                "apartmentId" => $logCoin->bdc_apartment_id,
                                "service_price_id" => $logCoin->bdc_apartment_service_price_id,
                                "cycle_name" => $logCoin->cycle_name,
                            ];
                        }


                        if (!$checkStatus && $data->type != 'phieu_thu_truoc' && $data->type != 'phieu_chi_khac') { // nếu ko tìm thấy giao dịch nào thì là lỗi
                            DB::rollBack();
                            continue;
                        }

                        $receiptRepository->update(["description" => 'hủy', 'updated_by' => auth()->user()->id], $id);
                        $receiptRepository->delete(["id" => $id]); // update xóa phiếu thu
                        Cache::store('redis')->forget(env('REDIS_PREFIX') . 'get_detail_receiptById_' . $id);
                        if ($data->payment_transaction) {
                            $payment_transaction = $data->payment_transaction;
                            $history_transaction = HistoryTransactionAccounting::where(['ngan_hang' => $payment_transaction, 'status' => 'da_hach_toan'])->first();
                            if ($history_transaction && $history_transaction->ten_khach_hang == null) {
                                $history_transaction->status = 'cho_hach_toan';
                                $history_transaction->bdc_apartment_id = null;
                                $history_transaction->detail = null;
                                $history_transaction->save();
                            } else {   // xác định được căn hộ
                                $history_transaction->status = 'cho_hach_toan';
                                $history_transaction->save();
                            }
                        }
                        DB::commit();

                    } catch (Exception $e) {
                        DB::rollBack();
                        continue;
                    }
                }
                if ($_add_queue_stat_payment && count($_add_queue_stat_payment) > 0) {
                    foreach ($_add_queue_stat_payment as $key => $value) {
                        QueueRedis::setItemForQueue('add_queue_stat_payment_', $value);
                    }
                }
                return back()->with('success', 'Xử lý xóa thành công!');
            }
        }
    }

    public function create(Request $request, ApartmentsRespository $apartmentsRespository, ServiceRepository $serviceRepository)
    {

        $meta_title = 'Tạo phiếu thu';
        $services = $serviceRepository->filterBuildingId($this->building_active_id);
        $apartments = $apartmentsRespository->getApartmentOfBuildingV3($this->building_active_id);
        if (isset($request->apartmentId)) {
            $transactionPaymentReceipt = $this->_transactionPaymentRepository->transactionPaymentReceiptAmount($this->building_active_id, $request->apartmentId);
        }
        $tai_khoan_ngan_hang = PaymentInfo::lists($this->building_active_id);
        $tai_khoan_ke_toan_phieu_thu = AccountingAccounts::lists($this->building_active_id);
        $check_type_default = Category::where(['bdc_building_id' => $this->building_active_id, 'type' => 'receipt', 'default' => 1])->first();
        $promotions = Promotion::where('building_id', $this->building_active_id)->whereDate('begin', '<=', Carbon::now()->format('Y-m-d'))->whereDate('end', '>=', Carbon::now()->format('Y-m-d'))->where('status', 1)->get();
        if (!$check_type_default) {
            $list_type_services = Helper::loai_phieu_thu;
            foreach ($list_type_services as $key => $value) {
                Category::create([
                    'type' => 'receipt',
                    'alias' => str_slug($value),
                    'bdc_building_id' => $this->building_active_id,
                    'url_id' => Auth::user()->id,
                    'user_id' => Auth::user()->id,
                    'default' => 1,
                    'content' => '<p>config phiếu thu</p>',
                    'status' => 1,
                    'title' => $value,
                    'category' => $key,
                    'config' => str_slug($value, '_'),
                ]);
            }
        }
        $typeReceipt = Category::where(['type' => 'receipt', 'bdc_building_id' => $this->building_active_id, 'status' => 1])->get();
        return view('receipt.v2.create_v2', [
            'meta_title' => 'Tạo phiếu thu',
            'apartments' => $apartments,
            'apartmentId' => @$request->apartmentId,
            'vi_can_ho' => isset($transactionPaymentReceipt[0]) ? (int)$transactionPaymentReceipt[0]->so_du : 0,
            'building_id' => $this->building_active_id,
            'services' => $services,
            'active_building' => null,
            'promotions' => json_encode($promotions),
            'banks' => Helper::banks(),
            'tai_khoan_ngan_hang' => $tai_khoan_ngan_hang,
            'tai_khoan_ke_toan_phieu_thu' => $tai_khoan_ke_toan_phieu_thu,
            'typeReceipt' => $typeReceipt,
        ]);
    }

    public function phieu_dieu_chinh(Request                         $request, ApartmentsRespository $apartmentsRespository, Vehicles $vehicle,
                                     ServiceRepository               $serviceRepository,
                                     ApartmentServicePriceRepository $serviceApartmentRepository)
    {

        $data['meta_title'] = 'Tạo phiếu điều chỉnh';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        $apartments = $apartmentsRespository->getApartmentOfBuildingV3($this->building_active_id);

        $typeReceipt = Category::where(['type' => 'receipt', 'bdc_building_id' => $this->building_active_id, 'status' => 1])->get();

        $receipt_apartments = @$request->bdc_apartment_id ? BdcV2DebitDetailDebitDetailRepository::getDebitByApartment($request, $this->building_active_id)->paginate($data['per_page']) : null;

        $coin_apartment = @$request->bdc_apartment_id ? BdcCoinRepository::getCoinByApartmentId_v2($request->bdc_apartment_id) : null;


        $data['apartments'] = $apartments;
        $data['typeReceipt'] = $typeReceipt;
        //$data['coin_apartment'] = $coin_apartment;
        $data['vehicle'] = $vehicle;
        $data['serviceRepository'] = $serviceRepository;
        $data['serviceApartmentRepository'] = $serviceApartmentRepository;
        $data['receipt_apartments'] = $receipt_apartments;
        return view('receipt.v2.create_phieu_dieu_chinh_giam', $data);
    }

    public function save_adjustment_slip(Request          $request, ReceiptRepository $receiptRepository,
                                         ConfigRepository $config)
    {

        $base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http") . "://" . @$_SERVER['HTTP_HOST'];
        $input = $request->all();
        $customerDescription = Helper::convert_vi_to_en($input['description']);
        $currentTime = Carbon::now()->hour . ':' . Carbon::now()->minute . ':' . Carbon::now()->second;
        $createdDate = $input['created_date'] == null ? Carbon::now() : Carbon::parse($input['created_date']);
        if (Carbon::parse($input['created_date']) > Carbon::now()->addDays(1)) {
            return $this->responseError('Ngày hạch toán không được lớn hơn ngày lập phiếu', 405);
        }
        $apartment_id = $input['apartment_id'];
        if (!$apartment_id) {
            return $this->responseError('Chưa chọn căn hộ', 402);
        }
        // lấy thông tin chủ hộ
        $_customer = CustomersRespository::findApartmentIdV2($input['apartment_id'], 0);
        $user_info = $_customer ? PublicUsersProfileRespository::getInfoUserById($_customer->user_info_id) : null;
        DB::beginTransaction();
        $buildingId = Apartments::get_detail_apartment_by_apartment_id($input['apartment_id'])->building_id;
        $action = Helper::getAction();
        if ($action) {
            $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id, $createdDate->format('Ym'), $action);
            if ($check_lock_cycle) {
                return $this->responseSuccess([], "Tạo phiếu điều chỉnh thất bại. kỳ " . $createdDate->format('Ym') . " đã được khóa", 200);
            }
        }
        $_add_queue_stat_payment = null;
        try {
            $dataReceipts = $request->list_items;
            if ($dataReceipts && count($dataReceipts) > 0) {
                $code_receipt = $receiptRepository->autoIncrementReceiptCodeAdjustmentSlip($config, $buildingId);
                $status = $receiptRepository::COMPLETED;
                $receipt = $receiptRepository->create([
                    'bdc_apartment_id' => $request->apartment_id,
                    'bdc_building_id' => $buildingId,
                    'receipt_code' => $code_receipt,
                    'cost' => abs($request->paid_payment),
                    'cost_paid' => abs($request->paid_payment),
                    'customer_name' => @$user_info->full_name,
                    'customer_address' => Apartments::get_detail_apartment_by_apartment_id($request->apartment_id)->name,
                    'provider_address' => 'Banking',
                    'bdc_receipt_total' => 'test',
                    'logs' => json_encode($dataReceipts),
                    'description' => $customerDescription,
                    'ma_khach_hang' => $request->ma_khach_hang,
                    'ten_khach_hang' => $request->ten_khach_hang,
                    'type_payment' => $request->type,
                    'url' => $base_url . "/admin/v2/receipt/getReceipt/" . $code_receipt,
                    'user_id' => Auth::user()->id,
                    'type' => ReceiptRepository::PHIEU_DIEUCHINH,
                    'status' => $status,
                    'create_date' => $createdDate,
                    'config_type_payment' => 1,
                ]);
                $debit_details = null;
                foreach ($dataReceipts as $key => $value) {

                    if (str_contains($value['service_apartment_id'], 'tien_thua_')) { // nếu là tiền thừa

                        $service_apartment_id = explode('tien_thua_', $value['service_apartment_id'])[1];

                        BdcCoinRepository::subCoin($buildingId, $request->apartment_id, $service_apartment_id, $createdDate->format('Ym'), $_customer->user_info_id, $value['paid_payment'], Auth::user()->id, 6, $receipt->id, $receipt->id);
                    } else {
                        // nếu là dịch vụ
                        $debit_id = explode('tien_dich_vu_', $value['service_apartment_id'])[1];

                        $debitDetail = DebitDetail::find($debit_id);
                        $debit_details[] = $debitDetail;

                        PaymentDetailRepository::createPayment(
                            $buildingId,
                            $debitDetail->bdc_apartment_id,
                            $debitDetail->bdc_apartment_service_price_id,
                            $createdDate->format('Ym'),
                            $debitDetail->id,
                            0 - $value['paid_payment'],
                            $createdDate,
                            $receipt->id,
                            0
                        );
                        BdcCoinRepository::addCoin($buildingId, $request->apartment_id, $debitDetail->bdc_apartment_service_price_id, $createdDate->format('Ym'), $_customer->user_info_id, $value['paid_payment'], Auth::user()->id, 6, $receipt->id, $customerDescription);
                        if ($debitDetail && isset($debitDetail->cycle_name) && $debitDetail->cycle_name != $createdDate->format('Ym')) { // cập nhật lại cycle_name debit
                            dBug::trackingPhpErrorV2($debitDetail->cycle_name);
                            $_add_queue_stat_payment[] = [
                                "apartmentId" => $debitDetail->bdc_apartment_id,
                                "service_price_id" => $debitDetail->bdc_apartment_service_price_id,
                                "cycle_name" => $debitDetail->cycle_name,
                            ];
                        }
                        $_add_queue_stat_payment[] = [
                            "apartmentId" => $debitDetail->bdc_apartment_id,
                            "service_price_id" => $debitDetail->bdc_apartment_service_price_id,
                            "cycle_name" => $createdDate->format('Ym'),
                        ];
                    }
                }
                $receipt->metadata = json_encode($debit_details);
                $receipt->save();

                DB::commit();
                if ($_add_queue_stat_payment && count($_add_queue_stat_payment) > 0) {
                    foreach ($_add_queue_stat_payment as $key => $value) {
                        QueueRedis::setItemForQueue('add_queue_stat_payment_', $value);
                    }
                }
                return $this->responseSuccess([], 'Tạo phiếu điều chỉnh thành công.', 200);
            }

        } catch (Exception $e) {
            DB::rollBack();
            throw new \Exception("register ERROR: " . $e->getMessage(), 1);
        }
    }

    public function create_payment_slip(Request                         $request, ApartmentsRespository $apartmentsRespository, Vehicles $vehicle,
                                        ServiceRepository               $serviceRepository,
                                        ApartmentServicePriceRepository $serviceApartmentRepository)
    {

        $data['meta_title'] = 'Tạo phiếu chi từ coin';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        $apartments = $apartmentsRespository->getApartmentOfBuildingV3($this->building_active_id);

        $typeReceipt = Category::where(['type' => 'receipt', 'bdc_building_id' => $this->building_active_id, 'status' => 1])->get();

        $coin_apartment = @$request->bdc_apartment_id ? BdcCoinRepository::getCoinByApartmentId_v2($request->bdc_apartment_id) : null;

        $data['apartments'] = $apartments;
        $data['typeReceipt'] = $typeReceipt;
        $data['coin_apartment'] = $coin_apartment;
        $data['vehicle'] = $vehicle;
        $data['serviceRepository'] = $serviceRepository;
        $data['serviceApartmentRepository'] = $serviceApartmentRepository;
        return view('receipt.v2.create_payment_slip', $data);
    }

    public function save_payment_slip(Request          $request, ReceiptRepository $receiptRepository,
                                      ConfigRepository $config)
    {
        $base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http") . "://" . @$_SERVER['HTTP_HOST'];
        $input = $request->all();
        $customerDescription = Helper::convert_vi_to_en($input['description']);
        $createdDate = $input['created_date'] == null ? Carbon::now() : Carbon::parse($input['created_date']);
        if (Carbon::parse($input['created_date']) > Carbon::now()->addDays(1)) {
            return $this->responseError('Ngày hạch toán không được lớn hơn ngày lập phiếu', 405);
        }
        $apartment_id = $input['apartment_id'];
        if (!$apartment_id) {
            return $this->responseError('Chưa chọn căn hộ', 402);
        }
        // lấy thông tin chủ hộ
        $_customer = CustomersRespository::findApartmentIdV2($input['apartment_id'], 0);
        $user_info = $_customer ? PublicUsersProfileRespository::getInfoUserById($_customer->user_info_id) : null;
        DB::beginTransaction();
        $_add_queue_stat_payment = null;
        $buildingId = Apartments::get_detail_apartment_by_apartment_id($input['apartment_id'])->building_id;
        try {
            $dataReceipts = $request->list_items;
            if ($dataReceipts && count($dataReceipts) > 0) {
                $code_receipt = $receiptRepository->autoIncrementReceiptPaymentSlipCode($config, $buildingId);
                $status = $receiptRepository::COMPLETED;
                $receipt = $receiptRepository->create([
                    'bdc_apartment_id' => $request->apartment_id,
                    'bdc_building_id' => $buildingId,
                    'receipt_code' => $code_receipt,
                    'cost' => abs($request->paid_payment),
                    'cost_paid' => abs($request->paid_payment),
                    'customer_name' => @$user_info->full_name,
                    'customer_address' => Apartments::get_detail_apartment_by_apartment_id($request->apartment_id)->name,
                    'provider_address' => 'Banking',
                    'bdc_receipt_total' => 'test',
                    'logs' => json_encode($dataReceipts),
                    'description' => $customerDescription,
                    'ma_khach_hang' => $request->ma_khach_hang,
                    'ten_khach_hang' => $request->ten_khach_hang,
                    'type_payment' => $request->type,
                    'url' => $base_url . "/admin/v2/receipt/getReceipt/" . $code_receipt,
                    'user_id' => Auth::user()->id,
                    'type' => ReceiptRepository::PHIEUCHI,
                    'status' => $status,
                    'create_date' => $createdDate,
                    'config_type_payment' => 1,
                ]);
                foreach ($dataReceipts as $key => $value) {

                    if (str_contains($value['service_apartment_id'], 'tien_thua_')) { // nếu là tiền thừa

                        $service_apartment_id = explode('tien_thua_', $value['service_apartment_id'])[1];

                        BdcCoinRepository::subCoin($buildingId, $request->apartment_id, $service_apartment_id, $createdDate->format('Ym'), $_customer->user_info_id, $value['paid_payment'], Auth::user()->id, 9, $receipt->id, $receipt->id);
                        if ($service_apartment_id > 0) {
                            dBug::trackingPhpErrorV2('tổ hợp phiếu chi:' . $service_apartment_id . 'kỳ:' . $createdDate->format('Ym'));

                            $_add_queue_stat_payment[] = [
                                "apartmentId" => $request->apartment_id,
                                "service_price_id" => $service_apartment_id,
                                "cycle_name" => $createdDate->format('Ym'),
                            ];
                        }

                    }
                }

                DB::commit();
                if ($_add_queue_stat_payment && count($_add_queue_stat_payment) > 0) {
                    foreach ($_add_queue_stat_payment as $key => $value) {
                        QueueRedis::setItemForQueue('add_queue_stat_payment_', $value);
                    }
                }
                return $this->responseSuccess([], 'Tạo phiếu chi thành công.', 200);
            }

        } catch (Exception $e) {
            Log::info('check_send_trans', '4_' . json_encode($e->getMessage()));
            DB::rollBack();
            throw new \Exception("register ERROR: " . $e->getMessage(), 1);
        }
    }

    public function save(
        Request           $request,
        BillRepository    $billRepository,
        ReceiptRepository $receiptRepository,
        ConfigRepository  $config
    )
    {
        $base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http") . "://" . @$_SERVER['HTTP_HOST'];
        $input = $request->all();
        // DebugLog::info("message_receipt",json_encode($input));
        $customerFullname = $input['customer_fullname'];//
        $apartment_id = $input['apartment_id'];//
        $dataReceipts = json_decode($input['data_receipt']);
        // log::info('check______67867:',$dataReceipts);
        // die;

        $customerAddress = $input['customer_address'];//
        $customerDescription = Helper::convert_vi_to_en($input['customer_description']);
        $customerTotalPaid = $input['customer_total_paid'];//
        $typePayment = $input['type_payment']; //
        $type = $input['type'];
        $typeReceipt = $input['type_receipt'];
        $paid_money = $input['paid_money'];
        $customer_paid_string = $input['customer_paid_string'];
        $vnpay_payment = '';
        $currentTime = Carbon::now()->hour . ':' . Carbon::now()->minute . ':' . Carbon::now()->second;
        $createdDate = $input['created_date'] == null ? Carbon::now() : Carbon::parse($input['created_date']);
        if (Carbon::parse($input['created_date']) > Carbon::now()->addDays(1)) {
            return $this->responseError('Ngày hạch toán không được lớn hơn ngày lập phiếu', 405);
        }
        $cycle = $createdDate->format('Ym');
        $action = Helper::getAction();
        if ($action) {
            $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id, $cycle, $action);
            if ($check_lock_cycle) {
                $responseData = [
                    'success' => true,
                    'message' => "Kỳ $cycle đã được khóa."
                ];
                return response()->json($responseData);
            }
        }
        $total_paid = collect($dataReceipts)->sum('paid');
        if (!$apartment_id) {
            return $this->responseError('Chưa chọn căn hộ', 402);
        }
        // lấy thông tin chủ hộ
        $_customer = CustomersRespository::findApartmentIdV2($apartment_id, 0);
        $buildingId = Apartments::get_detail_apartment_by_apartment_id($apartment_id)->building_id;
        // xử lý khi tạo phiếu thu trùng lặp
        $receipted = $receiptRepository->LatestRecordDatabaseByDatetime($customerTotalPaid, $request->apartment_id, $typePayment);
        $user_id = auth()->user()->id;
        if ($receipted && $receipted->user_id != $user_id) {
            return $this->responseError('Hệ thống đã hủy phiếu thu bị trùng!', 402);
        }
        $_add_queue_stat_payment = null;
        if ($dataReceipts == null && $customer_paid_string > 0 && $type != 1) {
            if ($typeReceipt === $receiptRepository::PHIEUKETOAN) {
                $code_receipt = $receiptRepository->autoIncrementAccountingReceiptCode($config, $buildingId);
            } else if ($typeReceipt === $receiptRepository::PHIEUBAOCO) {
                $code_receipt = $receiptRepository->autoIncrementCreditTransferReceiptCode($config, $buildingId);
            } else if ($typeReceipt === $receiptRepository::PHIEUCHI) {
                $code_receipt = $receiptRepository->autoIncrementReceiptPaymentSlipCode($config, $buildingId);
            } else {
                $code_receipt = $receiptRepository->autoIncrementReceiptCode($config, $buildingId);
            }
            $status = $receiptRepository::COMPLETED;
            $receipt = $receiptRepository->create([
                'bdc_apartment_id' => $apartment_id,
                'bdc_building_id' => $buildingId,
                'receipt_code' => $code_receipt,
                'cost' => $customerTotalPaid,
                'cost_paid' => $paid_money,
                'customer_name' => $customerFullname,
                'customer_address' => $customerAddress,
                'provider_address' => 'Banking',
                'bdc_receipt_total' => 'test',
                'logs' => 'phieu_thu',
                'description' => $customerDescription,
                'ma_khach_hang' => $request->ma_khach_hang,
                'ten_khach_hang' => $request->ten_khach_hang,
                'tai_khoan_co' => $request->tai_khoan_co,
                'tai_khoan_no' => $request->tai_khoan_no,
                'ngan_hang' => $request->ngan_hang,
                'type_payment' => $typePayment,
                'url' => $base_url . "/admin/v2/receipt/getReceipt/" . $code_receipt,
                'user_id' => Auth::user()->id,
                'type' => $typeReceipt,
                'status' => $status,
                'url_payment' => $vnpay_payment,
                'create_date' => $createdDate,
                'account_balance' => $customer_paid_string,
                'config_type_payment' => $type
            ]);

            // xử lý thu thừa tiền
            if ($customer_paid_string > 0) {
                BdcCoinRepository::addCoin($buildingId, $apartment_id, 0, $createdDate->format('Ym'), $_customer->user_info_id, $customer_paid_string, Auth::user()->id, 1, $receipt->id, $customerDescription);
                $_add_queue_stat_payment[] = [
                    "apartmentId" => $apartment_id,
                    "service_price_id" => 0,
                    "cycle_name" => $createdDate->format('Ym'),
                ];
            }

            $receipt->type_payment_name = Helper::loai_danh_muc[$typePayment];

            $urlPdf = $base_url . "/admin/v2/receipt/getReceipt/" . $receipt->receipt_code;

            return $this->responseSuccess([null, 'url_pdf' => $urlPdf, 'vnpay' => $vnpay_payment], 'Tạo phiếu thu thành công.', 200);
        }
        if ($typePayment == 'vi') {
            $transactionPaymentReceipt = $this->_transactionPaymentRepository->transactionPaymentReceiptAmount($buildingId, $request->apartment_id);
            $so_du_can_ho = isset($transactionPaymentReceipt[0]) ? (int)$transactionPaymentReceipt[0]->so_du : 0;
            if ($so_du_can_ho - $total_paid < 0) {
                return $this->responseError('Số dư trong ví không đủ để thánh toán hóa đơn!', 402);
            }
        }
        if ($customer_paid_string < $total_paid && $type == 2) {
            return $this->responseError('Số tiền nộp phải lớn hơn hoặc bằng số tiền thanh toán!', 402);
        }
        $name_building = Building::where('id', $buildingId)->first()->name ?? '';

        if (!$dataReceipts) {
            return $this->responseError('Không có dịch vụ nào được chọn.', 402);
        }

        $dataReceipts = collect($dataReceipts)->unique('debit_id');

        $check = false;

        DB::beginTransaction();

        try {
            if ($type == 1) { // Hạch toán dịch vụ
                $array_bills = null;
                $total_so_tien = 0;
                // lấy số tiền không chỉ định để hạch toán phiếu thu
                $total_so_du = BdcCoinRepository::getCoinTotal($apartment_id);

                if ($total_paid > 0 && $total_so_du == 0) {
                    return $this->responseError('Số tiền thừa không đủ để hạch toán hóa đơn!', 402);
                }

                // Tạo mã phiếu thu
                //Log::info('tu_check_receipt_v2','type_:'.$typeReceipt.'apartmentId_:'.$apartment_id);
                if ($typeReceipt === $receiptRepository::PHIEUKETOAN) {
                    $code_receipt = $receiptRepository->autoIncrementAccountingReceiptCode($config, $buildingId);
                } else if ($typeReceipt === $receiptRepository::PHIEUBAOCO) {
                    $code_receipt = $receiptRepository->autoIncrementCreditTransferReceiptCode($config, $buildingId);
                } else if ($typeReceipt === $receiptRepository::PHIEUCHI) {
                    $code_receipt = $receiptRepository->autoIncrementReceiptPaymentSlipCode($config, $buildingId);
                } else {
                    $code_receipt = $receiptRepository->autoIncrementReceiptCode($config, $buildingId);
                }
                $status = $receiptRepository::COMPLETED;
                if ($typePayment == 'vnpay') {
                    if (!isset($input['bank'])) {
                        return $this->responseError('Chưa chọn ngân hàng.', 404);
                    }
                    $vnpay_payment = $this->createPayment($code_receipt, $customerTotalPaid, $customerDescription, 1, null, $input['bank']);
                    $status = $receiptRepository::NOTCOMPLETED;
                }

                $receipt = $receiptRepository->create([
                    'bdc_apartment_id' => $apartment_id,
                    'bdc_building_id' => $buildingId,
                    'receipt_code' => $code_receipt,
                    'cost' => $customerTotalPaid,
                    'cost_paid' => $paid_money,
                    'customer_name' => $customerFullname,
                    'customer_address' => $customerAddress,
                    'provider_address' => 'Banking',
                    'bdc_receipt_total' => 'test',
                    'logs' => json_encode($dataReceipts),
                    'description' => $customerDescription,
                    'ma_khach_hang' => $request->ma_khach_hang,
                    'ten_khach_hang' => $request->ten_khach_hang,
                    'tai_khoan_co' => $request->tai_khoan_co,
                    'tai_khoan_no' => $request->tai_khoan_no,
                    'ngan_hang' => $request->ngan_hang,
                    'type_payment' => $typePayment,
                    'url' => $base_url . "/admin/v2/receipt/getReceipt/" . $code_receipt,
                    'user_id' => Auth::user()->id,
                    'type' => $typeReceipt,
                    'status' => $status,
                    'url_payment' => $vnpay_payment,
                    'create_date' => $createdDate,
                    'config_type_payment' => $type
                ]);
                $billIds = array();
                $debit_details = null;
                foreach ($dataReceipts as $dataReceipt) {

                    DebugLog::info('tu', 'danh sach' . json_encode($dataReceipt));

                    $debitDetail = DebitDetail::find($dataReceipt->debit_id);
                    if (!$debitDetail) {
                        // ghi log không tìm thấy dịch vụ cần thu
                        continue;
                    }
                    array_push($billIds, $dataReceipt->bill_code);
                    // kiếm tra xem số tiền thanh toán
                    if ($dataReceipt->chi_dinh_hach_toan === null) {
                        continue;
                    }

                    if (@$dataReceipt->promotion_id) {
                        $promotion = Promotion::find($dataReceipt->promotion_id);
                        $begin_date = Carbon::parse($promotion->begin);
                        $end_date = Carbon::parse($promotion->end)->format("Y-m-d");
                        $new_end_date = Carbon::parse($end_date . " 23:59:59");
                        $check = Carbon::now()->between($begin_date, $new_end_date);
                        $time_promotion = $promotion->begin . '->' . $new_end_date;
                        $bill = $this->billRepo->findBillFistNew($buildingId, $apartment_id);
                        Log::info("bill" , json_encode($bill));
                        $begin_cycle_name = Carbon::createFromFormat('Ym', $bill->cycle_name)->addMonth()->format('Ym');
                        if ($promotion) {
                            $apart_service = ApartmentServicePrice::find($debitDetail->bdc_apartment_service_price_id);
                            $service = $apart_service->service;
                            if ($check == false) {
                                DB::rollBack();
                                return $this->responseError($apart_service ? @$service->name . ' Không nằm trong khoảng thời gian khuyến mại' . $time_promotion : ' Không nằm trong khoảng thời gian khuyến mại' . $time_promotion, 402);
                            }
                            if ($dataReceipt->paid < $dataReceipt->promotion_price) {
                                DB::rollBack();
                                return $this->responseError($apart_service ? @$service->name . ' Số tiền không đủ điều kiện khuyến mại' : ' Số tiền không đủ điều kiện khuyến mại', 402);
                            }
                            $result_reset = Api::POST('admin/promotion/addPromotionApartment?building_id=' . $buildingId, [
                                'type' => 'service_vehicle',
                                'service_id' => $service->id,
                                'service_price_id' => $apart_service->id,
                                'apartment_id' => $apartment_id,
                                'promotion_id' => $promotion->id,
                                'begin_cycle_name' => $begin_cycle_name
                            ]);
                            if ($result_reset->status == false) {
                                DB::rollBack();
                                return $this->responseError($result_reset->mess, 402);
                            }
                        }
                        $debitDetail = DebitDetail::find($dataReceipt->debit_id);
                    }

                    $check_coin = BdcCoinRepository::getCoin($apartment_id, $dataReceipt->chi_dinh_hach_toan);

                    if ($check_coin->coin == 0 || $check_coin->coin < (int)$dataReceipt->paid) {
                        DB::rollBack();
                        $apart_service = $dataReceipt->chi_dinh_hach_toan != 0 ? ApartmentServicePrice::find($dataReceipt->chi_dinh_hach_toan) : null;
                        return $this->responseError($apart_service ? @$apart_service->service->name . ' không đủ để phân bổ' : 'Không đủ tiền để phân bổ', 402);
                    }

                    $check = true; // nếu chạy đến đây thì là có dịch vụ được hạch toán

                    $so_tien = $check_coin->coin <= (int)$dataReceipt->paid ? $check_coin->coin : (int)$dataReceipt->paid;


                    $total_so_tien += $so_tien;

                    // update giam coin từ chỉ định
                    $rsCoin = BdcCoinRepository::subCoin($buildingId, $apartment_id, $dataReceipt->chi_dinh_hach_toan, $createdDate->format('Ym'), $_customer->user_info_id, $so_tien, Auth::user()->id, 4, $dataReceipt->apartment_service_price_id, $receipt->id);


                    // kiếm tra xem số tiền thanh toán
                    if ($dataReceipt->paid > $dataReceipt->total_payment_current) { // số tiền thanh toán và số tiền còn nợ
                        $paid_service = $dataReceipt->total_payment_current;
                        $coinAdd = (int)$dataReceipt->paid - $dataReceipt->total_payment_current;
                        BdcCoinRepository::addCoin($buildingId, $apartment_id, $dataReceipt->apartment_service_price_id, $createdDate->format('Ym'), $_customer->user_info_id, $coinAdd, Auth::user()->id, 3, $dataReceipt->chi_dinh_hach_toan, $receipt->id);
                    } else {
                        $paid_service = $dataReceipt->paid;
                    }

                    PaymentDetailRepository::createPayment(
                        $buildingId,
                        $apartment_id,
                        $dataReceipt->apartment_service_price_id,
                        $createdDate->format('Ym'),
                        $debitDetail->id,
                        $paid_service,
                        $createdDate,
                        $receipt->id,
                        $rsCoin && isset($rsCoin['log']) ? $rsCoin['log'] : 0
                    );

                    $debitDetail->paid = $debitDetail->paid + $paid_service;
                    if (preg_match('/\d/', $dataReceipt->chiet_khau) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                        DB::rollBack();
                        $apart_service = ApartmentServicePrice::find($debitDetail->bdc_apartment_service_price_id);
                        return $this->responseError($apart_service ? @$apart_service->service->name . 'giá Giảm trừ không phải là số' : 'giá Giảm trừ không phải là số', 402);
                    }
                    //Log::info("tu_check_receipt",'_1:'.$debitDetail->id.'|'.$dataReceipt->paid .'|'. $dataReceipt->total_payment_current);
//                    $sumery_service = $debitDetail->sumery + $debitDetail->discount;
//                    $debitDetail->discount = $dataReceipt->chiet_khau;
//                    $debitDetail->sumery = $dataReceipt->chiet_khau != 0 ? $sumery_service - $dataReceipt->chiet_khau : $debitDetail->sumery;
//                    $debitDetail->discount_note = $debitDetail->discount_note . '|' . $sumery_service;
                    $debitDetail->save();
                    $debit_details[] = $debitDetail;
                    $_add_queue_stat_payment[] = [
                        "apartmentId" => $apartment_id,
                        "service_price_id" => $debitDetail->bdc_apartment_service_price_id,
                        "cycle_name" => $createdDate->format('Ym'),
                    ];
                    if ($debitDetail->cycle_name != $createdDate->format('Ym')) { // tìm kỳ lập phiếu thu và cập nhật lại để thống kê sau này
                        $_add_queue_stat_payment[] = [
                            "apartmentId" => $apartment_id,
                            "service_price_id" => $dataReceipt->apartment_service_price_id,
                            "cycle_name" => $debitDetail->cycle_name,
                        ];
                    }

                }
                $strBillIds = serialize($billIds);
                $receipt->bdc_bill_id = $strBillIds;
                $receipt->cost = $total_so_tien;
                $receipt->cost_paid = $total_so_tien;
                $receipt->metadata = json_encode($debit_details);
                $receipt->save();

                $receipt->type_payment_name = Helper::loai_danh_muc[$typePayment];

            } else {         // Thu tiền dịch vụ

                if ($customer_paid_string == 0) {
                    return $this->responseError('Số tiền nộp không đủ để thanh toán dịch vụ', 402);
                }

                // Tạo phiếu thu
                if ($typeReceipt === $receiptRepository::PHIEUKETOAN) {
                    $code_receipt = $receiptRepository->autoIncrementAccountingReceiptCode($config, $buildingId);
                } else if ($typeReceipt === $receiptRepository::PHIEUBAOCO) {
                    $code_receipt = $receiptRepository->autoIncrementCreditTransferReceiptCode($config, $buildingId);
                } else if ($typeReceipt === $receiptRepository::PHIEUCHI) {
                    $code_receipt = $receiptRepository->autoIncrementReceiptPaymentSlipCode($config, $buildingId);
                } else {
                    $code_receipt = $receiptRepository->autoIncrementReceiptCode($config, $buildingId);
                }
                $status = $receiptRepository::COMPLETED;
                if ($typePayment == 'vnpay') {
                    if (!isset($input['bank'])) {
                        return $this->responseError('Chưa chọn ngân hàng.', 404);
                    }
                    $vnpay_payment = $this->createPayment($code_receipt, $customerTotalPaid, $customerDescription, 1, null, $input['bank']);
                    $status = $receiptRepository::NOTCOMPLETED;
                }
                // DebugLog::info("message_receipt",json_encode($input));
                // dBug::trackingPhpErrorV2($typeReceipt);
                $receipt = $receiptRepository->create([
                    'bdc_apartment_id' => $apartment_id,
                    'bdc_building_id' => $buildingId,
                    'receipt_code' => $code_receipt,
                    'cost' => $customerTotalPaid,
                    'cost_paid' => $paid_money,
                    'customer_name' => $customerFullname,
                    'customer_address' => $customerAddress,
                    'provider_address' => 'Banking',
                    'bdc_receipt_total' => 'test',
                    'logs' => json_encode($dataReceipts),
                    'description' => $customerDescription,
                    'ma_khach_hang' => $request->ma_khach_hang,
                    'ten_khach_hang' => $request->ten_khach_hang,
                    'tai_khoan_co' => $request->tai_khoan_co,
                    'tai_khoan_no' => $request->tai_khoan_no,
                    'ngan_hang' => $request->ngan_hang,
                    'type_payment' => $typePayment,
                    'url' => $base_url . "/admin/v2/receipt/getReceipt/" . $code_receipt,
                    'user_id' => Auth::user()->id,
                    'type' => $typeReceipt,
                    'status' => $status,
                    'url_payment' => $vnpay_payment,
                    'create_date' => $createdDate,
                    'config_type_payment' => $type
                ]);
                $billIds = array();
                $debit_details = null;
                $total_paid_service = 0;
                foreach ($dataReceipts as $dataReceipt) {
                    $debitDetail = DebitDetail::find($dataReceipt->debit_id);
                    if (!$debitDetail) {
                        // ghi log không tìm thấy dịch vụ cần thu
                        continue;
                    }
                    array_push($billIds, $dataReceipt->bill_code);

                    $paidInt = $dataReceipt->paid;
                    if ($typeReceipt == 'phieu_chi') {
                        $paidInt = $paidInt * -1;
                    }
                    if (@$dataReceipt->promotion_id) {
                        $promotion = Promotion::find($dataReceipt->promotion_id);
                        $begin_date = Carbon::parse($promotion->begin);
                        $end_date = Carbon::parse($promotion->end)->format("Y-m-d");
                        $new_end_date = Carbon::parse($end_date . " 23:59:59");
                        $check = Carbon::now()->between($begin_date, $new_end_date);
                        $time_promotion = $promotion->begin . '->' . $new_end_date;
                        $bill = $this->billRepo->findBillFistNew($buildingId, $apartment_id);
                        Log::info("bill" , json_encode($bill));
                        $begin_cycle_name = intval(Carbon::createFromFormat('Ym', $bill->cycle_name)->addMonth()->format('Ym'));
                        if ($promotion) {
                            $apart_service = ApartmentServicePrice::find($debitDetail->bdc_apartment_service_price_id);
                            $service = $apart_service->service;
                            if ($check == false) {
                                DB::rollBack();
                                return $this->responseError($apart_service ? @$service->name . 'Không nằm trong khoảng thời gian khuyến mại' . $time_promotion : 'Không nằm trong khoảng thời gian khuyến mại' . $time_promotion, 402);
                            }
                            if ($dataReceipt->paid < $dataReceipt->promotion_price) {
                                DB::rollBack();
                                return $this->responseError($apart_service ? @$service->name . ' Số tiền không đủ điều kiện khuyến mại' : ' Số tiền không đủ điều kiện khuyến mại', 402);
                            }
                            $result_reset = Api::POST('admin/promotion/addPromotionApartment?building_id=' . $buildingId, [
                                'type' => 'service_vehicle',
                                'service_id' => $service->id,
                                'service_price_id' => $apart_service->id,
                                'apartment_id' => $apartment_id,
                                'promotion_id' => $promotion->id,
                                'begin_cycle_name' => $begin_cycle_name
                            ]);
                            if ($result_reset->status == false) {
                                DB::rollBack();
                                return $this->responseError($result_reset->mess, 402);
                            }

                        }
                        $debitDetail = DebitDetail::find($dataReceipt->debit_id);
                    }
                    // kiếm tra xem số tiền thanh toán
                    $rsCoin = false;
//                    if ($dataReceipt->paid > $debitDetail->sumery) { // số tiền thanh toán và số tiền còn nợ
//                        $paid_service = $debitDetail->sumery;
//                        $coinAdd = (int)$dataReceipt->paid - $debitDetail->sumery;
//                        $rsCoin = BdcCoinRepository::addCoin($buildingId, $apartment_id, $dataReceipt->apartment_service_price_id, $createdDate->format('Ym'), $_customer->user_info_id, $coinAdd, Auth::user()->id, 1, $receipt->id, $customerDescription);
//                    } else {
//                        $paid_service = $dataReceipt->paid;
//                    }

                    if($dataReceipt->paid > $dataReceipt->total_payment_current) { // số tiền thanh toán và số tiền còn nợ
                        $paid_service = $dataReceipt->total_payment_current;
                        $coinAdd        = (int)$dataReceipt->paid - $dataReceipt->total_payment_current;
                        $rsCoin = BdcCoinRepository::addCoin($buildingId,$apartment_id,$dataReceipt->apartment_service_price_id,$createdDate->format('Ym'),$_customer->user_info_id,$coinAdd,Auth::user()->id,1,$receipt->id,$customerDescription);
                    } else {
                        $paid_service = $dataReceipt->paid;
                    }

                    PaymentDetailRepository::createPayment(
                        $buildingId,
                        $apartment_id,
                        $dataReceipt->apartment_service_price_id,
                        $createdDate->format('Ym'),
                        $debitDetail->id,
                        $paid_service,
                        $createdDate,
                        $receipt->id,
                        $rsCoin && isset($rsCoin['log']) ? $rsCoin['log'] : 0
                    );
                    $total_paid_service += $paid_service;
                    $debitDetail->paid = $debitDetail->paid + $paid_service;
                    if (preg_match('/\d/', $dataReceipt->chiet_khau) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                        DB::rollBack();
                        $apart_service = ApartmentServicePrice::find($debitDetail->bdc_apartment_service_price_id);
                        return $this->responseError($apart_service ? @$apart_service->service->name . 'giá Giảm trừ không phải là số' : 'giá Giảm trừ không phải là số', 402);
                    }
                    //Log::info("tu_check_receipt",'_2:'.$debitDetail->id.'|'.$dataReceipt->paid .'|'. $debitDetail->sumery);
//                    $sumery_service = $debitDetail->sumery + $debitDetail->discount;
//                    $debitDetail->discount = $dataReceipt->chiet_khau;
//                    $debitDetail->sumery = $dataReceipt->chiet_khau != 0 ? $sumery_service - $dataReceipt->chiet_khau : $debitDetail->sumery;
//                    $debitDetail->discount_note = $debitDetail->discount_note . '|' . $sumery_service;
                    $debitDetail->save();
                    $debit_details[] = $debitDetail;
                    $_add_queue_stat_payment[] = [
                        "apartmentId" => $apartment_id,
                        "service_price_id" => $debitDetail->bdc_apartment_service_price_id,
                        "cycle_name" => $createdDate->format('Ym'),
                    ];
                    // lấy tổng thanh toán của một dịch vụ trong kỳ
                    if ($debitDetail->cycle_name != $createdDate->format('Ym')) { // tìm kỳ lập phiếu thu và cập nhật lại để thống kê sau này
                        $_add_queue_stat_payment[] = [
                            "apartmentId" => $apartment_id,
                            "service_price_id" => $dataReceipt->apartment_service_price_id,
                            "cycle_name" => $debitDetail->cycle_name,
                        ];
                    }

                }
                // xử lý thu thừa tiền
                $tienthuachuachidinh = intval($customer_paid_string) - intval($total_paid);

                if ($tienthuachuachidinh > 0) {
                    $rsAddCoin = BdcCoinRepository::addCoin($buildingId, $apartment_id, 0, $createdDate->format('Ym'), $_customer->user_info_id, $tienthuachuachidinh, Auth::user()->id, 1, $receipt->id, $customerDescription);
                    $receipt->account_balance = $tienthuachuachidinh;
                    $_add_queue_stat_payment[] = [
                        "apartmentId" => $apartment_id,
                        "service_price_id" => 0,
                        "cycle_name" => $createdDate->format('Ym')
                    ];
                }
                // $tien_thua =  intval($customer_paid_string) -  intval($total_paid_service);
                // if($tien_thua > 0){
                //       $receipt->account_balance = $tien_thua;
                // }
                $strBillIds = serialize($billIds);
                $receipt->bdc_bill_id = $strBillIds;
                $receipt->metadata = json_encode($debit_details);
                $receipt->save();
            }
            if ($check == false && $type == 1) {
                DB::rollBack();
                return $this->responseError('Chưa chọn chỉ định nào được chọn để hạch toán.' . json_encode($dataReceipts), 402);
            }
            $urlPdf = $base_url . "/admin/v2/receipt/getReceipt/" . $receipt->receipt_code;
            DB::commit();
            if ($_add_queue_stat_payment && count($_add_queue_stat_payment) > 0) {
                foreach ($_add_queue_stat_payment as $key => $value) {
                    QueueRedis::setItemForQueue('add_queue_stat_payment_', $value);
                }
            }
            return $this->responseSuccess([null, 'url_pdf' => $urlPdf, 'vnpay' => $vnpay_payment], 'Tạo phiếu thu thành công.', 200);
        } catch (\Exception $e) {
            Log::info('receipt_check', json_encode($e->getTraceAsString()));
            DB::rollBack();
            throw new \Exception("register ERROR: " . $e->getMessage(), 1);
        }
    }

    public function reviewReceipt(Request               $request,
                                  BillRepository        $billRepository,
                                  ReceiptRepository     $receiptRepository,
                                  DebitDetailRepository $debitDetailRepository,
                                  ConfigRepository      $config,
                                  ServiceRepository     $serviceRepository,
                                  ReceiptLogsRepository $receiptLogsRepository)
    {
        $input = $request->all();
        $customerFullname = $input['customer_fullname'];//
        $apartment_id = $input['apartment_id'];//
        $dataReceipts = json_decode($input['data_receipt']);
        $customerAddress = $input['customer_address'];//
        $customerDescription = Helper::convert_vi_to_en($input['customer_description']);
        $customerTotalPaid = $input['customer_total_paid'];//
        $typePayment = $input['type_payment']; //
        $type = $input['type'];
        $typeReceipt = $input['typeReceipt'];
        $paid_money = $input['paid_money'];
        $customer_paid_string = $input['customer_paid_string'];
        $vnpay_payment = '';
        $currentTime = Carbon::now()->hour . ':' . Carbon::now()->minute . ':' . Carbon::now()->second;
        $createdDate = $input['created_date'] == null ? Carbon::now() : Carbon::parse($input['created_date']);
        $total_paid = collect($dataReceipts)->sum('paid');
        if ($dataReceipts == null && $customer_paid_string > 0) {
            $status = $receiptRepository::COMPLETED;
            $code_receipt = $receiptRepository->autoIncrementAccountingReceiptCode($config, $this->building_active_id);
            $receipt = [
                'bdc_apartment_id' => $apartment_id,
                'bdc_building_id' => $this->building_active_id,
                'receipt_code' => $code_receipt,
                'cost' => $customerTotalPaid,
                'cost_paid' => $paid_money,
                'customer_name' => $customerFullname,
                'customer_address' => $customerAddress,
                'provider_address' => 'Banking',
                'bdc_receipt_total' => 'test',
                'logs' => 'phieu_thu_truoc',
                'description' => $customerDescription,
                'ma_khach_hang' => $request->ma_khach_hang,
                'ten_khach_hang' => $request->ten_khach_hang,
                'tai_khoan_co' => $request->tai_khoan_co,
                'tai_khoan_no' => $request->tai_khoan_no,
                'ngan_hang' => $request->ngan_hang,
                'type_payment' => $typePayment,
                'url' => "admin/receipt/getReceipt/" . $code_receipt,
                'user_id' => Auth::user()->id,
                'type' => $typeReceipt,
                'status' => $status,
                'url_payment' => $vnpay_payment,
                'create_date' => $createdDate
            ];
            $apartment = $this->apartmentRepo->findById($apartment_id);
            // xử lý thu thừa tiền
            if ($customer_paid_string > 0) {
                $listService[] = [
                    'cost_paid' => $customer_paid_string,
                ];
            }
            $receipt['type_payment_name'] = Helper::loai_danh_muc[$typePayment];

            $view = view("receipt.v2.pdf_v6", compact('receipt', 'apartment', 'listService', 'serviceRepository'))->render();
            return $this->responseSuccess([
                'html' => $view,
            ]);
        }
        if ($typePayment == 'vi') {
            $transactionPaymentReceipt = $this->_transactionPaymentRepository->transactionPaymentReceiptAmount($this->building_active_id, $request->apartment_id);
            $so_du_can_ho = isset($transactionPaymentReceipt[0]) ? (int)$transactionPaymentReceipt[0]->so_du : 0;
            if ($so_du_can_ho - $total_paid < 0) {
                return $this->responseError('Số dư trong ví không đủ để thánh toán hóa đơn!', 402);
            }
        }
        if ($customer_paid_string < $total_paid && $type == 2) {
            return $this->responseError('Số tiền nộp phải lớn hơn hoặc bằng số tiền thanh toán!', 402);
        }
        if (!$dataReceipts) {
            return $this->responseError('Không có dịch vụ nào được chọn.', 402);
        }
        // Tạo phiếu thu
        if ($typeReceipt == $receiptRepository::PHIEUKETOAN) {
            $code_receipt = $receiptRepository->autoIncrementAccountingReceiptCode($config, $this->building_active_id);
        } else if ($typeReceipt == $receiptRepository::PHIEUBAOCO) {
            $code_receipt = $receiptRepository->autoIncrementCreditTransferReceiptCode($config, $this->building_active_id);
        } else if ($typeReceipt == $receiptRepository::PHIEUCHI) {
            $code_receipt = $receiptRepository->autoIncrementReceiptPaymentSlipCode($config, $this->building_active_id);
        } else {
            $code_receipt = $receiptRepository->autoIncrementReceiptCode($config, $this->building_active_id);
        }
        $status = $receiptRepository::COMPLETED;
        if ($typePayment == 'vnpay') {
            if (!isset($input['bank'])) {
                return $this->responseError('Chưa chọn ngân hàng.', 404);
            }
            $vnpay_payment = $this->createPayment($code_receipt, $customerTotalPaid, $customerDescription, 1, null, $input['bank']);
            $status = $receiptRepository::NOTCOMPLETED;
        }

        $receipt = [
            'bdc_apartment_id' => $apartment_id,
            'bdc_building_id' => $this->building_active_id,
            'receipt_code' => $code_receipt,
            'cost' => $customerTotalPaid,
            'cost_paid' => $paid_money,
            'customer_name' => $customerFullname,
            'customer_address' => $customerAddress,
            'provider_address' => 'Banking',
            'bdc_receipt_total' => 'test',
            'logs' => 'test',
            'description' => $customerDescription,
            'ma_khach_hang' => $request->ma_khach_hang,
            'ten_khach_hang' => $request->ten_khach_hang,
            'tai_khoan_co' => $request->tai_khoan_co,
            'tai_khoan_no' => $request->tai_khoan_no,
            'ngan_hang' => $request->ngan_hang,
            'type_payment' => $typePayment,
            'url' => "admin/receipt/getReceipt/" . $code_receipt,
            'user_id' => Auth::user()->id,
            'type' => $typeReceipt,
            'status' => $status,
            'url_payment' => $vnpay_payment,
            'create_date' => $createdDate
        ];

        foreach ($dataReceipts as $dataReceipt) {
            $debitDetail = DebitDetail::find($dataReceipt->debit_id);
            if (!$debitDetail) {
                // ghi log không tìm thấy dịch vụ cần thu
                continue;
            }
            $paidInt = $dataReceipt->paid;
            if ($typeReceipt == 'phieu_chi') {
                $paidInt = $paidInt * -1;
            }
            // kiếm tra xem số tiền thanh toán

            $paid_service = 0;

            if ($dataReceipt->paid > $dataReceipt->total_payment_current) {
                $paid_service = $dataReceipt->total_payment_current;
                $customer_paid_string -= $dataReceipt->total_payment_current;
            } else {
                $paid_service = $dataReceipt->paid;
                $customer_paid_string -= $dataReceipt->paid;
            }
            $service_name = @$debitDetail->apartmentServicePrice->service->name;

            $listService[] = [
                'name' => $service_name,
                'title' => @$debitDetail->apartmentServicePrice->bdc_vehicle_id > 0 ? @$debitDetail->apartmentServicePrice->vehicle->number : $service_name,
                'from_date' => $debitDetail->from_date,
                'to_date' => $debitDetail->apartmentServicePrice->bdc_price_type_id == 2 ? $debitDetail->to_date : $debitDetail->to_date,
                'sumery' => $debitDetail->sumery - $debitDetail->paid,
                'cost' => $paid_service,
            ];
        }

        // xử lý thu thừa tiền
        if ($customer_paid_string > 0) {
            $listService[] = [
                'cost_paid' => $customer_paid_string,
            ];
        }
        $receipt['type_payment_name'] = Helper::loai_danh_muc[$typePayment];


        $apartment = $this->apartmentRepo->findById($apartment_id);
        $view = view("receipt.v2.pdf_v6", compact('receipt', 'apartment', 'listService', 'serviceRepository'))->render();
        return $this->responseSuccess([
            'html' => $view,
        ]);

    }

    public function export()
    {
        try {
           return $this->model->excelReceiptIndex($this->building_active_id);
        }catch (Exception $e){
            return redirect()->back()->with('warning','đã có lỗi xảy ra.');
        }
    }

    public function exportFilterSoQuyTienMat(Request $request)
    {
        try {
            set_time_limit(0);
            return $this->model->filterReceiptExcelNew($this->building_active_id, $request);
        }catch (Exception $e){
            return redirect()->back()->with('warning','đã có lỗi xảy ra.');
        }

    }

    public function exportFilterSoQuyChuyenKhoan(Request $request)
    {
        try {
            set_time_limit(0);
            return $this->model->filterReceiptExcelNewVerBanking($this->building_active_id, $request);
        }catch (Exception $e){
            SendTelegram::SupersendTelegramMessage('excel so quy tien mat EX:'.$e->getMessage());
            return redirect()->back()->with('warning','đã có lỗi xảy ra.');
        }

    }

    public function exportFilterThuChi(Request $request)
    {

        try {
            set_time_limit(0);
            return $this->model->filterReceiptExcel($this->building_active_id, $request);
        }catch (Exception $e){
            return redirect()->back()->with('warning','đã có lỗi xảy ra.');
        }

    }

    public function export_thu_tien_tavico(Request $request, DebitDetailRepository $debitDetailRepository)
    {
        try {
            set_time_limit(0);
            return $this->model->export_thu_tien_tavico($this->building_active_id, $request, $debitDetailRepository);
        }catch (Exception $e){
            return redirect()->back()->with('warning','đã có lỗi xảy ra.');
        }

    }

    public function exportFilterReceiptDeposit(Request $request)
    {
        try {
            set_time_limit(0);
            return $this->model->filterReceiptDepositExcel($this->building_active_id, $request);
        }catch (Exception $e){
            return redirect()->back()->with('warning','đã có lỗi xảy ra.');
        }

    }

    public function exportDetailFilter(Request $request, DebitDetailRepository $debitDetailRepository)
    {
        try {
            set_time_limit(0);
            return $this->model->filterReceiptExcelDetail($this->building_active_id, $request, $debitDetailRepository);
        }catch (Exception $e){
            return redirect()->back()->with('warning','đã có lỗi xảy ra.');
        }

    }

    public function exportDetailFilter_v2(Request $request, DebitDetailRepository $debitDetailRepository, ServiceRepository $serviceRepository)
    {
        try {
            set_time_limit(0);
            return $this->model->filterReceiptExcelDetail_v3($this->building_active_id, $request, $debitDetailRepository, $serviceRepository);
        }catch (Exception $e){
            return redirect()->back()->with('warning','đã có lỗi xảy ra.');
        }

    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $receipt = $this->model->findReceiptById($id);
            if ($receipt) {
                $bills = unserialize($receipt->data);
                if ($bills) {
                    foreach ($bills as $_bill) {
                        $billId = $_bill['bill_id'];
                        $billCode = $_bill['bill_code'];
                        $serviceId = $_bill['service_id'];
                        $version = $_bill['version'];
                        $checkVersion = $this->debitRepo->checkBillIdVersion($billId, $serviceId, $version);
                        if ($checkVersion) {
                            \DB::rollBack();
                            return redirect()->route('admin.receipt.index')->with('error', "Không thể xóa phiếu thu do có liên quan đến phiếu thu khác của Bảng kê $billCode chưa được xử lý.");
                        }
                        $this->debitRepo->filterBillIdVersion($billId, $serviceId, $version)->delete();
                    }
                }
                $check_transactionPayment = TransactionPayment::where('bdc_receipt_id', $id)->first();
                if ($check_transactionPayment) {
                    TransactionPayment::create([
                        'bdc_apartment_id' => $receipt->bdc_apartment_id,
                        'bdc_receipt_id' => $receipt->id,
                        'amount' => $receipt->cost,
                        'note' => $receipt->receipt_code,
                        'status' => 1, //duyệt
                        'type' => 'hoan_tien'
                    ]);
                }
                $receipt->delete();
                Cache::store('redis')->forget(env('REDIS_PREFIX') . 'get_detail_receiptById_' . $id);
                \DB::commit();
                return redirect()->route('admin.receipt.index')->with('success', 'Xóa phiếu thu thành công.');
            }
            \DB::rollBack();
            return redirect()->route('admin.receipt.index')->with('error', "Mã phiếu thu $id không tồn tại.");
        } catch (Exception $e) {
            \DB::rollBack();
            return redirect()->route('admin.receipt.index')->with('error', $e->getMessage());
        }
    }

    public function view_receipt(Request $request, ServiceRepository $serviceRepository, ConfigRepository $configRepository, $code)
    {
        // view_receipt_new_1
        $data['meta_title'] = 'reload pdf phiếu thu';
        $listService = null;
        $receipt = $this->model->findReceiptCodePay($code, $this->building_active_id);
        if (!$receipt) {
            return response('Không tìm thấy phiếu thu hoặc phiếu thu đã bị xóa.');
        }
        $building = Building::get_detail_building_by_building_id($receipt->bdc_building_id);

        $apartment = Apartments::get_detail_apartment_by_apartment_id($receipt->bdc_apartment_id);

        $name_building = @$building->name;

        $get_bill = unserialize($receipt->data);

        $configs = Configs::find($receipt->config_id);
        if ($configs) {
            $receipt->config_id = $configs->title;
        }
        if ($get_bill && $request->version == 1) {

            foreach ($get_bill as $key => $value) {
                $value = (object)$value;
                if (@$value->new_debit_id) {
                    $debitDetail = $this->debitRepo->findDebitV2($value->new_debit_id);
                } else if (@$value->bill_id && @$value->apartment_service_price_id && @$value->version) {
                    $debitDetail = $this->debitRepo->filterServiceBillIdWithVersionV2($value->bill_id, $value->apartment_service_price_id, $value->version);
                } else {
                    $debitDetail = $this->debitRepo->filterServiceBillIdWithVersion($building->bdc_building_id, $value->bill_id, $value->service_id, $value->version);
                }
                if ($debitDetail) {
                    $listService[] = $debitDetail;
                }
            }
            $receipt->type_payment_name = Helper::loai_danh_muc[$receipt->type_payment];
            return view("receipt.pdf_v4", compact('receipt', 'building', 'apartment', 'listService', 'serviceRepository'));
        } else {
            $PaymentDetail = PaymentDetail::where('bdc_receipt_id', $receipt->id)->orderBy('bdc_apartment_service_price_id')->get();
            if ((!$PaymentDetail) || is_null($PaymentDetail) || empty($PaymentDetail)|| ($PaymentDetail === []) )
            {
                SendTelegram::SupersendTelegramMessage('Fail: '.$PaymentDetail);
            }
            if (!$PaymentDetail) {
                return response('Không tìm thấy phiếu thu hoặc phiếu thu đã bị xóa.');
            }
            $nguon_hach_toan = null;
            $sum_total_paid = 0;

            $getTienThua = LogCoinDetailRepository::getDataByFromId($receipt->bdc_apartment_id, 1, $receipt->id);

            foreach ($PaymentDetail as $key => $value) {
                $value->type = 1; // chi tiêt tiền thừa
                $listService[] = $value;
                $sum_total_paid += $value->paid;
                if ($getTienThua) {
                    foreach ($getTienThua as $key_1 => $value_1) {
                        $value_1->type = 2; // chi tiêt tiền thừa
                        if ($value->bdc_apartment_service_price_id == $value_1->bdc_apartment_service_price_id) {
                            $getTienThua->forget($key_1);
                        }
                    }
                }
            }
            $coin = LogCoinDetailRepository::sum_coin($receipt->id);
            $tien_thua = LogCoinDetailRepository::sum_coin_by_accounting(@$receipt->id);
            $sum_total_paid += $coin;
            $sum_total_paid -= $tien_thua;
            $sum_total_paid = $receipt->type == 'phieu_thu_truoc' || $receipt->type == 'phieu_chi_khac' || $receipt->type == 'phieu_thu_ky_quy' || $receipt->type == 'phieu_hoan_ky_quy' || $receipt->type == 'phieu_bao_co' || $receipt->deleted_at != null ? $receipt->cost : $sum_total_paid;

            if ($receipt->type == 'phieu_ke_toan') {
                foreach ($PaymentDetail as $key => $value) {
                    $get_accounting_source = LogCoinDetailRepository::get_accounting_source($value->bdc_receipt_id, $value);
                    if($get_accounting_source){
                        if ($get_accounting_source->from_type == 4) {
                            $get_accounting_source->coin = 0 - $get_accounting_source->coin;
                            $nguon_hach_toan[] = $get_accounting_source;
                        }
                    }

                }
            }
            if ($receipt->type == 'phieu_chi') {
                $get_detail = isset($receipt->logs) ? json_decode($receipt->logs) : null;
                if ($get_detail) {
                    foreach ($get_detail as $key => $value) {
                        if (str_contains($value->service_apartment_id, 'tien_thua_')) {
                            $service_apartment_id = explode('tien_thua_', $value->service_apartment_id)[1];

                            $get_accounting_source = LogCoinDetailRepository::get_accounting_source_service_apartment_id_by_payment_slip($receipt->id, $service_apartment_id);
                            if ($get_accounting_source) {
                                $nguon_hach_toan[] = $get_accounting_source;
                            }
                        }
                    }
                    $sum_total_paid = $receipt->cost;
                }

            }
            $nguon_hach_toan_v1 = LogCoinDetailRepository::get_by_from_id_accounting($receipt->id);

            $receipt->type_payment_name = Helper::loai_danh_muc[$receipt->type_payment];


            $configPdfBill = $configRepository->findByKeyActiveFirst($building->id, $configRepository::RECEIPT_VIEW);
            if ($configPdfBill) {
                return view('receipt.' . $configPdfBill->value, compact('receipt', 'building', 'apartment', 'listService', 'serviceRepository', 'nguon_hach_toan', 'sum_total_paid', 'nguon_hach_toan_v1', 'getTienThua'));
            } else {
                return view('receipt.pdf_v5', compact('receipt', 'building', 'apartment', 'listService', 'serviceRepository', 'nguon_hach_toan', 'sum_total_paid', 'nguon_hach_toan_v1', 'getTienThua'));
            }
        }


    }

    public function indexCategory(Request $request)
    {
        return redirect()->route('admin.categories.index', ['type' => 'receipt']);
    }

    public function update($id, ReceiptRequest $request)
    {
        $_receipt = $this->model->findReceiptById($id);
        if (!$_receipt) {
            return redirect()->route('admin.v2.receipt.index')->with('warning', "Không tìm thấy dữ liệu.");
        }
        $cycle = Carbon::parse($_receipt->create_date)->format('Ym');
        $action = Helper::getAction();
        if ($action) {
            $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id, $cycle, $action);
            if ($check_lock_cycle) {
                return redirect()->route('admin.v2.receipt.index')->with('warning', "Kỳ $cycle đã được khóa.");
            }
        }
        $cycle = Carbon::parse($request->create_date)->format('Ym');
        $action = Helper::getAction();
        if ($action) {
            $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id, $cycle, $action);
            if ($check_lock_cycle) {
                return redirect()->route('admin.v2.receipt.index')->with('warning', "Kỳ $cycle đã được khóa.");
            }
        }
        if (Carbon::parse($request->create_date) > Carbon::parse($_receipt->created_at)) {
            return redirect()->route('admin.v2.receipt.index')->with('warning', "Ngày hạch toán không được lớn hơn ngày lập phiếu");
        }
        $this->model->updateReceipt($id, $request);
        return redirect()->route('admin.v2.receipt.index')->with('success', 'Sửa phiếu thu thành công.');
    }
}

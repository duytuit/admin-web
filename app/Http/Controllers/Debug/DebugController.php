<?php

namespace App\Http\Controllers\Debug;

use App\Commons\Api;
use App\Commons\clientApi;
use App\Commons\Helper;
use App\Models\BdcProgressives\Progressives;
use App\Models\LockCycleName\LockCycleName;
use App\Models\Permissions\GroupsPermissions;
use App\Util\Debug\Log;
use App\Commons\Util\Redis as UtilRedis;
use App\Exceptions\QueueRedis;
use App\Helpers\dBug;
use App\Http\Controllers\Controller;
use App\Jobs\PushDebit;
use App\Models\Apartments\Apartments;
use App\Models\Apartments\V2\UserApartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcBills\Bills;
use App\Models\BdcCoin\Coin;
use App\Models\BdcDebitDetail\DebitDetail as BdcDebitDetailDebitDetail;
use App\Models\BdcDebitLogs\DebitLogs;
use App\Models\BdcElectricMeter\ElectricMeter;
use App\Models\BdcReceiptLogs\ReceiptLogs;
use App\Models\BdcReceipts\Receipts;
use App\Models\BdcV2LogCoinDetail\LogCoinDetail;
use App\Models\BdcV2PaymentDetail\PaymentDetail;
use App\Models\Configs\Configs;
use App\Models\Customers\Customers;
use App\Models\PublicUser\Users;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use App\Models\PublicUser\UserInfo;
use App\Models\Service\Service;
use App\Repositories\BdcCoin\BdcCoinRepository;
use App\Traits\ApiResponse;
use App\Services\AppConfig;
use App\Services\SendSMSSoap;
use App\Services\FCM\SendNotifyFCMService;
use Carbon\Carbon;
use FCM;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Models\BdcV2UserApartment\UserApartment;
use App\Models\Building\Building;
use App\Models\Building\V2\Company;
use App\Models\BuildingHandbook\BuildingHandbook;
use App\Models\Campain;
use App\Models\CampainDetail;
use App\Models\Category;
use App\Models\CronJobManager\CronJobManager;
use App\Models\HistoryTransactionAccounting\HistoryTransactionAccounting;
use App\Models\Network\SocialPost;
use App\Models\Payment\PaymentSuccess;
use App\Models\Permissions\GroupPermissions;
use App\Models\Posts\Posts;
use App\Models\PublicUser\UserPermission;
use App\Models\PublicUser\V2\TokenUser;
use App\Models\PublicUser\V2\User;
use App\Models\PublicUser\V2\UserInfo as V2UserInfo;
use App\Models\Service\ServicePriceDefault;
use App\Models\System\Config as SystemConfig;
use App\Models\UserRequest\UserRequest;
use App\Models\VehicleCategory\VehicleCategory;
use App\Models\Vehicles\Vehicles;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcBills\V2\BillRepository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository as V2DebitDetailRepository;
use App\Repositories\BdcReceipts\V2\ReceiptRepository;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\MailTemplates\MailTemplateRepository;
use App\Repositories\SettingSendMails\SettingSendMailRepository;
use App\Services\FCM\V2\SendNotifyFCMService as V2SendNotifyFCMService;
use App\Services\RedisCommanService;
use App\Services\ServiceSendMailV2;
use App\Util\Redis as AppUtilRedis;
use BdcCoin;
use BdcV2DebitDetail;
use BdcV2LogCoinDetail;
use CURLFile;
use DateTime;
use Exception;
use GuzzleHttp\Pool as GuzzleHttpPool;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route as FacadesRoute;
use Maatwebsite\Excel\Facades\Excel;

use function GuzzleHttp\json_encode;

class DebugController extends Controller
{
    use ApiResponse;

    private $model;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function setAppIdOnline(Request $request)
    {
        AppConfig::setAppIdOn($request->app_id);
        return $request->app_id;
    }

    public function setAppIdForDomain(Request $request)
    {
        AppConfig::setAppIdForDomain($request->app_id, $request->domain);
        return AppConfig::getAppIdOfDomain($request->getHost());
        // return AppConfig::getAppIdOfDomain($request->getHost());
    }

    public function clearDebit(Request $request)
    {
        dd(6666);
    }

    public function clearKeyRedis(Request $request)
    {

    }

    public function sendSOAP(Request $request)
    {
        return SendSMSSoap::setItemForQueue([
            'content' => $request->get('content'),
            'target' => $request->get('phone')
        ]);
    }

    public function removelogcoin(Request $request)
    {
        $pass = $request->pass;
        if ($pass != 123) {
            dd('thất bại');
        }
        $get_log_coin = LogCoinDetail::whereIn('from_type', [1, 4])->where('note', 'not like', 'v1->v2%')->get();
        $log_coin_value = null;
        foreach ($get_log_coin as $key => $value) {
            if ($value->from_type == 1) {
                $receipt = Receipts::withTrashed()->find($value->from_id);
                if ($receipt->deleted_at != null) {
                    echo json_encode($value);
                }
            }
            if ($value->from_type == 4) {
                $receipt_1 = Receipts::withTrashed()->find($value->note);
                if ($receipt_1->deleted_at != null) {
                    echo json_encode($value);
                }
            }
        }
    }

    public function test_time_out(Request $request)
    {

        $time = $request->get("time");
//         Log::info("tandc", "log test_time_out");
        // current time
        set_time_limit($time < 60 ? 60 : $time + 5);

        echo 'begin: ' . date('h:i:s') . "</br>";

        // sleep for 10 seconds
        sleep($time);

        // wake up !
        echo 'end: ' . date('h:i:s') . "\n";
    }

    public function checkCampain(Request $request)
    {
        if (empty($request->type)) {
            dd('chưa chuyền param query: type');
        }
        $type = $request->type;
        $list_campain = Campain::where(function ($q) use ($type) {
            $q->where('status->' . $type, 0);
            $q->where('total->' . $type, '<>', 0);
        })
            ->where('send', 0)
            ->orderBy('sort', 'asc')->get();
        foreach ($list_campain as $key => $value) {
            echo json_encode($value) . "</br>";
        }
        dd($list_campain->count());
    }

    public function checkEmailExist(Request $request, ConfigRepository $config, BillRepository $billRepository, ReceiptRepository $receiptRepository)
    {
        dd((50 < 120&& 120 < 90)? 50 : 100);
    }

    public function changeMeter(Request $request)
    {
        try {
           $cycle_name = $request->month_create;
          $electric_meter = ElectricMeter::where(['bdc_building_id'=>$request->bdc_building_id,'bdc_apartment_id'=>$request->bdc_apartment_id,'month_create'=> $cycle_name,'type'=>$request->type,'type_action'=>$request->type_action])->first();
          //if(!$electric_meter){
              ElectricMeter::create([
                  'bdc_building_id'=>$request->bdc_building_id,
                  'bdc_apartment_id'=>$request->bdc_apartment_id,
                  'month_create'=>$cycle_name,
                  'type'=>$request->type,
                  'before_number'=>$request->before_number,
                  'after_number'=>$request->after_number,
                  'type_action'=>$request->type_action,
                  'status'=>0,
                  'date_update'=>Carbon::parse($request->date_update)
              ]);
              dd('thêm thành công');
//          }else{
//              dd('đã tồn tại');
//          }
        }catch (Exception $e){
            dd($e);
        }

    }

    public function removeBillWithDebitNotExit(Request $request)
    {
        if (empty($request->building_id)) {
            dd('chưa chuyền param query: building_id');
        }
        $building_id = $request->building_id;
        $rs = Bills::where('bdc_building_id', $building_id)->whereNotExists(
            function ($query) {
                $query->from('bdc_v2_debit_detail')
                    ->whereRaw('bdc_v2_debit_detail.bdc_bill_id = bdc_bills.id')
                    ->whereNull('bdc_v2_debit_detail.deleted_at')
                    ->select('bdc_v2_debit_detail.id');
            })->delete();
        dd($rs);
    }

    public function delCoin(Request $request)
    {
        if (empty($request->logcoin)) {
            dd('chưa chuyền param query: logcoin');
        }

        $pass = $request->pass;
        if ($pass != 123) {
            dd('thất bại');
        }

        $logcoin = $request->logcoin;

        $log_coin = LogCoinDetail::find($logcoin);

        $get_coin = Coin::where([
            'bdc_apartment_id' => $log_coin->bdc_apartment_id,
            'bdc_apartment_service_price_id' => $log_coin->bdc_apartment_service_price_id,
        ])->first();
        $cost_coin = 0;
        if ($get_coin) {
            $cost_coin = $get_coin->coin - $log_coin->coin;
            BdcCoinRepository::updateCoin($log_coin->bdc_building_id, $log_coin->bdc_apartment_id, $log_coin->bdc_apartment_service_price_id, $cost_coin);
        }
        if ($cost_coin >= 0) {
            LogCoinDetail::find($logcoin)->delete();
            echo json_encode('thành công.') . "\n";
        } else {
            echo json_encode('thất bại.') . "\n";
        }

    }

    public function sub_coin(Request $request)
    {
        if (empty($request->bdc_building_id)) {
            dd('chưa chuyền param query: bdc_building_id');
        }
        $bdc_building_id = $request->bdc_building_id;
        if (empty($request->bdc_apartment_service_price_id)) {
            dd('chưa chuyền param query: bdc_apartment_service_price_id');
        }
        $bdc_service_id = $request->bdc_apartment_service_price_id;
        if ($request->coin == null) {
            dd('chưa chuyền param query: coin');
        }
        $pass = $request->pass;
        if ($pass != 123) {
            dd('thất bại');
        }
        $coin = $request->coin;

        $rs = DB::table('bdc_coin')->where(['bdc_building_id' => $bdc_building_id, 'bdc_apartment_service_price_id' => $bdc_service_id])->first();
        if (!$rs) {
            $apart_service = ApartmentServicePrice::find($bdc_service_id);
            if ($apart_service) {
                DB::table('bdc_coin')->insert([
                    'bdc_building_id' => $bdc_building_id,
                    'bdc_apartment_id' => $apart_service->bdc_apartment_id,
                    'bdc_apartment_service_price_id' => $bdc_service_id,
                    'coin' => $coin,
                ]);
            }
        } else {
            $rs_2 = DB::table('bdc_coin')->where(['bdc_building_id' => $bdc_building_id, 'bdc_apartment_service_price_id' => $bdc_service_id])->update(['coin' => $coin]);
        }
        BdcCoinRepository::clearCache($rs->bdc_apartment_id, $bdc_service_id);
        dd($rs);
    }

    public function checkInfoCampainDetail(Request $request)
    {
        if (empty($request->contact)) {
            dd('chưa chuyền param query: contact');
        }
        $contact = $request->contact;

        $failApp = CampainDetail::where('contact', 'like', '%' . $contact . '%')->orderBy('campain_id', 'desc')->get();
        dd($failApp);
        if ($failApp) {
            foreach ($failApp as $key => $value) {
                echo json_encode($value) . "</br>";
            }

        }
    }

    function updateApartmentId(Request $request)
    {
        if (empty($request->old_id)) {
            dd('chưa chuyền param query: old_id');
        }
        if (empty($request->new_id)) {
            dd('chưa chuyền param query: new_id');
        }
        Bills::where(['bdc_apartment_id' => $request->old_id])->update(['bdc_apartment_id' => $request->new_id]);
        BdcDebitDetailDebitDetail::where(['bdc_apartment_id' => $request->old_id])->update(['bdc_apartment_id' => $request->new_id]);
        DebitDetail::where(['bdc_apartment_id' => $request->old_id])->update(['bdc_apartment_id' => $request->new_id]);
        Receipts::where(['bdc_apartment_id' => $request->old_id])->update(['bdc_apartment_id' => $request->new_id]);
        ApartmentServicePrice::where(['bdc_apartment_id' => $request->old_id])->update(['bdc_apartment_id' => $request->new_id]);
    }

    function isValidDate($date, $format = 'Y-m-d')
    {
        return $date == date($format, strtotime($date));
    }

    public function test(Request $request, Users $users)
    {
        $coin_apartment = DebitDetail::select(DB::raw('sum(sumery+discount) as tong_tien,sum(discount) as chiet_khau,sum(sumery) as thanh_tien,sum(paid) as thanh_toan'))
            ->whereHas('bill', function ($query) {
                $query->where('status', '>=', -2);
            })
            ->where('bdc_apartment_id', 16203)->groupBy("bdc_apartment_id")->first();
    }

    public function testPush(Request $request)
    {
        $token = $request->get('token');
        if (!$token) {
            echo 'token null';
            die;
        }
        $data_payload = [];
        $data_payload['message'] = "test";
        $data_payload['title'] = "test push";
        $noticustom = $request->message;
        $rs = V2SendNotifyFCMService::testPushV2($token, $noticustom, $data_payload, $request->get('type_config'));
        //dBug::trackingPhpErrorV2($rs);
        dd($rs);
        echo "this test push || " . $token;
        die;
    }

    public function Maintain(Request $request)
    {
        Helper::setMaintenance($request->maintain);
        echo $request->maintain;
    }

    public function getMaintain(Request $request)
    {
        $maintain = Helper::getMaintenance();
        $cookie = @$_COOKIE['bdc_test'];
        if ($maintain == 'false' || $cookie) {
            return redirect('/admin');
        } else {
            return view('maintenance_page');
        }

    }

    public function clearKeysRedis(Request $request)
    {
        $allKey = Redis::connection('cache')->keys('*' . $request->key . '*');
        if($allKey){
            $result = Redis::connection('cache')->del($allKey);
        }
        $allKey = Redis::connection('default')->keys('*' . $request->key . '*');
        if($allKey){
            $result = Redis::connection('default')->del($allKey);
        }
    }

    public function getKeysRedis(Request $request)
    {
        $allKey = Redis::keys('*' . $request->key . '*');
        dd($allKey[0]);
    }

    public function getEntireKeysRedis(Request $request)
    {
        $allKey = Redis::keys($request->key);
        dd($allKey);
    }

    public function getAllKeysRedis(Request $request)
    {
        $allKey = Redis::keys('*');
        dd($allKey);
    }

    public function check_view_receipt(Request $request)
    {

        $data['meta_title'] = 'reload pdf phiếu thu';

        $receipt = DB::table('bdc_receipts')->where('receipt_code', $request->code)->first();

        if (!$receipt) {
            return response('Không tìm thấy phiếu thu hoặc phiếu thu đã bị xóa.');
        }

        $get_log = isset($receipt->logs) ? json_decode($receipt->logs) : null;
        if ($get_log) {
            foreach ($get_log as $key => $value) {
                $service = Service::get_detail_bdc_service_by_bdc_service_id(@$value->service_id);
                echo "Hóa đơn: " . $value->bill_code . " Dịch vụ: " . @$service->name . 'nộp: ' . @$value->paid . '<br>';
            }
        }

        $get_bill = isset($receipt->bdc_bill_id) ? unserialize($receipt->bdc_bill_id) : null;
        echo "======================================================================================";
        $get_bill = array_unique($get_bill);
        if ($get_bill) {

            echo "Số tiền nộp: " . number_format($receipt->cost) . '<br>';
            for ($i = 0; $i < count($get_bill); $i++) {
                $log_receipt = ReceiptLogs::where(['bill_code' => $get_bill[$i], 'message' => 'Tạo version công nợ thành công'])->where('created_at', 'like', '%' . Carbon::parse($receipt->created_at)->format('Y-m-d H:i') . '%')->orderBy('created_at')->get();
                foreach ($log_receipt as $key => $value) {
                    $service = Service::get_detail_bdc_service_by_bdc_service_id($value->bdc_service_id);

                    $data = json_decode($value->input);
                    echo "Hóa đơn: " . $get_bill[$i] . " Dịch vụ: " . $service->name . 'nộp: ' . $data->paid . '<br>';
                }
            }
        }
    }

    public function get_cookie(Request $request)
    {
        if (empty($request->type)) {
            dd('chưa chuyền param query: type');
        }
        $type = $request->type;
        $count = 0;
        $dfgdfg = Campain::whereNotIn('id', [17979, 18004, 18030, 18048])->where(function ($q) use ($type) {
            $q->where('status->' . $type, 0);
            $q->where('total->' . $type, '<>', 0);
        })
            ->where('send', 0)
            ->orderBy('sort', 'asc')->get();
        foreach ($dfgdfg as $key => $value) {
            $count++;
            $rs_debit = Campain::updateStatus($value->id, $type);
            echo $rs_debit . "</br>";
        }
        dd($count);
    }

    public function set_cookie(Request $request)
    {
        $sdfgg = str_replace("|", '', "|27476");
        dd($sdfgg);
    }

    public function install_command(Request $request)
    {
        $time = $request->get("time", false);
        if (empty($request->command)) {
            dd('chưa chuyền param query: command');
        }
        $command = $request->command;
        if ($time) $command .= " " . $time;
        $dfg = Artisan::call($command);
        dd($dfg);
    }

    public function test_1(Request $request)
    {
        $route = FacadesRoute::current()->action['permission'];
        dd($route);
    }

    public function test_2(Request $request)
    {
        $route = FacadesRoute::current()->action['permission'];
        dd($route);
    }

    public function install_command_migrate(Request $request)
    {
        $dfg = Artisan::call('migrate',
            array(
                '--path' => 'database/migrations',
                '--force' => true));
        dd($dfg);
    }

    public function run_command(Request $request)
    {
        $command = $request->command;
        if ($command == 'true') {
            QueueRedis::setFlagQueue(1);
        } else {
            QueueRedis::forgetFlagQueue();
        }
        dd('thành công.');
    }

    public function addcoin(Request $request)
    {
        if (empty($request->bdc_building_id)) {
            dd('chưa chuyền param query: bdc_building_id');
        }
        $bdc_building_id = $request->bdc_building_id;
        if (empty($request->bdc_apartment_id)) {
            dd('chưa chuyền param query: bdc_apartment_id');
        }
        $bdc_apartment_id = $request->bdc_apartment_id;
        if (empty($request->bdc_service_id)) {
            dd('chưa chuyền param query: bdc_service_id');
        }
        $bdc_service_id = $request->bdc_service_id;
        if (empty($request->coin)) {
            dd('chưa chuyền param query: coin');
        }
        if (empty($request->pass)) {
            dd('chưa chuyền param query: pass');
        }
        $pass = $request->pass;
        if ($pass != 123) {
            dd('thất bại');
        }
        // $building_list = [68]; // những toà đang chạy
        // $get_apart = Apartments::find($bdc_apartment_id);

        // if(!in_array($get_apart->building_id,$building_list)){
        //     dd('cần chọn căn hộ tòa 68');
        // }
        $coin = $request->coin;
        $_customer = CustomersRespository::findApartmentIdV2($bdc_apartment_id, 0);
        $ApartmentServiceId = DebitDetailRepository::getServiceApartment($bdc_building_id, $bdc_apartment_id, $bdc_service_id);
        $rs = BdcCoinRepository::addCoin($bdc_building_id, $bdc_apartment_id, $ApartmentServiceId, Carbon::now()->format('Ym'), @$_customer->user_info_id ?? 0, $coin, 1, 7, 0, 'v1->v2');
        echo json_encode($rs) . "\n";
    }

    public function updateCycleNamePaymentDetailByCreateDateReceipt(Request $request)
    {
        $receipt = Receipts::find($request->id);
        if (!$receipt) {
            dd('không tìm thấy mã phiếu thu');
        }
        $logCoinDetail = LogCoinDetail::where(['from_id' => $receipt->id, 'from_type' => 1])->get();
        if ($logCoinDetail->count() == 0) {
            $logCoinDetail = LogCoinDetail::where(['note' => $receipt->id, 'from_type' => 4])->get();
            if ($logCoinDetail->count() == 0) {
                $logCoinDetail = LogCoinDetail::where(['note' => $receipt->id, 'from_type' => 9])->get();
                if ($logCoinDetail->count() == 0) {
                    $logCoinDetail = LogCoinDetail::where(['from_id' => $receipt->id, 'from_type' => 6])->get();
                }
            }
        }
        $_add_queue_stat_payment = null;
        foreach ($logCoinDetail as $key_1 => $value_1) {
            $cycle_name_before = $value_1->cycle_name;
            $cycle_name_coin = Carbon::parse($receipt->create_date)->format('Ym');
            $value_1->cycle_name = $cycle_name_coin;
            // $value_1->note = $receipt->description;
            $value_1->save();
            $_add_queue_stat_payment[] = [
                "apartmentId" => $value_1->bdc_apartment_id,
                "service_price_id" => $value_1->bdc_apartment_service_price_id,
                "cycle_name" => $cycle_name_before,
            ];
            $_add_queue_stat_payment[] = [
                "apartmentId" => $value_1->bdc_apartment_id,
                "service_price_id" => $value_1->bdc_apartment_service_price_id,
                "cycle_name" => $cycle_name_coin,
            ];
        }

        $paymentDetail = PaymentDetail::where('bdc_receipt_id', $receipt->id)->get();
        foreach ($paymentDetail as $key_2 => $value_2) {
            $cycle_name_before = $value_2->cycle_name;
            $cycle_name_payment = Carbon::parse($receipt->create_date)->format('Ym');
            $value_2->cycle_name = $cycle_name_payment;
            $value_2->save();
            $_add_queue_stat_payment[] = [
                "apartmentId" => $value_2->bdc_apartment_id,
                "service_price_id" => $value_2->bdc_apartment_service_price_id,
                "cycle_name" => $cycle_name_before,
            ];
            $_add_queue_stat_payment[] = [
                "apartmentId" => $value_2->bdc_apartment_id,
                "service_price_id" => $value_2->bdc_apartment_service_price_id,
                "cycle_name" => $cycle_name_payment,
            ];
        }
        if ($_add_queue_stat_payment && count($_add_queue_stat_payment) > 0) {
            foreach ($_add_queue_stat_payment as $key => $value) {
                QueueRedis::setItemForQueue('add_queue_stat_payment_', $value);
            }
        }

    }

    public function updatePaidDebitCycleNameApartment(Request $request)
    {
        $apartmentid = $request->apartmentid;
        $serviceid = $request->serviceid;
        $cyclename = $request->cyclename;
        $rs = DebitDetailRepository::updatePaidByCycleNameFromReceipt($apartmentid, $serviceid, $cyclename);
        dd($rs);
    }

    public function delete_log_debit(Request $request)
    {
        $sub_month = Carbon::now()->subMonth(1)->format('Y-m-d');
        $check = DB::table('bdc_debit_logs')->where('created_at', '<', $sub_month)->delete();
        $check_1 = DB::table('cron_job_logs')->where('created_at', '<', $sub_month)->delete();
        dd($check . '|' . $check_1);
    }

    public function changeCycleNameDebitDetail(Request $request)
    {
        // try {
        //     DB::beginTransaction();
        //     $apartment_service_price_id = $request->apartment_service_price_id;
        //     $cycle_name = $request->cycle_name;
        //     $cycle_name_new = $request->cycle_name_new;
        //     $debit_new_cycle_name = DebitDetail::withTrashed()->where(['bdc_apartment_service_price_id' => $apartment_service_price_id, 'cycle_name' => $cycle_name_new, 'bdc_bill_id' => 0])->forceDelete();
        //     $debit = DebitDetail::where(['bdc_apartment_service_price_id' => $apartment_service_price_id, 'cycle_name' => $cycle_name])->first();
        //     Bills::find($debit->bdc_bill_id)->update(['cycle_name'=>$cycle_name_new]);
        //     $debit->cycle_name = $cycle_name_new;
        //     $debit->save(); 
        //     DB::commit();
        //     dd('thành công');
        // } catch (Exception $e) {
        //     DB::rollBack();
        //     dd($e->getMessage());

        // }

    }

    public function insertIntoBdcDebitV2(Request $request)
    {
        // if(empty($request->buildingId))
        // {
        //     dd('chưa chuyền param query: buildingId');
        // }
        // $building_list = [80];// những toà đã chạy
        // $buildingId = $request->buildingId;
        // if(in_array($buildingId,$building_list)){
        //     dd('đã chạy toà '.$buildingId);
        // }
        // $apart = "and bdc_debit_detail.bdc_apartment_id in (14255)";
        // $sql = "select * from (SELECT  bdc_apartment_service_price_id, count(bdc_apartment_service_price_id) as test,cycle_name,bdc_price_type_id,id
        // FROM    bdc_debit_detail
        // WHERE  EXISTS (SELECT id 
        // FROM bdc_bills 
        // WHERE bdc_debit_detail.bdc_bill_id = bdc_bills.id and bdc_bills.deleted_at is null) and bdc_debit_detail.bdc_building_id = $buildingId and sumery > 0 and version = 0 and bdc_price_type_id <> 1 and deleted_at is null group by bdc_apartment_id,bdc_apartment_service_price_id,cycle_name) as tb where test > 1";
        // $rs = DB::select(DB::raw($sql));
        // $sql_1 = "select * from (SELECT  bdc_apartment_service_price_id, count(bdc_apartment_service_price_id) as test,cycle_name,bdc_price_type_id,id
        // FROM    bdc_debit_detail
        // WHERE EXISTS (SELECT id 
        // FROM bdc_bills 
        // WHERE bdc_debit_detail.bdc_bill_id = bdc_bills.id and bdc_bills.deleted_at is null) and bdc_debit_detail.bdc_building_id = $buildingId and sumery > 0 and version = 0 and bdc_price_type_id <> 1 and deleted_at is null group by bdc_apartment_id,bdc_apartment_service_price_id,cycle_name) as tb where test = 1";
        // $rs_2 = DB::select(DB::raw($sql_1));
        // $result = array_merge($rs, $rs_2);
        // // $sql_3 ="SELECT * FROM dev_dbdc.bdc_debit_detail where id=279946";
        // // $result = DB::select(DB::raw($sql_3));
        // if(count($result) == 0)
        // {
        //     echo 'không tìm thấy dữ liệu';
        // }
        // foreach ($result as $key => $value) {
        //    //DebitDetail::withTrashed()->where('bdc_apartment_service_price_id',$value->bdc_apartment_service_price_id)->forceDelete();
        //    QueueRedis::setItemForQueue('add_queue_insert_debit_v3',$value);
        // }
        // dd('thành công.'.$buildingId);
    }

    public function insertIntoBdcDebitFirstCycleNameUse(Request $request)
    {
        // if(empty($request->buildingId))
        // {
        //     dd('chưa chuyền param query: buildingId');
        // }
        // $buildingId = $request->buildingId;
        // set_time_limit(0);
        // $sql ="SELECT 
        // tb.cycle_name,tb.bdc_bill_id,tb.bdc_apartment_service_price_id,tb.bdc_building_id,tb.sumery,tb.bdc_apartment_id
        //  FROM
        //  bdc_debit_detail AS tb
        //      inner JOIN
        //  (SELECT 
        //      MAX(version) as version,bdc_bill_id , bdc_apartment_service_price_id , bdc_apartment_id
        //  FROM
        //      bdc_debit_detail
        //  WHERE
        //          bdc_building_id = $buildingId
        //          AND deleted_at IS NULL
        //  GROUP BY bdc_bill_id , bdc_apartment_service_price_id , bdc_apartment_id) AS tb1 ON 
        //          tb1.bdc_apartment_service_price_id = tb.bdc_apartment_service_price_id
        //      AND tb1.bdc_apartment_id = tb.bdc_apartment_id
        //      AND tb1.bdc_bill_id = tb.bdc_bill_id
        //      AND tb1.version = tb.version
        //      AND tb.deleted_at IS NULL AND tb.sumery < 0 AND tb.version > 0 order by tb.bdc_apartment_id,tb.cycle_name";
        // $result = DB::select(DB::raw($sql));
        // if(count($result) == 0)
        // {
        //     echo 'không tìm thấy dữ liệu';
        // }
        // $count =0;
        // foreach ($result as $key => $value) {
        //     $value->sumery = abs($value->sumery);
        //     $count+=$value->sumery;
        //     QueueRedis::setItemForQueue('add_queue_insert_debit_v4_1',$value);
        //     echo $count ."</br>";
        // }

        // dd('thành công.'.$count);
    }

    public function BackInsertIntoBdcDebitFirstCycleName(Request $request)
    {

        // if(empty($request->buildingId))
        // {
        //     dd('chưa chuyền param query: buildingId');
        // }
        // $buildingId = $request->buildingId;
        // set_time_limit(0);
        //  // ->whereIn('bdc_apartment_id',[11085,11454,11455,11459,11480,11492,11496,11545,11680,11872,12294])
        // $result = DebitDetail::where('bdc_building_id',$buildingId)->where('bdc_bill_id','>',0)->where('discount','<>',0)->get();
        // if(count($result) == 0)
        // {
        //     echo 'không tìm thấy dữ liệu';
        // }
        // foreach ($result as $key => $value) {
        //     echo "kết quả --->$value->id \n";
        //     $value->sumery =  $value->discount_note;
        //     $value->discount_type = 0;
        //     $value->discount = 0;
        //     $value->discount_note = 'back của v1';
        //     $value->save();
        // }
        // dd('thành công.'.$buildingId);
    }

    public function insertIntoBdcDebitV2ByPriceOne(Request $request)
    {
        // if(empty($request->buildingIda))
        // {
        //     dd('chưa chuyền param query: buildingId');
        // }
        // $building_list = [80];// những toà đã chạy
        // $buildingId = $request->buildingId;
        // if(in_array($buildingId,$building_list)){
        //     dd('đã chạy toà '.$buildingId);
        // }
        // // and bdc_bill_id=177317
        // // $apart = "and bdc_debit_detail.bdc_apartment_id in (14255)";
        // $sql = "SELECT *
        // FROM  bdc_debit_detail
        // WHERE EXISTS (SELECT id from(SELECT * from (SELECT  bdc_apartment_service_price_id, count(bdc_apartment_service_price_id) as test,cycle_name,bdc_price_type_id,id
        // FROM  bdc_debit_detail
        // WHERE EXISTS (SELECT id 
        // FROM bdc_bills 
        // WHERE bdc_debit_detail.bdc_bill_id = bdc_bills.id and bdc_bills.deleted_at is null) and bdc_debit_detail.bdc_building_id = $buildingId and sumery > 0 and version=0 and bdc_price_type_id=1 and sumery > 0 and deleted_at is null group by bdc_apartment_id,bdc_apartment_service_price_id,cycle_name) as tb where test = 1) as tb2 where tb2.id = bdc_debit_detail.id ) and bdc_debit_detail.bdc_building_id = $buildingId and version=0 and bdc_price_type_id=1 and sumery > 0 and deleted_at is null group by bdc_apartment_id,bdc_apartment_service_price_id,cycle_name";
        // $rs = DB::select(DB::raw($sql));
        // $sql_1 = "SELECT *
        // FROM  bdc_debit_detail
        // WHERE EXISTS (SELECT id from(SELECT * from (SELECT  bdc_apartment_service_price_id, count(bdc_apartment_service_price_id) as test,cycle_name,bdc_price_type_id,id
        // FROM  bdc_debit_detail
        // WHERE EXISTS (SELECT id 
        // FROM bdc_bills 
        // WHERE bdc_debit_detail.bdc_bill_id = bdc_bills.id and bdc_bills.deleted_at is null) and bdc_debit_detail.bdc_building_id = $buildingId and sumery > 0 and version=0 and bdc_price_type_id=1 and sumery > 0 and deleted_at is null group by bdc_apartment_id,bdc_apartment_service_price_id,cycle_name) as tb where test > 1) as tb2 where tb2.id = bdc_debit_detail.id ) and bdc_debit_detail.bdc_building_id = $buildingId and version=0 and bdc_price_type_id=1 and sumery > 0 and deleted_at is null group by bdc_apartment_id,bdc_apartment_service_price_id,cycle_name";
        // $rs_1 = DB::select(DB::raw($sql_1));
        // $result = array_merge($rs, $rs_1);

        // $sql_3 ="SELECT * FROM bdc_debit_detail where id=593606";
        // $result = DB::select(DB::raw($sql_3));

        // if(count($result) == 0)
        // {
        //     echo 'không tìm thấy dữ liệu';
        // }
        // foreach ($result as $key => $value) {
        //     //DebitDetail::withTrashed()->where('bdc_apartment_service_price_id',$value->bdc_apartment_service_price_id)->forceDelete();
        //     QueueRedis::setItemForQueue('add_queue_insert_debit_v3_1',$value);
        // }
        // dd('thành công.'.$buildingId);
    }

    public function addExcessCash(Request $request)
    {
        // if(empty($request->buildingId))
        // {
        //     dd('chưa chuyền param query: buildingId');
        // }
        // $buildingId = $request->buildingId;
        // $building_list = [80];// những toà đã chạy
        // if(!in_array($buildingId,$building_list)){
        //     dd('đã chạy toà '.$buildingId);
        // }
        // $sql ="SELECT * from ( SELECT *, (`dau_ky` + `ps_trongky` - `thanh_toan`) AS `du_no_cuoi_ky` FROM (
        //     SELECT bdc_apartment_id, `customer_name`, `name`, `building_place_id`, bdc_building_id, 
        //         COALESCE(SUM(`dau_ky`), 0) AS `dau_ky`, COALESCE(SUM(`thanh_toan`), 0) AS `thanh_toan`, COALESCE(SUM(`ps_trongky`), 0) AS `ps_trongky` FROM (
        //         SELECT `tbl_main`.`bdc_apartment_id`, `bdc_apartments`.`name`, `bdc_apartments`.`building_place_id`, `tbl_main`.`bdc_building_id`, `tbl_main`.`bdc_bill_id`, `bdc_bills`.`customer_name`, (
        //             SELECT SUM(tb1.sumery) AS `ps_trongky` FROM (
        //                 SELECT `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
        //                     `bdc_debit_detail`.`bdc_bill_id`, SUM(`bdc_debit_detail`.`sumery`) AS `sumery`
        //                 FROM `bdc_debit_detail`
        //                 INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `bdc_debit_detail`.`bdc_bill_id`
        //                 WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2 
        //                     AND `bdc_debit_detail`.`deleted_at` IS NULL AND `version` = 0
        //                 GROUP BY `bdc_debit_detail`.`bdc_apartment_id`, `bdc_debit_detail`.`bdc_apartment_service_price_id`, 
        //                     `bdc_debit_detail`.`bdc_bill_id`, `bdc_debit_detail`.`sumery`
        //             ) AS tb1 WHERE tb1.`bdc_apartment_id` = `tbl_main`.`bdc_apartment_id` AND tb1.`bdc_bill_id` = `tbl_main`.`bdc_bill_id`
        //         ) as `ps_trongky`
        //         , 
        //         (
        //             0
        //         ) as `dau_ky`
        //         , 
        //         (
        //             SELECT SUM(tb1.paid) AS `thanh_toan` 
        //             FROM `bdc_debit_detail` as tb1
        //             INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tb1`.`bdc_bill_id`
        //             WHERE `tb1`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2
        //                 AND tb1.`bdc_apartment_id` = `tbl_main`.`bdc_apartment_id` 
        //                 AND tb1.`bdc_bill_id` = `tbl_main`.`bdc_bill_id`
        //                 AND `tb1`.`deleted_at` IS NULL 
        //         ) as `thanh_toan`
        //         FROM `bdc_debit_detail` AS tbl_main 
        //         INNER JOIN `bdc_apartments` ON `bdc_apartments`.`id` = `tbl_main`.`bdc_apartment_id`
        //         INNER JOIN `bdc_bills` ON `bdc_bills`.`id` = `tbl_main`.`bdc_bill_id` 
        //         WHERE `tbl_main`.`bdc_building_id` = $buildingId AND `bdc_bills`.`status` >= -2  AND `tbl_main`.`deleted_at` IS NULL 
        //         GROUP BY `tbl_main`.`bdc_apartment_id`, `tbl_main`.`bdc_building_id`, `tbl_main`.`bdc_bill_id`  
        //         ORDER BY `tbl_main`.`bdc_apartment_id` ASC
        //     ) AS tbl_fn GROUP BY bdc_apartment_id) AS tbl_m) as tb_1 where tb_1.du_no_cuoi_ky < 0";

        // $rs = DB::select(DB::raw($sql));
        // if(count($rs) == 0)
        // {
        //     dd('không tìm thấy dữ liệu');
        // }
        // dd($rs);
        // foreach ($rs as $key => $value) {
        //     $_customer = CustomersRespository::findApartmentIdV2($value->bdc_apartment_id, 0);
        //     $logcoin = LogCoinDetail::where(['bdc_building_id'=>$buildingId,'bdc_apartment_id'=>$value->bdc_apartment_id,'bdc_apartment_service_price_id'=>0,'from_id'=>0,'note'=>'v1->v2'])->first();
        //     if($logcoin){
        //        Log::info('check_insert_log_coin',json_encode($logcoin));
        //        continue;
        //     }
        //     BdcCoinRepository::addCoin($buildingId,$value->bdc_apartment_id,0,Carbon::now()->format('Ym'),$_customer->user_info_id,abs($value->du_no_cuoi_ky),1,1,0,'v1->v2');
        // }

        // dd('thành công.');
    }

    public function addExcessCashDetail(Request $request, ApartmentsRespository $apartmentsRespository, V2DebitDetailRepository $debitDetailRepository)
    {
        //     if(empty($request->buildingId))
        //     {
        //         dd('chưa chuyền param query: buildingId');
        //     }
        //     $buildingId = $request->buildingId;
        //     $building_list = [80]; // những toà đã chạy
        //     if(in_array($buildingId,$building_list)){
        //         dd('đã chạy toà '.$buildingId);
        //     }
        //     set_time_limit(0);
        //    $sql ="SELECT
        //    tb.cycle_name,tb.bdc_bill_id,tb.bdc_apartment_service_price_id,tb.bdc_building_id,tb.new_sumery,tb.bdc_apartment_id
        //     FROM
        //     bdc_debit_detail AS tb
        //         inner JOIN
        //     (SELECT
        //         MAX(version) as version,bdc_bill_id , bdc_apartment_service_price_id , bdc_apartment_id
        //     FROM
        //         bdc_debit_detail
        //     WHERE
        //             bdc_building_id = $buildingId
        //             AND deleted_at IS NULL
        //     GROUP BY bdc_bill_id , bdc_apartment_service_price_id , bdc_apartment_id) AS tb1 ON
        //             tb1.bdc_apartment_service_price_id = tb.bdc_apartment_service_price_id
        //         AND tb1.bdc_apartment_id = tb.bdc_apartment_id
        //         AND tb1.bdc_bill_id = tb.bdc_bill_id
        //         AND tb1.version = tb.version
        //         AND tb.deleted_at IS NULL and new_sumery < 0 and sumery > 0";

        //     $rs = DB::select(DB::raw($sql));
        //     if(count($rs) == 0)
        //     {
        //         dd('không tìm thấy dữ liệu');
        //     }
        //     foreach ($rs as $key => $value) {
        //         QueueRedis::setItemForQueue('add_queue_insert_debit_excess_cash_v1',$value);
        //     }

        //     dd('thành công.');
    }

    public function convertVehicleCategory(Request $request)
    {
        // if(empty($request->buildingId))
        // {
        //     dd('chưa chuyền param query: buildingId');
        // }
        // $buildingId = $request->buildingId;
        // set_time_limit(0);
        // $vehicle_categorys = VehicleCategory::where('bdc_building_id',$buildingId)->get();
        // if($vehicle_categorys->count() == 0)
        // {
        //     dd('không tìm thấy dữ liệu');
        // }
        // try {
        //     DB::beginTransaction();
        //     $sql="update bdc_debit_detail set code_receipt = bdc_service_id where bdc_building_id = $buildingId";
        //     DB::statement($sql);
        //     foreach ($vehicle_categorys as $key => $value) {
        //         $service = DB::table('bdc_services')->whereNull('deleted_at')->where('bdc_building_id',$buildingId)->where('name','like','Phí dịch vụ - '.$value->name.' -%')->first();
        //         Log::info('check_update_service',json_encode($service));
        //         if(!$service){
        //             continue;
        //         }
        //         DB::table('bdc_service_price_default')->whereNull('deleted_at')->where('bdc_building_id',$buildingId)->where('name','like','Phí dịch vụ - '.$value->name.'- %')->update(['bdc_service_id'=>$service->id]);
        //         DB::table('bdc_services')->whereNull('deleted_at')->where('bdc_building_id',$buildingId)->where('name','like','Phí dịch vụ - '.$value->name.' -%')->where('id','<>',$service->id)->update(['status'=>0]);
        //         DB::table('bdc_apartment_service_price')->whereNull('deleted_at')->where('bdc_building_id',$buildingId)->where('name','like','Phí dịch vụ - '.$value->name.' -%')->update(['bdc_service_id'=>$service->id]);
        //         $bdc_apartment_service_price = DB::table('bdc_apartment_service_price')->whereNull('deleted_at')->where('bdc_building_id',$buildingId)->where('name','like','Phí dịch vụ - '.$value->name.' -%')->pluck('id');
        //         $debit = DB::table('bdc_debit_detail')->where('bdc_building_id',$buildingId)->whereIn('bdc_apartment_service_price_id',$bdc_apartment_service_price)->first();
        //         DB::table('bdc_debit_detail')->where('bdc_building_id', $buildingId)->whereIn('bdc_apartment_service_price_id', $bdc_apartment_service_price)->update(['bdc_service_id' => $service->id, 'image' => @$debit->bdc_service_id ?? 0]);
        //         $value->bdc_service_id = $service->id;
        //         $value->save();
        //     }
        //     DB::commit();
        //     dd('thành công.');
        // } catch (Exception $e) {
        //     DB::rollBack();
        //     dd($e->getMessage().'|'.$e->getLine());
        // }

    }

    public function checkConvertVehicleCategory(Request $request)
    {
        // if(empty($request->buildingId))
        // {
        //     dd('chưa chuyền param query: buildingId');
        // }
        // $buildingId = $request->buildingId;
        // $vehicle_categorys = VehicleCategory::where('bdc_building_id',$buildingId)->get();
        // if($vehicle_categorys->count() == 0)
        // {
        //     dd('không tìm thấy dữ liệu');
        // }
        // try {
        //     foreach ($vehicle_categorys as $key => $value) {
        //         $service = DB::table('bdc_services')->where('bdc_building_id',$buildingId)->where('name','like','Phí dịch vụ - '.$value->name.' -%')->first();
        //         $service = json_encode($service);
        //         echo  "<p style='color: red' >".$service."</p>";
        //     }
        //     dd('thành công.');
        // } catch (Exception $e) {
        //     DB::rollBack();
        // }

    }

    public function checkdebit(Request $request)
    {
        if (empty($request->buildingId)) {
            dd('chưa chuyền param query: buildingId');
        }
        $buildingId = $request->buildingId;
        set_time_limit(0);
        $debit = DebitDetail::select('bdc_apartment_id')->where(['bdc_building_id' => $buildingId])->groupBy('bdc_apartment_id')->get();
        $total_v1 = 0;
        $total_v2 = 0;
        $count = 0;
        foreach ($debit as $key => $value) {
            $bdc_apartment_id = $value->bdc_apartment_id;
            $paid_v1 = BdcDebitDetailDebitDetail::where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $value->bdc_apartment_id])->sum('paid');
            $sumery_v1 = BdcDebitDetailDebitDetail::where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $value->bdc_apartment_id, 'version' => 0])->sum('sumery');
            $sumery_v2 = DebitDetail::where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $value->bdc_apartment_id])->sum('sumery');
            $paid_coin = LogCoinDetail::where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $value->bdc_apartment_id, 'from_type' => 4])->sum('coin');
            $paid_payment = PaymentDetail::where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $value->bdc_apartment_id])->sum('paid');
            $paid_v2 = $paid_payment - $paid_coin;
            $total_v1 += $paid_v1;
            $total_v2 += $paid_v2;
            $lech = $paid_v1 - $paid_v2;
            if ($paid_v1 != $paid_v2) {
                $count++;
                echo "<p style='color: red' >" . "$count - Căn hộ: $bdc_apartment_id ------v1 tổng $sumery_v1 thanh toán $paid_v1 --------v2 tổng $sumery_v2 thanh toán $paid_v2 =>lệch : $lech" . "</p>";
            }
        }
        $lech_total = number_format($total_v1 - $total_v2);
        echo "<p style='color: red' >" . "------v1 tổng $total_v1 --------v2 tổng $total_v2 => lệch : $lech_total" . "</p>";
    }

    public function sendNotifyV2(Request $request, V2SendNotifyFCMService $sendNotifyFCMService)
    {
        if (empty($request->user_id)) {
            dd('chưa chuyền param query: user_id');
        }
        $user_id = $request->user_id;
        //$rs = $sendNotifyFCMService->pushNotify(null,$user_id);
        //dd($rs);
    }

    public static function content_type($filename)
    {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );
        return isset($mime_types[$filename]) === true ? $mime_types[$filename] : false;
    }

    public function SendMailCustom(Request $request, SettingSendMailRepository $sendMailRepository, MailTemplateRepository $mailTemplateRepository)
    {
        if (empty($request->type)) {
            dd('chưa chuyền param query: type');
        }
        $type = $request->type;
        $list_campain = Campain::where(function ($q) use ($type) {
            $q->where('status->' . $type, 0);
            $q->where('total->' . $type, '<>', 0);
        })
            ->where('send', 0)
            ->orderBy('sort', 'asc')->get();
        foreach ($list_campain as $key => $value) {
            ServiceSendMailV2::sendMail($sendMailRepository, $mailTemplateRepository, $value);
        }
    }

    public function SendMailCustomV2(Request $request, SettingSendMailRepository $sendMailRepository, MailTemplateRepository $mailTemplateRepository)
    {
        ServiceSendMailV2::sendMail_v2($sendMailRepository, $mailTemplateRepository);
    }

    public function SendNotifyCustomV2(Request $request, SettingSendMailRepository $sendMailRepository, MailTemplateRepository $mailTemplateRepository)
    {
        SendNotifyFCMService::sendV2();
    }
    public function SendNotifyCustomV3(Request $request, SettingSendMailRepository $sendMailRepository, MailTemplateRepository $mailTemplateRepository)
    {
        SendNotifyFCMService::sendV3();
    }

    public function SendNotifyCustomV4(Request $request, SettingSendMailRepository $sendMailRepository, MailTemplateRepository $mailTemplateRepository)
    {
        SendNotifyFCMService::sendV4();
    }

    public function SendMailCustomNoCampain(Request $request, SettingSendMailRepository $sendMailRepository, MailTemplateRepository $mailTemplateRepository)
    {
        if (empty($request->type)) {
            dd('chưa chuyền param query: type');
        }
        $type = $request->type;
        $list_campain = Campain::where(function ($q) use ($type) {
            $q->where('status->' . $type, 0);
            $q->where('total->' . $type, '<>', 0);
        })
            ->where('send', 0)
            ->orderBy('sort', 'asc')->get();
        foreach ($list_campain as $key => $value) {
            ServiceSendMailV2::sendMail($sendMailRepository, $mailTemplateRepository, $value);
        }
    }

    public function testSendMail(Request $request)
    {
        $url = 'https://api.elasticemail.com/v2/email/send';
        $attachFile = '["https://bdcadmin.dxmb.vn/media/11685/file/B%E1%BA%A3n%20Tin%20D%E1%BB%8Bch%20V%E1%BB%A5%20OPB%20-%2030-05%20t%E1%BB%9Bi%2005-06.pdf","https://bdcadmin.dxmb.vn/media/11685/file/B%E1%BA%A3n%20Tin%20D%E1%BB%8Bch%20V%E1%BB%A5%20OPB%20-%2030-05%20t%E1%BB%9Bi%2005-06.pdf"]';

        $files = null;
        if ($attachFile != null) {
            $attachFileDecode = json_decode($attachFile);
            foreach ($attachFileDecode as $key => $_attachFile) {
                $arr = pathinfo($_attachFile);
                $type = self::content_type($arr['extension']);
                $files['file_' . ($key + 1)] = new CURLFile($_attachFile, $type, $arr['basename']);
            }
        }
        try {
            $headerParams = [
                "X-ElasticEmail-ApiKey" => "B18E5145AF4FB7A607E3A2F725EF678EDB44127CED3AFF2AB2260125CAC2BE05E2BE0B37056DAE6643BC747E09975860"
            ];
            $options = [
                'multipart' => [
                    [
                        'name' => 'dfgfdgdfg',
                        'contents' => fopen('https://bdcadmin.dxmb.vn/media/11685/file/B%E1%BA%A3n%20Tin%20D%E1%BB%8Bch%20V%E1%BB%A5%20OPB%20-%2030-05%20t%E1%BB%9Bi%2005-06.pdf', 'r'),
                        'filename' => '5db394d55f299c77c538.pdf',
                    ],
                    [
                        'name' => 'from',
                        'contents' => 'no.reply@mail.dxmb.vn',
                    ],
                    [
                        'name' => 'apikey',
                        'contents' => 'B18E5145AF4FB7A607E3A2F725EF678EDB44127CED3AFF2AB2260125CAC2BE05E2BE0B37056DAE6643BC747E09975860',
                    ],
                    [
                        'name' => 'subject',
                        'contents' => 'Gửi thông báo',
                    ],
                    [
                        'name' => 'bodyHtml',
                        'contents' => "<!DOCTYPE html> <html lang='en'> <head> <style type='text/css'> body { background: #f5f5f5; font-size: 15px; color: #000000 !important; font-family: roboto; } .font-weight-bold { font-weight: bold; } .container-mail { width: 80% !important; margin: 0 auto; display: grid; } .mt-2 { margin-top: .5rem !important; } .mt-3 { margin-top: 1rem !important; } .text-center { text-align: center !important; } .justify-content-center { -webkit-box-pack: center !important; -ms-flex-pack: center !important; justify-content: center !important; } .d-flex { display: -webkit-box !important; display: -ms-flexbox !important; display: flex !important; } .logo { margin: 0 auto; padding: 15px 0px; } .content { padding: 55px 50px; background: #fff; box-shadow: 0px 0px 15px #cccccc45; overflow: hidden; border-radius: 9px; } .social i { height: 25px; width: 25px; border-radius: 50%; color: #fff; padding-top: 6px; padding-left: 0px; font-size: 14px; margin-right: 10px; text-align: center; } .social i:hover { opacity: 0.5; } .fa-facebook { background: #527abc; } .fa-link { background: #52bb91; } .fa-envelope { background: #51b9d4; } footer { margin: 0 auto; padding: 30px 0px; } .info-company p { margin-bottom: 6px !important; } @media  only screen and (max-width: 768px) { .container-mail { width: 80% !important; } } @media  only screen and (max-width: 576px) { .container-mail { width: 100% !important; } .content { padding: 55px 20px; } .d-flex { display: none; } .col1, .col2 { width: 100% !important; } .ct { display: block !important; } .img_content { height: 190px; width: 100%; border: 1px solid transparent; border-radius: 5px; } } .img_content { width: 100%; height: 190px; border: 1px solid transparent; border-radius: 5px; } /*CSS CONTENT*/ .dear { font-size: 20px; } .img-themvaocanho { max-width: 100%; margin: 0 auto; display: block; } .col-tk { height: 170px; } .col1, .col2 { /* width: 49%; */ border: 5px solid #fff; } .ct { width: 100%; display: inline-flex; } </style> </head> <body> <div class='container-mail' style='color: #000000'> <head> <img class='logo' src='http://127.0.0.1:8001/images/logo-bdc.png'> </head> <!-- START CONTENT --> <div class='content' style='border: 1px solid #d2d1d1; background: #fff;'> <p>Kính gửi Quý cư dân <strong>Đào Ngân,</strong></p> <p><p>Sáng 27/10, huyện Củ Chi đã triển khai chiến dịch tiêm vaccine Covid-19 cho trẻ 16 - 17 tuổi tại điểm tiêm Trường Tiểu học thị trấn Củ Chi.</p> <p>Các bạn học sinh lớp 12 xếp hàng ngay ngắn, đợi kiểm tra thủ tục trước khi vào khu vực chờ tiêm</p> <p>Ai nấy đều vui vẻ, hào hứng khi mình là những học sinh được tiêm vaccine đầu tiên tại TP.HCM</p> <p>Với số lượng hơn 51.000 em nhỏ từ 12 - 17 tuổi, huyện Củ Chi sẽ tổ chức tiêm theo lứa tuổi giảm dần với 6 điểm tiêm cố định gồm: Trường Tiểu học thị trấn Củ Chi (đường Nguyễn Phúc Trú, khu phố 1, thị trấn Củ Chi), Trường THCS thị trấn 2 (số 28 đường Lê Vĩnh Huy, khu phố 7, thị trấn Củ Chi), Trường THCS Phước Thạnh (tỉnh lộ 7, ấp Phước An, xã Phước Thạnh), Trường THPT An Nhơn Tây (tỉnh lộ 7, xã An Nhơn Tây), Trường THCS Tân Thạnh Đông (tỉnh lộ 15, xã Tân Thạnh Đông), Trường Tiểu học Hòa Phú (ấp 1A, xã Hòa Phú).</p> <p>Học sinh vui vẻ, cha mẹ đứng đợi con ngoài cổng trường...</p> <p>Theo ghi nhận của PV, 7h sáng đã rất đông các bậc phụ huynh đưa theo con em đến điểm tiêm tại Trường Tiểu học thị trấn Củ Chi để chờ đợi, ai nấy đều rất vui vẻ, hào hứng sau nhiều ngày chờ đợi, các em học sinh lớp 12 đã được chích vaccine, đảm bảo các điều kiện an toàn để sớm quay lại trường học.</p> <p>Đứng đợi một góc trước cổng trường, cô Nguyễn Thị Phượng (56 tuổi) vui mừng khi Hồng Trang (17 tuổi), con gái cô Phượng đã được chích vaccine. 'Nghe chích vaccine là mừng rồi, cô không lo lắng gì hết, nhà cô còn mỗi bé Trang chưa chích thôi nên cô cũng động viên con đi chích', cô Phượng chia sẻ.</p> <p>Cô Phượng đã mong ngày con gái được tiêm vaccine từ lâu nên không lo lắng, hồi hộp mà động viên con đi tiêm</p> <p>Bên trong khu vực tiếp nhận, các thủ tục được kiểm tra, hướng dẫn một cách rõ ràng, cụ thể</p> <p>Sau khi được kiểm tra đầy đủ giấy tờ, phiếu đăng ký khám sàng lọc, đồng ý từ phía gia đình, các bạn học sinh lớp 12 xếp hàng ngay ngắn, di chuyển vào bên trong trường để làm các thủ tục trước khi tiêm. Mặc dù đây là lần đầu tiên triển khai việc chích vaccine cho trẻ em từ 12 - 17 tuổi nhưng hầu hết các bạn có tâm lý thoải mái, tự tin.</p> <p>Ngồi một góc đợi gọi tên vào tiêm, Nguyễn Xuân Phước (17 tuổi) cho biết đã cảm thấy ổn hơn sau khi nhìn thấy công tác chuẩn bị cho việc chích vaccine. </p> <p>Phước dí dỏm cho biết sợ mũi kim tiêm vì 'đau'</p> <p>'Dạ trước khi tiêm em thấy hồi hộp, em cũng bị sợ kim tiêm nên em sợ lắm. Tối giờ em ngủ không được luôn, em thấy kim tiêm là em sợ, bây giờ em run lắm. Nhưng em cũng vui vì được chích loại vaccine tốt nhất, sau khi thấy mọi người thăm khám, em đỡ run rồi', Phước dí dỏm nói.</p> <p>Ngồi kế bên Phước, Thân Trọng Bảo (17 tuổi) cũng cho biết bản thân em hơi lo lắng vì sợ sẽ gặp phải các triệu chứng sau tiêm. Tuy nhiên, nhìn thấy các bạn đều tự tin, vui vẻ nên bản thân em cũng đỡ áp lực hơn.</p> <p>Trọng Bảo mong sẽ không gặp phản ứng, triệu chứng sau khi tiêm</p> <p>'Sáng giờ đến trường gặp các bạn nên em cũng rất vui, em hi vọng tiêm xong mình sẽ ổn, có vaccine để bảo vệ cơ thể mình tốt hơn', Bảo nói.</p> <p>Sau khi qua các bước khám sàng lọc, kiểm tra, tư vấn sức khỏe..., những bạn trẻ đầu tiên đã được gọi vào bàn tiêm. Loại vaccine được sử dụng để tiêm cho trẻ là Pfizer, các công tác chuẩn bị đều diễn ra hết sức chu đáo.</p> <p>Loại vaccine được sử dụng để tiêm cho trẻ từ 12 - 17 tuổi được Bộ Y tế duyệt là Pfizer</p> <p>Những bạn học sinh lớp 12 được tiêm vaccine đầu tiên tại TP.</p></p> <p>abc tú</p> </div> <div style='text-align: center;margin-top: 5px'> <a href='#'> <img alt=' src='http://127.0.0.1:8001/media/z2628854170821_788aa9f9c4e231b62433bf38669e9adc.jpg'  class='img_content' /> </a> </div> <!-- END CONTENT --> <footer> <div class='social d-flex justify-content-center text-center mb-2' style='margin-left: auto;margin-right: auto;display: block !important;'> <a href='#'><img src='http://127.0.0.1:8001/images/facebook.jpg'></a> <a href='https://buildingcare.biz'><img src='http://127.0.0.1:8001/images/website.jpg'></a> <a href='mailto:bdc@crm.dxmb.vn'><img src='http://127.0.0.1:8001/images/email.jpg'></a> </div> <div class='info-company text-center'> <p class='font-weight-bold'>PHẦN MỀM TIỆN ÍCH CHUNG CƯ BUILDING CARE</p> <p>Địa chỉ: Tầng 18, Center Building, 85 Vũ Trọng Phụng, Thanh Xuân, Hà Nội</p> <p><span>Hotline: 0948.36.9191 &emsp;</span> <span>Website: https://buildingcare.biz &emsp;</span> <span>Mail: bdc@crm.dxmb.vn</span></p> </div> </footer> </div> </body> </html>",
                    ],
                    [
                        'name' => 'bodyText',
                        'contents' => 'text/html',
                    ],
                    [
                        'name' => 'to',
                        'contents' => 'duytu89@gmail.com',
                    ],
                    [
                        'name' => 'isTransactional',
                        'contents' => true
                    ]
                ]
            ];
            dd($options);
            $client = new \GuzzleHttp\Client(['headers' => $headerParams]);
            $requestClient = $client->request('POST', 'https://api.elasticemail.com/v2/email/send', $options);
            $response = json_decode((string)$requestClient->getBody(), true);
            return json_encode($response);
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }

    }

    public function createDebitV2ByApartmentId(Request $request)
    {
        // if(empty($request->apartment))
        // {
        //     dd('chưa chuyền param query: apartment');
        // }
        // $apartment = $request->apartment;
        // $building_list = [72]; // những toà đang chạy
        // $get_apart = Apartments::find($apartment);

        // if(!in_array($get_apart->building_id,$building_list)){
        //     dd('cần chọn căn hộ tòa 72');
        // }

        // DebitDetail::withTrashed()->where('bdc_apartment_id',$apartment)->forceDelete();

        // $sql = "select * from (SELECT  bdc_apartment_service_price_id, count(bdc_apartment_service_price_id) as test,cycle_name,bdc_price_type_id,id
        // FROM    bdc_debit_detail
        // WHERE  EXISTS (SELECT id 
        // FROM bdc_bills 
        // WHERE bdc_debit_detail.bdc_bill_id = bdc_bills.id and bdc_bills.deleted_at is null) and bdc_debit_detail.bdc_apartment_id = $apartment and sumery > 0 and version = 0 and bdc_price_type_id <> 1 and deleted_at is null group by bdc_apartment_id,bdc_apartment_service_price_id,cycle_name) as tb where test > 1";
        // $rs = DB::select(DB::raw($sql));
        // $sql_1 = "select * from (SELECT  bdc_apartment_service_price_id, count(bdc_apartment_service_price_id) as test,cycle_name,bdc_price_type_id,id
        // FROM    bdc_debit_detail
        // WHERE EXISTS (SELECT id 
        // FROM bdc_bills 
        // WHERE bdc_debit_detail.bdc_bill_id = bdc_bills.id and bdc_bills.deleted_at is null) and bdc_debit_detail.bdc_apartment_id = $apartment and sumery > 0 and version = 0 and bdc_price_type_id <> 1 and deleted_at is null group by bdc_apartment_id,bdc_apartment_service_price_id,cycle_name) as tb where test = 1";
        // $rs_2 = DB::select(DB::raw($sql_1));
        // $result = array_merge($rs, $rs_2);
        // $count=0;
        // if(count($result) > 0)
        // {
        //     foreach ($result as $key => $value) {
        //         $count++;
        //         //DebitDetail::withTrashed()->where('bdc_apartment_service_price_id',$value->bdc_apartment_service_price_id)->forceDelete();
        //         $this->priceManyByApartment($value);
        //      }
        // }

        // $result = [];
        // $sql = null;
        // $sql_1 = null;
        // $rs = null;

        // $sql = "SELECT *
        // FROM  bdc_debit_detail
        // WHERE EXISTS (SELECT id from(SELECT * from (SELECT  bdc_apartment_service_price_id, count(bdc_apartment_service_price_id) as test,cycle_name,bdc_price_type_id,id
        // FROM  bdc_debit_detail
        // WHERE EXISTS (SELECT id 
        // FROM bdc_bills 
        // WHERE bdc_debit_detail.bdc_bill_id = bdc_bills.id and bdc_bills.deleted_at is null) and bdc_debit_detail.bdc_apartment_id = $apartment and sumery > 0 and version=0 and bdc_price_type_id=1 and sumery > 0 and deleted_at is null group by bdc_apartment_id,bdc_apartment_service_price_id,cycle_name) as tb where test = 1) as tb2 where tb2.id = bdc_debit_detail.id ) and bdc_debit_detail.bdc_apartment_id = $apartment and version=0 and bdc_price_type_id=1 and sumery > 0 and deleted_at is null group by bdc_apartment_id,bdc_apartment_service_price_id,cycle_name";
        // $rs = DB::select(DB::raw($sql));
        // $sql_1 = "SELECT *
        // FROM  bdc_debit_detail
        // WHERE EXISTS (SELECT id from(SELECT * from (SELECT  bdc_apartment_service_price_id, count(bdc_apartment_service_price_id) as test,cycle_name,bdc_price_type_id,id
        // FROM  bdc_debit_detail
        // WHERE EXISTS (SELECT id 
        // FROM bdc_bills 
        // WHERE bdc_debit_detail.bdc_bill_id = bdc_bills.id and bdc_bills.deleted_at is null) and bdc_debit_detail.bdc_apartment_id = $apartment and sumery > 0 and version=0 and bdc_price_type_id=1 and sumery > 0 and deleted_at is null group by bdc_apartment_id,bdc_apartment_service_price_id,cycle_name) as tb where test > 1) as tb2 where tb2.id = bdc_debit_detail.id ) and bdc_debit_detail.bdc_apartment_id = $apartment and version=0 and bdc_price_type_id=1 and sumery > 0 and deleted_at is null group by bdc_apartment_id,bdc_apartment_service_price_id,cycle_name";
        // $rs_1 = DB::select(DB::raw($sql_1));
        // $result = array_merge($rs, $rs_1);

        // if(count($result) > 0)
        // {
        //     foreach ($result as $key => $value) {
        //         $count++;
        //         $this->priceOneByApartment($value);
        //     }
        // }
        // echo "$count". "</br>";
    }

    public function priceManyByApartment($debit)
    {
        // try {
        //         $value = $debit;
        //         if($value){
        //             $value = (object)$value;
        //             $rs_debit = BdcDebitDetailDebitDetail::where(['bdc_apartment_service_price_id' => $value->bdc_apartment_service_price_id, 'cycle_name' => $value->cycle_name, 'version' => 0])->get();
        //             $bdc_building_id = 0;
        //             $bdc_bill_id = 0;
        //             $bdc_apartment_id = 0;
        //             $bdc_apartment_service_price_id = 0;
        //             $from_date = 0;
        //             $to_date = 0;
        //             $detail = 0;
        //             $cycle_name = null;
        //             $quantity = 0;
        //             $price = 0;
        //             $so_dau = 0;
        //             $so_cuoi = 0;
        //             $progressive = null;
        //             $dataJson=[];
        //             $sumery = 0;
        //             foreach ($rs_debit as $key_1 => $value_1) {
        //                 if ($key_1 == 0) {
        //                     $bdc_building_id = $value_1->bdc_building_id;
        //                     $bdc_bill_id = $value_1->bdc_bill_id;
        //                     $bdc_apartment_id = $value_1->bdc_apartment_id;
        //                     $bdc_apartment_service_price_id = $value_1->bdc_apartment_service_price_id;
        //                     $from_date = $value_1->from_date;
        //                     if ($value->bdc_price_type_id == 2) {
        //                         $detail = json_decode($value_1->detail);
        //                         $so_dau = @$detail->so_dau ?? 0;
        //                         $_apartmentServicePrice = DB::table('bdc_apartment_service_price')->find($value_1->bdc_apartment_service_price_id);
        //                         $progressive = DB::table('bdc_progressives')->find($_apartmentServicePrice->bdc_progressive_id);
        //                     }
        //                 }
        //                 if ($value->bdc_price_type_id == 2) {
        //                     $detail_1 = json_decode($value_1->detail);
        //                     $so_cuoi = @$detail_1->so_cuoi ?? 0;
        //                     if(@$detail_1->data){
        //                         $dataJson = array_merge($dataJson,$detail_1->data);
        //                     }
        //                 }
        //                 $cycle_name = $value_1->cycle_name;
        //                 $sumery += $value_1->sumery;
        //                 $to_date =  $value_1->to_date;
        //             }
        //             if($sumery == 0 || $sumery < 0){
        //                 return 1;
        //             }
        //             echo "debit_id: $value->id sumery: $sumery". "</br>";
        //             if ($value->bdc_price_type_id == 2) {
        //                     $progressivePrices =  DB::table('bdc_progressive_price')->where('progressive_id',$progressive->id)->get();
        //                     $price = 0;
        //                     $totalPrice = 0;
        //                     $dataArray = array();
        //                     $_dataArray = array();
        //                     $soDau = 0;
        //                     $soCuoi = 0;
        //                     $totalNumber = 0;

        //                     foreach ($progressivePrices as $progressivePrice) {
        //                         // tính tổng tiền cho dich vụ điện nước
        //                         $soDau = $so_dau;
        //                         $soCuoi = $so_cuoi;
        //                         $totalNumber = $soCuoi - $soDau;
        //                         if ($progressivePrice->to >= $totalNumber) {
        //                             $price = ($totalNumber - $progressivePrice->from + 1) * $progressivePrice->price;
        //                             $_dataArray["from"] = $progressivePrice->from;
        //                             $_dataArray["to"] = $totalNumber;
        //                             $_dataArray["price"] = $progressivePrice->price;
        //                             $_dataArray["total_price"] = $price;
        //                             $totalPrice += $price;
        //                             array_push($dataArray, $_dataArray);
        //                             break;
        //                         } else {
        //                             $price = ($progressivePrice->to - $progressivePrice->from + 1) * $progressivePrice->price;
        //                             $_dataArray["from"] = $progressivePrice->from;
        //                             $_dataArray["to"] = $progressivePrice->to;
        //                             $_dataArray["price"] = $progressivePrice->price;
        //                             $_dataArray["total_price"] = $price;
        //                             $totalPrice += $price;
        //                             array_push($dataArray, $_dataArray);
        //                         }
        //                     }
        //                     $dataJson = json_encode($dataJson);
        //                     $dataString = '{"so_dau": ' . $soDau . ', "so_cuoi": ' . $soCuoi . ', "tieu_thu": ' . $totalNumber . ', "data":' . $dataJson . "}";
        //                     $check = DebitDetail::create([
        //                         'bdc_building_id' => $bdc_building_id,
        //                         'bdc_bill_id' => $bdc_bill_id,
        //                         'bdc_apartment_id' => $bdc_apartment_id,
        //                         'bdc_apartment_service_price_id' => $bdc_apartment_service_price_id,
        //                         'from_date' => $from_date,
        //                         'to_date' => $to_date,
        //                         'detail' => $dataString,
        //                         'previous_owed' => 0,
        //                         'cycle_name' => $cycle_name,
        //                         'quantity' => 0,
        //                         'price' => 0,
        //                         'sumery' => $sumery,
        //                         'note' =>  'v1->v2'.'|'.$value->id,
        //                     ]);

        //             } else {
        //                 DebitDetail::create([
        //                     'bdc_building_id' => $bdc_building_id,
        //                     'bdc_bill_id' => $bdc_bill_id,
        //                     'bdc_apartment_id' => $bdc_apartment_id,
        //                     'bdc_apartment_service_price_id' => $bdc_apartment_service_price_id,
        //                     'from_date' => $from_date,
        //                     'to_date' => $to_date,
        //                     'detail' => '[]',
        //                     'previous_owed' => 0,
        //                     'cycle_name' => $cycle_name,
        //                     'quantity' => 1,
        //                     'price' => 0,
        //                     'sumery' => $sumery,
        //                     'note' => 'v1->v2'.'|'.$value->id,
        //                 ]);


        //             }
        //             return 1;
        //         }

        // } catch (\Exception $e) {
        //    echo $e->getMessage().'|'.$e->getLine().'</br>';
        // }
    }

    public function getPassUserV2(Request $request)
    {
        if (empty($request->account)) {
            dd('chưa chuyền param query: account');
        }
        $account = $request->account;

        $user = User::where(function ($query) use ($account) {
            $query->where('email', $account)
                ->orwhere('phone', $account);
        })->first();

        if ($user) {
            $new_result = [];
            $user_info = V2UserInfo::where('user_id', $user->id)->first();
            $user_apartment = UserApartments::where('user_info_id', $user_info->id)->first();

            if ($user_apartment) {
                $building = Building::get_detail_building_by_building_id($user_apartment->building_id);
                $request->request->add(['building_id' => $user_apartment->building_id]);
                $request->request->add(['user_id' => $user->id]);
                $result_reset = Api::POST('admin/resetUserPass', $request->all());
                $new_result[] = $user_info->toArray();
                $new_result[] = $user_apartment->toArray();
                $new_result[] = $user->toArray();
                $new_result[] = $building;
                $new_result[] = $result_reset;
                dd($new_result);
            }
        }

    }

    public function priceOneByApartment($debit)
    {
        try {
            $value = $debit;
            if ($value) {
                $value = (object)$value;
                echo "debit_id: $value->id sumery: $value->sumery" . "</br>";

                DebitDetail::create([
                    'bdc_building_id' => $value->bdc_building_id,
                    'bdc_bill_id' => $value->bdc_bill_id,
                    'bdc_apartment_id' => $value->bdc_apartment_id,
                    'bdc_apartment_service_price_id' => $value->bdc_apartment_service_price_id,
                    'from_date' => $value->from_date,
                    'to_date' => $value->to_date,
                    'detail' => '[]',
                    'previous_owed' => 0,
                    'cycle_name' => $value->cycle_name,
                    'quantity' => $value->quantity,
                    'price' => $value->price,
                    'sumery' => $value->sumery,
                    'note' => 'v1->v2' . '|' . $value->id,
                ]);

            }
            return 1;
        } catch (\Exception $e) {
            echo $e->getMessage() . '|' . $e->getLine() . '</br>';
        }
    }

    public function generate(Request $request)
    {
        $buildings = [102];
        $list_config_default = Helper::config_receipt;
        foreach ($buildings as $key => $value) {
            foreach ($list_config_default as $key_1 => $value_1) {
                $check_config_default = Configs::where(['bdc_building_id' => $value, 'key' => $value_1['key']])->first();
                if (!$check_config_default) {
                    $value_1['bdc_building_id'] = $value;
                    $value_1['publish'] = 1;
                    $value_1['status'] = 1;
                    $value_1['default'] = 1;
                    $value_1['value'] = $value_1['value'] . '_' . $value;
                    Configs::create($value_1);
                }
            }
        }
    }

    public function reportApartmentByBuilding(Request $request)
    {
        set_time_limit(0);
        //$IdBuilding = [17,37,68, 69,70,71,72,77,80,81,86,87,90,98];
        $IdBuilding = [17];
        if (empty($request->buildingId)) {
            dd('chưa chuyền param query: buildingId');
        }
        $buildingId = $request->buildingId;
        $count_v2 = 0;
        $getApartment = Apartments::whereIn('building_id', [$buildingId])->orderBy('building_id')->get();
        $result = Excel::create('Phiếu thu', function ($excel) use ($getApartment, $count_v2) {
            $excel->setTitle('Phiếu thu');
            $excel->sheet('Phiếu thu', function ($sheet) use ($getApartment, $count_v2) {
                $content = [];

                $count_empty_resident = 0;
                $count_resident_not_login_v1_v2 = 0;
                $count_apartment_not_login_v1_v2 = 0;
                $count_resident_login_v1_and_not_login_v2 = 0;
                $count_apartment_login_v1_and_not_login_v2 = null;
                $count_apartment_login_v2 = [];
                $count_resident_has_account = null;
                $account_login_v2 = 0;
                $cu_dan_v2 = null;
                foreach ($getApartment as $key => $value) {
                    $building = Building::get_detail_building_by_building_id($value->building_id);
                    //V1 ==========================================================================
                    $_Customer = Customers::where('bdc_apartment_id', $value->id)->where('type', '<>', 7)->pluck('pub_user_profile_id')->toArray();
                    if (count($_Customer) > 0) {
                        $userLogin = Users::whereHas('userInfoProfile', function ($query) use ($_Customer) {
                            $query->whereIn('id', $_Customer);
                        })->where('mobile_active', '>=', 1)->get();
                    }


                    //V2 ==========================================================================
                    $_Customer_v2 = UserApartments::where('apartment_id', $value->id)->where('type', '<>', 7)->pluck('user_info_id')->toArray();
                    $_Customer_v2_ = UserApartments::where('apartment_id', $value->id)->count();
                    if (count($_Customer_v2) > 0) {
                        foreach ($_Customer_v2 as $key_6 => $value_6) {
                            $cu_dan_v2[] = $value_6;
                        }
                        $user_info_v2 = V2UserInfo::whereHas('user_token')->whereIn('id', $_Customer_v2)->pluck('user_id')->toArray();
                        $userLogin_v2 = count($user_info_v2) > 0 ? User::whereIn('id', $user_info_v2)->get() : null;
                    }
                    $list_user_v2 = [];
                    if (isset($userLogin_v2) && $userLogin_v2->count() > 0) {
                        foreach ($userLogin_v2 as $key_2 => $value_2) {
                            $count_v2++;
                            if ($value_2->email) {
                                $list_user_v2[] = $value_2->email;
                                $count_resident_has_account[] = $value->email;
                                $count_apartment_login_v2[] = $value->id;
                                $account_login_v2++;
                            } else {
                                $list_user_v2[] = $value_2->phone;
                                $count_resident_has_account[] = $value->phone;
                                $count_apartment_login_v2[] = $value->id;
                                $account_login_v2++;
                            }
                        }
                    }
                    $list_user_v1 = [];
                    if (isset($userLogin) && $userLogin->count() > 0) {
                        foreach ($userLogin as $key_1 => $value_1) {

                            if ($value_1->email) {
                                $list_user_v1[] = $value_1->email;
                                $count_resident_has_account[] = $value->email;
                                if (!in_array($value_1->email, $list_user_v2)) {
                                    $count_resident_login_v1_and_not_login_v2++;
                                    $count_apartment_login_v1_and_not_login_v2[] = $value->id;
                                }
                            } else {
                                $list_user_v1[] = $value_1->mobile;
                                $count_resident_has_account[] = $value->mobile;
                                if (!in_array($value_1->mobile, $list_user_v2)) {
                                    $count_resident_login_v1_and_not_login_v2++;
                                    $count_apartment_login_v1_and_not_login_v2[] = $value->id;
                                }
                            }


                        }
                    }

                    if (count($_Customer_v2) == 0) {
                        $count_empty_resident++;
                    }
                    if ((count($_Customer) > 0 || count($_Customer_v2) > 0) && (count($list_user_v1) == 0 && count($list_user_v2) == 0)) {
                        $count_apartment_not_login_v1_v2++;
                    }
                    // if (count($list_user_v1) > count($list_user_v2)) {
                    //     foreach ($list_user_v1 as $key_3 => $value_3) {
                    //         $content[] = [
                    //             'ID tòa' =>  $building->id,
                    //             'Tên tòa' =>  $building->name,
                    //             'Căn hộ' =>  $value->name,
                    //             'Số người đang ở' => 1,
                    //             'Chi tiết TK tải app v1' => $value_3,
                    //             'Chi tiết TK tải app v2' => @$list_user_v2[$key_3],
                    //         ];

                    //     }
                    // } else if (count($list_user_v1) >0 && count($list_user_v1) <= count($list_user_v2)) {
                    //     foreach ($list_user_v2 as $key_4 => $value_4) {
                    //         $content[] = [
                    //             'ID tòa' =>  $building->id,
                    //             'Tên tòa' =>  $building->name,
                    //             'Căn hộ' =>  $value->name,
                    //             'Số người đang ở' => 1,
                    //             'Chi tiết TK tải app v1' => @$list_user_v1[$key_4],
                    //             'Chi tiết TK tải app v2' => $value_4,
                    //         ];
                    //     }
                    // }else{
                    //     $content[] = [
                    //         'ID tòa' =>  $building->id,
                    //         'Tên tòa' =>  $building->name,
                    //         'Căn hộ' =>  $value->name,
                    //         'Số người đang ở' => 0,
                    //         'Chi tiết TK tải app v1' => '',
                    //         'Chi tiết TK tải app v2' => '',
                    //     ];
                    // }

                    // if($_Customer_v2_ == 0) {
                    //     $content[] = [
                    //         'ID tòa' =>  $building->id,
                    //         'Tên tòa' =>  $building->name,
                    //         'Căn hộ' =>  $value->name,
                    //         'Số người đang ở' => 0,
                    //         'Chi tiết TK tải app v1' => '',
                    //         'Chi tiết TK tải app v2' => '',
                    //     ];
                    // }
                }

                // báo cáo thống kê
                $count_apartment_login_v1_and_not_login_v2 = $count_apartment_login_v1_and_not_login_v2 ? array_unique($count_apartment_login_v1_and_not_login_v2) : null;
                $count_apartment_login_v2 = $count_apartment_login_v2 ? array_unique($count_apartment_login_v2) : [];
                $cu_dan_v2 = array_unique($cu_dan_v2);
                $sql = "select * from bdc_v2_user_info where id in ('" . implode("','", $cu_dan_v2) . "') and deleted_at is null";
                $user_info_v2 = DB::table(DB::raw("($sql) as tb1"))->pluck('user_id')->toArray();

                $sql_1 = "select * from bdc_v2_token where user_id not in ('" . implode("','", $user_info_v2) . "') and deleted_at is null";

                $userToken = count($user_info_v2) > 0 ? DB::table(DB::raw("($sql_1) as tb1"))->pluck('user_id')->toArray() : [];

                if (count($userToken) > 0) {
                    foreach ($userToken as $key_5 => $value_5) {
                        $_user = User::find($value_5);
                        if ($_user) {
                            $_user_old = Users::where(function ($query) use ($_user) {
                                $query->where('email', $_user->email)
                                    ->orwhere('mobile', $_user->phone);
                            })->first();
                            if (!$_user_old) {
                                $count_resident_not_login_v1_v2++;
                            }
                        }

                    }
                }
                $content[] = [
                    'ID tòa' => $building->id,
                    'Tên tòa' => $building->name,
                    'Căn hộ' => $value->name,
                    'Số người đang ở' => 0,
                    'Chi tiết TK tải app v1' => '',
                    'Chi tiết TK tải app v2' => '',
                ];
                $content[] = [
                    // chi tiết báo cáo thống kê căn hộ
                    'Số lượng căn hộ',
                    $getApartment->count(),
                ];
                $content[] = [
                    // chi tiết báo cáo thống kê căn hộ
                    'Số căn hộ chưa có người đến ở',
                    $count_empty_resident,
                ];
                $content[] = [
                    'Số lượng căn hộ đã từng dùng app v1 mà chưa dùng app v2 (nếu có chủ cũ thì ko tính)',
                    $count_apartment_login_v1_and_not_login_v2 ? count($count_apartment_login_v1_and_not_login_v2) : 0,
                ];
                $content[] = [
                    'Số lượng căn hộ đã có người ở và chưa sử dụng app v1 và app v2 (nếu có chủ cũ thì ko tính)',
                    $count_apartment_not_login_v1_v2,
                ];
                $content[] = [
                    'Số lượng căn hộ sử dụng app v2 (nếu có chủ cũ thì ko tính)',
                    count($count_apartment_login_v2),
                ];
                $content[] = [
                    'Số lượng cư dân có tài khoản',
                    count($user_info_v2)
                ];
                $content[] = [
                    'Số lượng cư dân đã từng dùng app v1 mà chưa dùng app v2',
                    $count_resident_login_v1_and_not_login_v2,
                ];
                $content[] = [
                    'Số lượng cư dân có tài khoản nhưng chưa sử dụng app v1 và app v2',
                    $count_resident_not_login_v1_v2,
                ];
                $content[] = [
                    'Số lượng cư dân có tài khoản sử dụng app v2',
                    $account_login_v2,
                ];
                if ($content) {
                    $sheet->fromArray($content);
                }
            });
        })->store('xlsx', storage_path('exports/'));
        $file = storage_path('exports/' . $result->filename . '.' . $result->ext);
        return response()->download($file)->deleteFileAfterSend(true);

    }

    public function reportApartmentByBuildingV2(Request $request)
    {
        set_time_limit(0);
        //$IdBuilding = [17,37,68, 69,70,71,72,77,80,81,86,87,90,98];
        $IdBuilding = [17];
        $count_v2 = 0;
        $getApartment = Apartments::whereIn('building_id', $IdBuilding)->orderBy('building_id')->get();
        foreach ($getApartment as $key => $value) {
            //V2 ==========================================================================
            $_Customer_v2 = UserApartments::where('apartment_id', $value->id)->pluck('user_info_id')->toArray();
            if (count($_Customer_v2) > 0) {
                $user_info_v2 = V2UserInfo::whereIn('id', $_Customer_v2)->pluck('user_id')->toArray();
                $userToken = count($user_info_v2) > 0 ? TokenUser::whereIn('user_id', $user_info_v2)->groupBy('user_id')->pluck('user_id')->toArray() : [];
                $userLogin_v2 = count($userToken) > 0 ? User::whereIn('id', $userToken)->get() : null;
            }
            $list_user_v2 = [];
            if (isset($userLogin_v2) && $userLogin_v2->count() > 0) {
                foreach ($userLogin_v2 as $key_2 => $value_2) {
                    $count_v2++;
                    if ($value_2->email) {
                        echo $value_2->email . '</br>';
                    } else {
                        echo $value_2->phone . '</br>';
                    }
                }
            }
        }
        dd($count_v2);
    }

    public function setConfigBank(Request $request)
    {
        if (empty($request->buildingId)) {
            dd('chưa chuyền param query: buildingId');
        }
        if (empty($request->chanel_payment)) {
            dd('chưa chuyền param query: chanel_payment');
        }
        $buildingId = $request->buildingId;
        $chanel_payment = $request->chanel_payment;
        $building = Building::find($buildingId);
        $building->chanel_payment = $chanel_payment;
        $building->save();
        dd($building);
    }

    public function clearKeyApartment(Request $request)
    {
        if (empty($request->buildingId)) {
            return response()->json(['status' => false, 'messages' => 'chưa có param chuyền vào.'], 200);
        }
        $buildingId = $request->buildingId;
        $aparts = Apartments::where('building_id', $buildingId)->get();
        foreach ($aparts as $key => $value) {
            Cache::store('redis')->forget(env('REDIS_PREFIX') . 'get_detail_apartmentById_' . $value->id);
        }
        return response()->json(['status' => true, 'messages' => 'thành công' . $aparts->count()], 200);
    }
}

<?php

namespace App\Http\Controllers\Dev;

use App\Commons\Helper;
use App\Exceptions\QueueRedis;
use App\Helpers\dBug;
use App\Models\BdcBills\Bills;
use App\Models\BdcCoin\Coin;
use App\Models\BdcReceipts\Receipts;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Models\BdcV2LogCoinDetail\LogCoinDetail;
use App\Models\BdcV2PaymentDetail\PaymentDetail;
use App\Models\CronJobManager\CronJobManager;
use App\Models\DatabaseLog\DatabaseLog;
use App\Models\RequestLog\RequestLog;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcCoin\BdcCoinRepository;
use App\Repositories\BdcLockCyclename\BdcLockCyclenameRepository;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository;
use App\Repositories\BdcReceipts\ReceiptRepository;
use App\Repositories\BdcV2LogCoinDetail\LogCoinDetailRepository;
use App\Repositories\BdcV2PaymentDetail\PaymentDetailRepository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Promotion\PromotionRepository;
use App\Repositories\PromotionApartment\PromotionApartmentRepository;
use App\Repositories\Service\ServiceRepository;
use App\Services\SendTelegram;
use App\Traits\ApiResponse;
use App\Util\Redis;
use DebugBar;
use Illuminate\Http\Request;
use App\Http\Controllers\Backend\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\FCM\SendNotifyFCMService;
use FCM;
use Illuminate\Support\Facades\Redis as RedisLaravel;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use App\Util\Debug\Log;
use League\Flysystem\Exception;
use Maatwebsite\Excel\Facades\Excel;

class DevController extends Controller
{
    use ApiResponse;
    /**
     * Khởi tạo
     */
    public function __construct(Request $request, BdcCoinRepository $BdcCoinRepository)
    {
        //$this->middleware('auth', ['except'=>[]]);
//        //$this->middleware('route_permision');
//        parent::__construct($request);
    }


    /**
     * Danh sách bản ghi
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        dd(43);
    }

    /**
     * Danh sách bản ghi
     *
     * @param Request $request
     * @return Response
     */
    public function updateDebit(Request $request)
    {
//        dd(123);
        $listUpdate = array(
            0 => array('17', '06/2020', '202006'),
            1 => array('17', '07/2020', '202007'),
            2 => array('17', '08/2020', '202008'),
            3 => array('17', '09/2020', '202009'),
            4 => array('17', '1', '202101'),
            5 => array('17', '10', '202010'),
            6 => array('17', '11', '202011'),
            7 => array('17', '12', '202012'),
            8 => array('17', '2', '202102'),
            9 => array('17', '202105', '202105'),
            10 => array('17', '202106', '202106'),
            11 => array('17', '202107', '202107'),
            12 => array('17', '202108', '202108'),
            13 => array('17', '202109', '202109'),
            14 => array('17', '202110', '202110'),
            15 => array('17', '202111', '202111'),
            16 => array('17', '202112', '202112'),
            17 => array('17', '202201', '202201'),
            18 => array('17', '3', '202103'),
            19 => array('17', '4', '202004'),
            20 => array('17', '5', '202005'),
            21 => array('17', '6', '202006'),
            22 => array('17', '7', '202007'),
            23 => array('17', '8', '202008'),
            24 => array('17', '9', '202009'),
            25 => array('20', '06/2020', '202006'),
            26 => array('20', '2', '201902'),
            27 => array('20', '6/2020', '202006'),
            28 => array('30', '07/2020', '202007'),
            29 => array('30', '08/2020', '202008'),
            30 => array('30', '09/2020', '202009'),
            31 => array('30', '10/2020', '202010'),
            32 => array('30', '11/2020', '202011'),
            33 => array('30', '12/2020', '202012'),
            34 => array('30', '6/2020', '202006'),
            35 => array('30', '7/2020', '202007'),
            36 => array('30', '8/2020', '202008'),
            37 => array('37', '01/2021', '202101'),
            38 => array('37', '02/2021', '202102'),
            39 => array('37', '03/2020', '202003'),
            40 => array('37', '03/2021', '202103'),
            41 => array('37', '04/2020', '202004'),
            42 => array('37', '05/2020', '202005'),
            43 => array('37', '06/2020', '202006'),
            44 => array('37', '07/2020', '202007'),
            45 => array('37', '08/2020', '202008'),
            46 => array('37', '09/2020', '202009'),
            47 => array('37', '1', '202101'),
            48 => array('37', '1/2020', '202001'),
            49 => array('37', '10', '202010'),
            50 => array('37', '10/2019', '201910'),
            51 => array('37', '10/2020', '202010'),
            52 => array('37', '11', '202011'),
            53 => array('37', '11/2019', '201911'),
            54 => array('37', '11/2020', '202011'),
            55 => array('37', '12', '202012'),
            56 => array('37', '12/2019', '201912'),
            57 => array('37', '12/2020', '202012'),
            58 => array('37', '2', '202002'),
            59 => array('37', '202104', '202104'),
            60 => array('37', '202105', '202105'),
            61 => array('37', '202106', '202106'),
            62 => array('37', '202107', '202107'),
            63 => array('37', '202108', '202108'),
            64 => array('37', '202109', '202109'),
            65 => array('37', '202110', '202110'),
            66 => array('37', '202111', '202111'),
            67 => array('37', '202112', '202112'),
            68 => array('37', '202201', '202201'),
            69 => array('37', '3', '202003'),
            70 => array('37', '4', '202004'),
            71 => array('37', '4/2020', '202004'),
            72 => array('37', '5', '202005'),
            73 => array('37', '5/2020', '202005'),
            74 => array('37', '6', '202006'),
            75 => array('37', '7', '202007'),
            76 => array('37', '7/2019', '201907'),
            77 => array('37', '8', '202008'),
            78 => array('37', '8/2019', '201908'),
            79 => array('37', '9', '202009'),
            80 => array('37', '9/2019', '201909'),
            81 => array('60', '01/2020', '202001'),
            82 => array('60', '01/2021', '202101'),
            83 => array('60', '02/2020', '202002'),
            84 => array('60', '02/2021', '202102'),
            85 => array('60', '03/2020', '202003'),
            86 => array('60', '03/2021', '202103'),
            87 => array('60', '04/2020', '202004'),
            88 => array('60', '05/2020', '202005'),
            89 => array('60', '1', '202101'),
            90 => array('60', '1_2021', '202101'),
            91 => array('60', '11', '202011'),
            92 => array('60', '11/2020', '202011'),
            93 => array('60', '12', '202012'),
            94 => array('60', '12/2019', '201912'),
            95 => array('60', '12/2020', '202012'),
            96 => array('60', '2', '202002'),
            97 => array('60', '2_2021', '202102'),
            98 => array('60', '2020-11-01 00:00:00', '202011'),
            99 => array('60', '202101', '202101'),
            100 => array('60', '202102', '202102'),
            101 => array('60', '202103', '202103'),
            102 => array('60', '202104', '202104'),
            103 => array('60', '3', '202003'),
            104 => array('60', '3_2021', '202103'),
            105 => array('60', '7_2021', '202107'),
            106 => array('60', '8_2021', '202108'),
            107 => array('61', '02/2020', '202002'),
            108 => array('62', '01/2020', '202001'),
            109 => array('62', '01/2021', '202101'),
            110 => array('62', '02/2020', '202002'),
            111 => array('62', '02/2021', '202102'),
            112 => array('62', '03', '202003'),
            113 => array('62', '03/2020', '202003'),
            114 => array('62', '03/2021', '202103'),
            115 => array('62', '04', '202004'),
            116 => array('62', '04/2020', '202004'),
            117 => array('62', '05', '202005'),
            118 => array('62', '05/2020', '202005'),
            119 => array('62', '06', '202006'),
            120 => array('62', '06/2020', '202006'),
            121 => array('62', '07', '202007'),
            122 => array('62', '07/2020', '202007'),
            123 => array('62', '08/2020', '202008'),
            124 => array('62', '09', '202009'),
            125 => array('62', '09/2020', '202009'),
            126 => array('62', '1', '202001'),
            127 => array('62', '10', '201910'),
            128 => array('62', '10/2020', '202010'),
            129 => array('62', '11/2020', '202011'),
            130 => array('62', '202103', '202103'),
            131 => array('62', '202104', '202104'),
            132 => array('62', '202105', '202105'),
            133 => array('62', '3', '202003'),
            134 => array('62', '3/2020', '202003'),
            135 => array('62', '4', '202004'),
            136 => array('62', '4/2019', '201904'),
            137 => array('62', '4/2020', '202004'),
            138 => array('62', '8', '202008'),
            139 => array('62', '8/2020', '202008'),
            140 => array('64', '10', '202010'),
            152 => array('64', '3', '202103'),
            153 => array('64', '4', '202104'),
            154 => array('65', '02/2020', '202002'),
            155 => array('65', '03/2020', '202003'),
            156 => array('66', '04/2020', '202004'),
            157 => array('66', '05/2020', '202005'),
            158 => array('66', '4/2020', '202004'),
            159 => array('66', '5/2020', '202005'),
            160 => array('67', '01/2021', '202101'),
            161 => array('67', '02/2021', '202102'),
            162 => array('67', '09/2020', '202009'),
            163 => array('67', '1-2021', '202101'),
            164 => array('67', '10/2020', '202010'),
            165 => array('67', '11/2020', '202011'),
            166 => array('67', '2_2021', '202102'),
            172 => array('68', '01/2021', '202101'),
            173 => array('68', '02/2021', '202102'),
            174 => array('68', '03/2021', '202103'),
            175 => array('68', '1', '202101'),
            176 => array('68', '1/2021', '202101'),
            177 => array('68', '10/2020', '202010'),
            178 => array('68', '11', '202011'),
            179 => array('68', '11/2020', '202011'),
            180 => array('68', '12/2020', '202012'),
            181 => array('68', '2', '202102'),
            195 => array('68', '3', '202103'),
            196 => array('68', '4', '202104'),
            197 => array('73', '2021', '2021'),
            198 => array('77', '122021', '202012'),
        );

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'test_index_update');
        if ($rs === null) $rs = 0;
        if (!isset($listUpdate[$rs])) {
            Cache::store('redis')->set(env('REDIS_PREFIX') . 'test_index_update', $rs + 1, 60 * 60 * 24);
            echo "not exits " . $rs;
            die;
        }
        $sql = "UPDATE bdc_debit_detail SET cycle_name = '" . $listUpdate[$rs][2] . "' WHERE bdc_building_id=" . $listUpdate[$rs][0] . " AND cycle_name = '" . $listUpdate[$rs][1] . "'";
        $rows = DB::update($sql);
        Cache::store('redis')->set(env('REDIS_PREFIX') . 'test_index_update', $rs + 1, 60 * 60 * 24);
        dd($rows);
    }

    public function resetDebit(Request $request)
    {
//        dd(1);
        /*$sql = "UPDATE bdc_debit_detail SET cycle_name = '".."' WHERE bdc_building_id=".." AND cycle_name = '".."'";
        $rows = DB::select(DB::raw($sql));
        dd($rows);
        foreach ($rows as $row) {
        }*/
        Cache::store('redis')->set(env('REDIS_PREFIX') . 'test_index_update', 0);
        echo "this reset index";
        die;
    }

    public function updateBill(Request $request)
    {
        $listUpdate = array(
            0 => array('17', '06/2020', '202006'),
            1 => array('17', '07/2020', '202007'),
            2 => array('17', '08/2020', '202008'),
            3 => array('17', '09/2020', '202009'),
            4 => array('17', '1', '202101'),
            5 => array('17', '10', '202010'),
            6 => array('17', '11', '202011'),
            7 => array('17', '12', '202012'),
            8 => array('17', '2', '202102'),
            9 => array('17', '202105', '202105'),
            10 => array('17', '202106', '202106'),
            11 => array('17', '202107', '202107'),
            12 => array('17', '202108', '202108'),
            13 => array('17', '202109', '202109'),
            14 => array('17', '202110', '202110'),
            15 => array('17', '202111', '202111'),
            16 => array('17', '202112', '202112'),
            17 => array('17', '202201', '202201'),
            18 => array('17', '3', '202103'),
            19 => array('17', '4', '202004'),
            20 => array('17', '5', '202005'),
            21 => array('17', '6', '202006'),
            22 => array('17', '7', '202007'),
            23 => array('17', '8', '202008'),
            24 => array('17', '9', '202009'),
            25 => array('20', '06/2020', '202006'),
            26 => array('20', '2', '201902'),
            27 => array('20', '6/2020', '202006'),
            28 => array('30', '07/2020', '202007'),
            29 => array('30', '08/2020', '202008'),
            30 => array('30', '09/2020', '202009'),
            31 => array('30', '10/2020', '202010'),
            32 => array('30', '11/2020', '202011'),
            33 => array('30', '12/2020', '202012'),
            34 => array('30', '6/2020', '202006'),
            35 => array('30', '7/2020', '202007'),
            36 => array('30', '8/2020', '202008'),
            37 => array('37', '01/2021', '202101'),
            38 => array('37', '02/2021', '202102'),
            39 => array('37', '03/2020', '202003'),
            40 => array('37', '03/2021', '202103'),
            41 => array('37', '04/2020', '202004'),
            42 => array('37', '05/2020', '202005'),
            43 => array('37', '06/2020', '202006'),
            44 => array('37', '07/2020', '202007'),
            45 => array('37', '08/2020', '202008'),
            46 => array('37', '09/2020', '202009'),
            47 => array('37', '1', '202101'),
            48 => array('37', '1/2020', '202001'),
            49 => array('37', '10', '202010'),
            50 => array('37', '10/2019', '201910'),
            51 => array('37', '10/2020', '202010'),
            52 => array('37', '11', '202011'),
            53 => array('37', '11/2019', '201911'),
            54 => array('37', '11/2020', '202011'),
            55 => array('37', '12', '202012'),
            56 => array('37', '12/2019', '201912'),
            57 => array('37', '12/2020', '202012'),
            58 => array('37', '2', '202002'),
            59 => array('37', '202104', '202104'),
            60 => array('37', '202105', '202105'),
            61 => array('37', '202106', '202106'),
            62 => array('37', '202107', '202107'),
            63 => array('37', '202108', '202108'),
            64 => array('37', '202109', '202109'),
            65 => array('37', '202110', '202110'),
            66 => array('37', '202111', '202111'),
            67 => array('37', '202112', '202112'),
            68 => array('37', '202201', '202201'),
            69 => array('37', '3', '202003'),
            70 => array('37', '4', '202004'),
            71 => array('37', '4/2020', '202004'),
            72 => array('37', '5', '202005'),
            73 => array('37', '5/2020', '202005'),
            74 => array('37', '6', '202006'),
            75 => array('37', '7', '202007'),
            76 => array('37', '7/2019', '201907'),
            77 => array('37', '8', '202008'),
            78 => array('37', '8/2019', '201908'),
            79 => array('37', '9', '202009'),
            80 => array('37', '9/2019', '201909'),
            81 => array('60', '01/2020', '202001'),
            82 => array('60', '01/2021', '202101'),
            83 => array('60', '02/2020', '202002'),
            84 => array('60', '02/2021', '202102'),
            85 => array('60', '03/2020', '202003'),
            86 => array('60', '03/2021', '202103'),
            87 => array('60', '04/2020', '202004'),
            88 => array('60', '05/2020', '202005'),
            89 => array('60', '1', '202101'),
            90 => array('60', '1_2021', '202101'),
            91 => array('60', '11', '202011'),
            92 => array('60', '11/2020', '202011'),
            93 => array('60', '12', '202012'),
            94 => array('60', '12/2019', '201912'),
            95 => array('60', '12/2020', '202012'),
            96 => array('60', '2', '202002'),
            97 => array('60', '2_2021', '202102'),
            98 => array('60', '2020-11-01 00:00:00', '202011'),
            99 => array('60', '202101', '202101'),
            100 => array('60', '202102', '202102'),
            101 => array('60', '202103', '202103'),
            102 => array('60', '202104', '202104'),
            103 => array('60', '3', '202003'),
            104 => array('60', '3_2021', '202103'),
            105 => array('60', '7_2021', '202107'),
            106 => array('60', '8_2021', '202108'),
            107 => array('61', '02/2020', '202002'),
            108 => array('62', '01/2020', '202001'),
            109 => array('62', '01/2021', '202101'),
            110 => array('62', '02/2020', '202002'),
            111 => array('62', '02/2021', '202102'),
            112 => array('62', '03', '202003'),
            113 => array('62', '03/2020', '202003'),
            114 => array('62', '03/2021', '202103'),
            115 => array('62', '04', '202004'),
            116 => array('62', '04/2020', '202004'),
            117 => array('62', '05', '202005'),
            118 => array('62', '05/2020', '202005'),
            119 => array('62', '06', '202006'),
            120 => array('62', '06/2020', '202006'),
            121 => array('62', '07', '202007'),
            122 => array('62', '07/2020', '202007'),
            123 => array('62', '08/2020', '202008'),
            124 => array('62', '09', '202009'),
            125 => array('62', '09/2020', '202009'),
            126 => array('62', '1', '202001'),
            127 => array('62', '10', '201910'),
            128 => array('62', '10/2020', '202010'),
            129 => array('62', '11/2020', '202011'),
            130 => array('62', '202103', '202103'),
            131 => array('62', '202104', '202104'),
            132 => array('62', '202105', '202105'),
            133 => array('62', '3', '202003'),
            134 => array('62', '3/2020', '202003'),
            135 => array('62', '4', '202004'),
            136 => array('62', '4/2019', '201904'),
            137 => array('62', '4/2020', '202004'),
            138 => array('62', '8', '202008'),
            139 => array('62', '8/2020', '202008'),
            140 => array('64', '10', '202010'),
            152 => array('64', '3', '202103'),
            153 => array('64', '4', '202104'),
            154 => array('65', '02/2020', '202002'),
            155 => array('65', '03/2020', '202003'),
            156 => array('66', '04/2020', '202004'),
            157 => array('66', '05/2020', '202005'),
            158 => array('66', '4/2020', '202004'),
            159 => array('66', '5/2020', '202005'),
            160 => array('67', '01/2021', '202101'),
            161 => array('67', '02/2021', '202102'),
            162 => array('67', '09/2020', '202009'),
            163 => array('67', '1-2021', '202101'),
            164 => array('67', '10/2020', '202010'),
            165 => array('67', '11/2020', '202011'),
            166 => array('67', '2_2021', '202102'),
            172 => array('68', '01/2021', '202101'),
            173 => array('68', '02/2021', '202102'),
            174 => array('68', '03/2021', '202103'),
            175 => array('68', '1', '202101'),
            176 => array('68', '1/2021', '202101'),
            177 => array('68', '10/2020', '202010'),
            178 => array('68', '11', '202011'),
            179 => array('68', '11/2020', '202011'),
            180 => array('68', '12/2020', '202012'),
            181 => array('68', '2', '202102'),
            195 => array('68', '3', '202103'),
            196 => array('68', '4', '202104'),
            197 => array('73', '2021', '2021'),
            198 => array('77', '122021', '202012'),
            240 => array('20', '02-2019', '201902'),
            241 => array('20', '03-2019', '201903'),
            242 => array('20', '04-2019', '201904'),
            243 => array('20', '05-2019', '201905'),
            244 => array('20', '06-2019', '201906'),
            245 => array('20', '07-2019', '201907'),
            246 => array('13', '07-2018', '201807'),
            247 => array('13', '08-2018', '201808'),
            248 => array('92', '20225', '202205'),
        );

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'test_index_update_bill');
        if ($rs === null) $rs = 0;
        if (!isset($listUpdate[$rs])) {
            Cache::store('redis')->set(env('REDIS_PREFIX') . 'test_index_update_bill', $rs + 1, 60 * 60 * 24);
            echo "not exits " . $rs;
            die;
        }
        $sql = "UPDATE bdc_bills SET cycle_name = '" . $listUpdate[$rs][2] . "' WHERE bdc_building_id=" . $listUpdate[$rs][0] . " AND cycle_name = '" . $listUpdate[$rs][1] . "'";
        $rows = DB::update($sql);
        Cache::store('redis')->set(env('REDIS_PREFIX') . 'test_index_update_bill', $rs + 1, 60 * 60 * 24);
        dd($rows);
    }

    public function resetBill(Request $request)
    {
        Cache::store('redis')->set(env('REDIS_PREFIX') . 'test_index_update_bill', 0);
        echo "this reset index";
        die;
    }
    

    public function testPush(Request $request)
    {
        $token = $request->get('token');
        $typeconfig = $request->get('typeconfig');
        if (!$token) {
            echo 'token null';
            die;
        }
        $data_payload = [];
        $data_payload['message'] = "test";
        $data_payload['title'] = "test push";

        $rs = SendNotifyFCMService::testPush($token, "Dương Còi Pro", $data_payload,$typeconfig);
        dd($rs);
//        $fcm = new SendNotifyFCMService();
//        $fcm->send($message, $to_user, $data_payload, $prioryty , $content_available,$title_noti,$building_id, $data_payload['app_config']);
        echo "this test push || " . $token;
        die;
    }

    public function checkmaxID (Request $request)
    {
        $sql_check="select max(id) as maxID From Transfer_Exportdetail";
        $rs = DB::select(DB::raw($sql_check));
        echo $rs[0]->maxID;
        die;
    }

    public function checkIDmax (Request $request)
    {
        $sql_check="select max(id) as maxID From Transfer_event";
        $rs = DB::select(DB::raw($sql_check));
        echo $rs[0]->maxID;
        die;
    }
    public function checkIDmax_monthly_ticket (Request $request)
    {
        $sql_check="select max(id) as maxID From Tranfer_RegisterMonthlyTicket";
        $rs = DB::select(DB::raw($sql_check));
        echo $rs[0]->maxID;
        die;
    }

    public function checkstatus_program (Request $request)
    {
        $sql_check="select * From logs where user_id = 999 ";
        $rs = DB::select(DB::raw($sql_check));
        echo $rs[0]->content;
        die;
    }

    public function request_query (Request $request)
    {
        $sql_check="select * From logs where user_id = 999 and logs_type='request_update' and Data1 is null order by updated_at desc ";
        $rs = DB::select(DB::raw($sql_check));
        if ($rs)
        {
        return [
            'id' => $rs[0]->id,
            'content' => $rs[0]->content,
        ];
        }
        else echo 'none';
        die;
    }
    public function update_log (Request $request)
    {
        $sql_check="UPDATE logs SET Data1= 'DONE' ,updated_at= now() WHERE id= $request->id ";
        DB::select(DB::raw($sql_check));
        echo 'Done';
        die;
    }

    public function Tranfer_RegisterMonthlyTicket (Request $request)
    {   
        $id = $request->id;
        $sql_check="select id From Tranfer_RegisterMonthlyTicket where id = '$id'";
        $rs = DB::select(DB::raw($sql_check));
        if ($rs) {  
            echo 'Fail';
            die;
        }
        $CompanyID= $request->CompanyID;
        $DepartmentID= $request->DepartmentID;
        $GroupID= $request->GroupID;
        $VehicleIndex= $request->VehicleIndex;
        $Name= $request->Name;
        $CMND= $request->CMND;
        $Tel= $request->Tel;
        $Address= $request->Address;
        $TicketID= $request->TicketID;
        $PlateNumber= $request->PlateNumber;
        $PlateNumber2= $request->PlateNumber2;
        $PlateNumber3= $request->PlateNumber3;
        $VehicleDescription= $request->VehicleDescription;
        $VehicleColor= $request->VehicleColor;
        $VehicleImage= $request->VehicleImage;
        $OwnerImage= $request->OwnerImage;
        $PlateImage= $request->PlateImage;
        $VehicleImagePath= $request->VehicleImagePath;
        $OwnerImagePath= $request->OwnerImagePath;
        $Description= $request->Description;
        $DateCreate= $request->DateCreate;
        $DateStart= $request->DateStart;
        $DateStop= $request->DateStop;
        $CardNumber2= $request->CardNumber2;
        $Email= $request->Email;
        $Finger1= $request->Finger1;
        $Finger2= $request->Finger2;
        $Finger3= $request->Finger3;
        $Finger4= $request->Finger4;
        $Finger5= $request->Finger5;
        $Finger6= $request->Finger6;
        $Finger7= $request->Finger7;
        $Finger8= $request->Finger8;
        $Finger9= $request->Finger9;
        $Finger10= $request->Finger10;
        $IsVehicleInPark_VeLuot= $request->IsVehicleInPark_VeLuot;
        $MoneyEventID= $request->MoneyEventID;
        $nowtime= \Carbon\Carbon::now()->format('Y-m-d H:i:s');
        $sql = "INSERT INTO Tranfer_RegisterMonthlyTicket (id,CompanyID,DepartmentID,GroupID,VehicleIndex,Name,CMND,Tel,Address,TicketID,PlateNumber,PlateNumber2,PlateNumber3,VehicleDescription,VehicleColor,VehicleImage,OwnerImage,PlateImage,VehicleImagePath,OwnerImagePath,Description,DateCreate,DateStart,DateStop,CardNumber2,Email,Finger1,Finger2,Finger3,Finger4,Finger5,Finger6,Finger7,Finger8,Finger9,Finger10,IsVehicleInPark_VeLuot,MoneyEventID,Transfer_Time)
        values('$id','$CompanyID','$DepartmentID','$GroupID','$VehicleIndex','$Name','$CMND','$Tel','$Address','$TicketID','$PlateNumber','$PlateNumber2','$PlateNumber3','$VehicleDescription','$VehicleColor','$VehicleImage','$OwnerImage','$PlateImage','$VehicleImagePath','$OwnerImagePath','$Description','$DateCreate','$DateStart','$DateStop','$CardNumber2','$Email',null,null,null,null,null,null,null,null,null,null,'$IsVehicleInPark_VeLuot','$MoneyEventID',now())";
        $rs = DB::insert($sql);
        echo DB::getPdo()->lastInsertId();
        die;
    }

    public function Confirm_id_Vehicle(Request $request)
    {
        $sql= "SELECT ID FROM Tranfer_RegisterMonthlyTicket ORDER BY DateCreate DESC";
        $rs = DB::select(DB::raw($sql));
        $string= "(";
        foreach ($rs as $data)
        {
            $string .= "'".$data->ID."'" .",";
        }
        $string.= ")";
        if ($rs) {
        return [
            'msg' => '200',
            'content' => $string ,
        ];
        }
        else
        {
        return [
            'msg' => '404',
            'content' => 'Not Found',
        ];
        }
    }
    public function Detected_vehicles(Request $request)
    {
        $ids=$request->id;
        if(!$ids) return 'Thiếu tham số id';
        $sql_check="SELECT id From Detected_vehicles where id = $ids";
        $rs = DB::select(DB::raw($sql_check));
        if ($rs) {  
            return [
                'Code' => 404,
                'msg' => 'Fail',
                'content' => 'Already exist ID:'.$ids
            ];
        }
        $cam_id= $request->cam_id;
        $obj_name=$request->obj_name;
        $image=$request->image;
        $state= $request->state;
        $checking= $request->checking;
        $created_at= $request->created_at;
        $sql= "INSERT INTO Detected_vehicles VALUES($ids,'$cam_id','$obj_name','$image','$state','$checking','$created_at',now())";
        $rs = DB::insert($sql);
        return [
            'Code' => 200,
            'msg' => 'Done',
        ];
    }
    public function Transfer_Exportdetail (Request $request)
    {   
        $id = $request->id;
        $sql_check="select id From Transfer_Exportdetail where id = $id";
        $rs = DB::select(DB::raw($sql_check));
        if ($rs) {  
            echo 'Fail';
            die;
        }
        $IP = $request->ip;
        $DateTime= $request->datetime;
        $LogType= $request->logtype;
        $UserName= $request->username;
        $Detail= $request->detail;
        $Description= $request->description;
        $VehicleImagePath= $request->vehicleimagepath;
        $OwnerImagePath= $request->ownerimagepath;
        $bdc_building_id= $request->bdc_building_id;
        $nowtime= \Carbon\Carbon::now()->format('Y-m-d H:i:s');
        $sql = "insert into Transfer_Exportdetail (id,IP, `DateTime`, LogType, UserName,Detail ,Description,VehicleImagePath,OwnerImagePath,bdc_building_id,transfer_time) 
        values  ($id,'$IP','$DateTime','$LogType', '$UserName','$Detail', '$Description', '$VehicleImagePath','$OwnerImagePath', $bdc_building_id, '$nowtime')";
        $rs = DB::insert($sql);
        echo DB::getPdo()->lastInsertId();
        die;
    }
    public function Transfer_event (Request $request)
    {   
        $id = $request->id;
        $sql_check="select id From Transfer_event where id = $id";
        $rs = DB::select(DB::raw($sql_check));
        if ($rs) {  
            echo 'Fail';
            die;
        }
        $EventDateTime = $request->EventDateTime;
        $CardNumber= $request->CardNumber;
        $TicketName= $request->TicketName;
        $TicketType= $request->TicketType;
        $VehicleTypeDetail= $request->VehicleTypeDetail;
        $VehicleType= $request->VehicleType;
        $MonthlyTicketGroupName= $request->MonthlyTicketGroupName;
        $MonthlyTicketName= $request->MonthlyTicketName;
        $MonthlyTicketTel= $request->MonthlyTicketTel;
        $MonthlyTicketAddress= $request->MonthlyTicketAddress;
        $MonthlyTicketPlateNumber= $request->MonthlyTicketPlateNumber;
        $PlateNumber = $request-> PlateNumber;
        $VehicleImagePath = $request->VehicleImagePath;
        $OwnerImagePath = $request->OwnerImagePath;
        $LaneID = $request->LaneID;
        $LaneDirection = $request->LaneDirection;
        $UserName = $request->UserName;
        $StatusEditPlateNumber = $request-> StatusEditPlateNumber;
        $StatusOverTimeArchive = $request->StatusOverTimeArchive;
        $Description = $request->Description;
        $SyncID = $request->SyncID;
        $IsReported = $request->IsReported;
        $ReportState = $request->ReportState;
        $PCSyncID = $request->PCSyncID;
        $EmailStatus = $request->EmailStatus;
        $compareMonthlyTicketPlateNumber = $request->compareMonthlyTicketPlateNumber;
        $MonthlyTicketEmail = $request->MonthlyTicketEmail;
        $MonthlyTicketDescription = $request->MonthlyTicketDescription;
        $nowtime= \Carbon\Carbon::now()->format('Y-m-d H:i:s');
        $sql = "INSERT INTO buildingcare.Transfer_event
        (id, EventDateTime, CardNumber, TicketName, TicketType, VehicleTypeDetail, VehicleType, MonthlyTicketGroupName, MonthlyTicketName, MonthlyTicketTel, MonthlyTicketAddress, MonthlyTicketPlateNumber, PlateNumber, VehicleImagePath, OwnerImagePath, LaneID, LaneDirection, UserName, StatusEditPlateNumber, StatusOverTimeArchive, `Description`, SyncID, IsReported, ReportState, PCSyncID, EmailStatus, compareMonthlyTicketPlateNumber, MonthlyTicketEmail, MonthlyTicketDescription, Transfer_time, building_id)
        VALUES($id, '$EventDateTime', '$CardNumber','$TicketName', '$TicketType', '$VehicleTypeDetail', '$VehicleType', '$MonthlyTicketGroupName', '$MonthlyTicketName', '$MonthlyTicketTel', '$MonthlyTicketAddress', '$MonthlyTicketPlateNumber', '$PlateNumber', '$VehicleImagePath', '$OwnerImagePath', '$LaneID', '$LaneDirection', '$UserName', '$StatusEditPlateNumber', '$StatusOverTimeArchive', '$Description', '$SyncID', $IsReported, '$ReportState', '$PCSyncID', '$EmailStatus', '$compareMonthlyTicketPlateNumber', '$MonthlyTicketEmail', '$MonthlyTicketDescription', now(), '77');";
        $rs = DB::insert($sql);
        echo DB::getPdo()->lastInsertId();
        die;
    }

    public function Transfer_countvehicle (Request $request)
    {
        $VehicleIn = $request->VehicleIn;
        $VehicleOut= $request->VehicleOut;
        $VehicleIn_Daily= $request->VehicleIn_Daily;
        $VehicleIn_Monthly= $request->VehicleIn_Monthly;
        $VehicleOut_Monthly= $request->VehicleOut_Monthly;
        $VehicleOut_Daily= $request->VehicleOut_Daily;
        $LastTimeUpdateTicket= $request->LastTimeUpdateTicket;
        $LastTimeUpdateLostTicket= $request->LastTimeUpdateLostTicket;
        $LastTimeUpdateMonthlyTicket= $request->LastTimeUpdateMonthlyTicket;
        $LastTimeUpdateMoneyMode= $request->LastTimeUpdateMoneyMode;
        $LastTimeUpdateOption= $request->LastTimeUpdateOption;
        $CurrentDateTime= $request->CurrentDateTime;
        $LastTimeUpdatePermissions= $request->LastTimeUpdatePermissions;
        $LastTimeUpdateUsers= $request->LastTimeUpdateUsers;
        $LastTimeUpdateMonthlyTicket_Finger= $request->LastTimeUpdateMonthlyTicket_Finger;
        $LastTimeUpdateCountVehicleInPark_TheoLoaiXe= $request->LastTimeUpdateCountVehicleInPark_TheoLoaiXe;
        $totalVehicleInPark= $request->totalVehicleInPark;
        $totalVehicleInPark_car= $request->totalVehicleInPark_car;
        $totalVehicleInPark_motor= $request->totalVehicleInPark_motor;
        $totalVehicleInPark_motor_Daily= $request->totalVehicleInPark_motor_Daily;
        $totalVehicleInPark_bicycle= $request->totalVehicleInPark_bicycle;
        $totalVehicleInPark_electricBike= $request->totalVehicleInPark_electricBike;
        $totalVehicleInPark_walk= $request->totalVehicleInPark_walk;
        $totalMoneyInDay= $request->totalMoneyInDay;
        $nowtime= \Carbon\Carbon::now()->format('Y-m-d H:i:s');
        //$sql = "insert into Transfer_countvehicle (VehicleIn,VehicleOut,VehicleIn_Daily,VehicleIn_Monthly,VehicleOut_Daily,VehicleOut_Monthly,LastTimeUpdateTicket,LastTimeUpdateLostTicket,LastTimeUpdateMonthlyTicket,LastTimeUpdateOption,LastTimeUpdateMoneyMode,CurrentDateTime,LastTimeUpdatePermissions,LastTimeUpdateUsers,LastTimeUpdateMonthlyTicket_Finger,LastTimeUpdateCountVehicleInPark_TheoLoaiXe, totalVehicleInPark,totalVehicleInPark_car,totalVehicleInPark_motor,totalVehicleInPark_motor_Daily,totalVehicleInPark_bicycle,totalVehicleInPark_electricBike, totalVehicleInPark_walk,totalMoneyInDay,transfer_time)
        //values ($VehicleIn,$VehicleOut,$VehicleIn_Daily,$VehicleIn_Monthly,$VehicleOut_Daily,$VehicleOut_Monthly,'$LastTimeUpdateTicket','$LastTimeUpdateLostTicket','$LastTimeUpdateMonthlyTicket','$LastTimeUpdateOption','$LastTimeUpdateMoneyMode','$CurrentDateTime','$LastTimeUpdatePermissions','$LastTimeUpdateUsers','$LastTimeUpdateMonthlyTicket_Finger','$LastTimeUpdateCountVehicleInPark_TheoLoaiXe',$totalVehicleInPark,$totalVehicleInPark_car,$totalVehicleInPark_motor,$totalVehicleInPark_motor_Daily,$totalVehicleInPark_bicycle,$totalVehicleInPark_electricBike,$totalVehicleInPark_walk,$totalMoneyInDay,now())";
        $sql ="UPDATE buildingcare.Transfer_countvehicle
        SET VehicleIn=$VehicleIn, VehicleOut=$VehicleOut, VehicleIn_Daily=$VehicleIn_Daily, VehicleIn_Monthly=$VehicleIn_Monthly, VehicleOut_Daily=$VehicleOut_Daily, VehicleOut_Monthly=$VehicleOut_Monthly, LastTimeUpdateTicket='$LastTimeUpdateTicket', LastTimeUpdateLostTicket='$LastTimeUpdateLostTicket', LastTimeUpdateMonthlyTicket='$LastTimeUpdateMonthlyTicket', LastTimeUpdateOption='$LastTimeUpdateOption', LastTimeUpdateMoneyMode='$LastTimeUpdateMoneyMode', CurrentDateTime='$CurrentDateTime', LastTimeUpdatePermissions='$LastTimeUpdatePermissions', LastTimeUpdateUsers='$LastTimeUpdateUsers', LastTimeUpdateMonthlyTicket_Finger='$LastTimeUpdateMonthlyTicket_Finger', LastTimeUpdateCountVehicleInPark_TheoLoaiXe='$LastTimeUpdateCountVehicleInPark_TheoLoaiXe', totalVehicleInPark=$totalVehicleInPark, totalVehicleInPark_car=$totalVehicleInPark_car, totalVehicleInPark_motor=$totalVehicleInPark_motor, totalVehicleInPark_motor_Daily=$totalVehicleInPark_motor_Daily, totalVehicleInPark_bicycle=$totalVehicleInPark_bicycle, totalVehicleInPark_electricBike=$totalVehicleInPark_electricBike, totalVehicleInPark_walk=$totalVehicleInPark_walk, totalMoneyInDay='$totalMoneyInDay', transfer_time=now()
        WHERE id=0;";
        
        $rs = DB::update($sql);
        echo "Done";
        die;
    }
    
    public function update_electricmeter(Request $request)
    {
        if (!$request->id)
        {
            echo "chưa truyền id";
            die;
        }
        $sql="UPDATE buildingcare.bdc_electric_meter
        SET updated_at=now() ";
        if ($request->image)
        {
            $sql += ", images= '".$request->image."'";
        }
        if ($request->before_number)
        {
            $sql += ", before_number= '".$request->before_number."'";
        }
        if ($request->after_number)
        {
            $sql += ", after_number= '".$request->after_number."'";
        }
        if ($request->date_update)
        {
            $chot_so= \Carbon\Carbon::parse($request->date_update)->format('Y-m-d H:i:s');
            $sql += ", date_update= '".$chot_so."'";
        }
        $sql += "WHERE id= ". $request->id."";
        SendTelegram::SupersendTelegramMessage($sql);
        DB::update($sql);
        echo "Done";
        die;
    }
   

    public function duongdemotask(Request $request)
    {
        $query = "SELECT t.*, tas.id as id1 FROM bdc_v2_task t";
        $query .= " LEFT JOIN bdc_v2_task_schedule tas ON t.schedule_id = tas.id";
        $query .= " WHERE t.create_by LIKE '%user_%'";

        $bindings = [];

        if ($request->schedule_id) {
            $query .= " AND t.schedule_id = ?";
            $bindings[] = $request->schedule_id;
        }
        $countQuery = "SELECT COUNT(*) as count FROM ($query) AS subquery";
        $totalCount = DB::select($countQuery, $bindings)[0]->count;
        //$query .= " LIMIT ? OFFSET ?";
       // $bindings[] = $request->limit;
       // $bindings[] = ($request->page - 1) * $request->limit;
        $list = DB::select($query);
        foreach ($list as &$task) {
            $asset_info_id = explode("_", $task->create_by)[1];
            $query = "SELECT * FROM asset_detail WHERE id = $asset_info_id";
            /*$assetDetail = DB::select(DB::raw($query));
            $query1 = "SELECT * FROM asset_category WHERE id = ".$assetDetail[0]->asset_category_id."";
            $assetCategory =  DB::select(DB::raw($query1));
            $query2 = "SELECT * FROM asset_area_office WHERE id = $asset_info_id";
            $office = DB::select(DB::raw($query2));
            $task->asset_name = $assetDetail[0]->name;
            $task->asset_cate_name = $assetCategory[0]->title;
            $task->office = $office[0]->name;
            $task->asset_info = $asset_info_id;*/
            $task->querylord = $query;
        }

        return [
            'count' => $totalCount,
            'list' => $list,
        ];
    }

    public function clearLog(Request $request)
    {
        $table = $request->get("table", false);
        Log::clearLog($table);
        echo "<script>window.close();</script>";
        echo "clear table log ok!";
        die;
    }

    public function addLog(Request $request)
    {
        $rs = Log::log("tandc", "chay den day 1");
        dd($rs);
        die;
    }

    public function viewLog(Request $request)
    {
        $table = $request->get("table", false);
        $auto = $request->get("auto", false);
        $limit = $request->get("limit", 1000);
        if (!$table) {
            echo "thiếu tham số table";
            die;
        }
        if ($auto) header("refresh: 5");
        $dataKeys = Log::getAllKeyLog($table);
        if (!$dataKeys) {
            echo "Không có log!";
            die;
        }
        echo "<a href='/admin/dev/clearLog?table=" . $table . "' target='_blank'>Xóa log</a>";

        $length = $i = count($dataKeys);
        do {
            $key = $dataKeys[$i - 1];
            $data = Log::getLog("tandc", $key);
            if ($data) {
                echo '<p><b style="color: red">' . $data['time'] . ' </b> ' . print_r($data['mess'], true) . '</p>';
            }
            $i--;
        } while ($i > 0 && $limit > ($length - $i));
        die;
    }

    function handleDis($buildingId, $apartmentId)
    {
        $sql = "SELECT * from bdc_debit_detail WHERE bdc_building_id = " . $buildingId . " AND sumery < 0 AND version = 0 AND deleted_at is null ORDER BY id ASC";
        if ($apartmentId) {
            $sql = "SELECT * from bdc_debit_detail WHERE bdc_building_id = " . $buildingId . " AND bdc_apartment_id = " . $apartmentId . " AND sumery < 0 AND version = 0 AND deleted_at is null ORDER BY id ASC";
        }
        $data = DB::select(DB::raw($sql));

        $sql = "SELECT * from bdc_services WHERE name like '%nuoc%' AND deleted_at is null AND bdc_building_id = " . $buildingId;
        $dataNuoc = DB::table(DB::raw("($sql) as tb1"))->pluck('id')->toArray();
        if ($dataNuoc) {
            $sql = "SELECT * from bdc_apartment_service_price WHERE deleted_at is null AND bdc_service_id in (" . implode(",", $dataNuoc) . ")";
            $dataNuoc = DB::table(DB::raw("($sql) as tb1"))->pluck('id')->toArray();
        } else {
            $dataNuoc = [];
        }
        $count = 0;
        $flg = Cache::store('redis')->get(env('REDIS_PREFIX') . 'index_convert_discount', 0);
        if($apartmentId) $flg = 0;
        foreach ($data as $debit) {
            if ($debit->id < $flg) continue;

            if ($count >= 10000) {
                echo "tạm nghỉ";
                echo "</br>";
                break;
            }

            $checkType = $this->sqlSelect("SELECT COALESCE(sum(paid),0) as tongsd from bdc_debit_detail WHERE version > 0 AND deleted_at is null AND bdc_bill_id = " . $debit->bdc_bill_id . " AND bdc_apartment_service_price_id = " . $debit->bdc_apartment_service_price_id);
            $data2 = DB::select(DB::raw("SELECT * from bdc_debit_detail WHERE version > 0 AND deleted_at is null AND bdc_bill_id = " . $debit->bdc_bill_id . " AND bdc_apartment_service_price_id = " . $debit->bdc_apartment_service_price_id));
//            if(!$checkType) dd($debit);

            $checkDis = $this->sqlSelect("SELECT COALESCE(sum(discount),0) as tong from bdc_v2_debit_detail WHERE discount_note = 'convert|" . $debit->id . "' ");

            Log::dump($debit->sumery . " || " . $debit->bdc_apartment_id . " || " . $checkDis->tong);
            if ($checkDis->tong != abs($debit->sumery)) {
                echo "</br>";
                echo "xem lại";
                echo "</br>";
            }
            if ($checkType->tongsd == 0) {
                echo "chưa sử dụng";
                echo "</br>";
                // chuwa thanh toan
                $tienchuatt = abs($debit->sumery);
                if ($checkDis->tong >= $tienchuatt) continue;
//                $tienchuatt = $tienchuatt - $checkDis->tong;
                $i = 0;
                $datee = explode(".", (string)($debit->cycle_name / 100));
                $date = Carbon::createFromDate($datee[0], $datee[1], 1);
                /*do {
                    if($i == 0){
                        $cycle_name = $date->format("Ym");
                    } else {
                        $cycle_name = $date->addMonths(1)->format("Ym");
                    }
                    $rsDis = $this->handleDiscount2($dataNuoc, $debit->bdc_apartment_id, $cycle_name, $tienchuatt, $debit->id);
                    if ($rsDis) {
                        $checkDis = $this->sqlSelect("SELECT COALESCE(sum(discount),0) as tong from bdc_v2_debit_detail WHERE discount_note = 'convert|" . $debit->id . "' ");
                        if ($tienchuatt <= $checkDis->tong) {
                            $i = 100;
                        }
                    }
                    Log::dump($checkDis);
                    $i++;
                } while ($i < 10 && $tienchuatt > 0 && (int) $cycle_name < 202207);*/

                if ($tienchuatt > 0) Log::dump("con thieu 1: " . $tienchuatt);
                if ($tienchuatt <= 0) Log::dump("xong1: ");
            } else { // da tt
                $tiendatt = abs($debit->sumery);
                if ($checkDis->tong >= $tiendatt) continue;
//                if ($debit->sumery != $checkType->tongsd) {
//                    $tiendatt = abs($checkType->tongsd);
//                    if($tiendatt > abs($debit->sumery)) {
//                        $tiendatt = abs($debit->sumery);
//                    }
//                }
//                $tiendatt = $tiendatt - $checkDis->tong;

                $i = 0;
                $datee = explode(".", (string)($debit->cycle_name / 100));
                $date = Carbon::createFromDate($datee[0], $datee[1], 1);
                do {
                    if($i == 0){
                        $cycle_name = $date->format("Ym");
                    } else {
                        $cycle_name = $date/*->subMonth(2)*/ ->addMonths(1)->format("Ym");
                    }
                    if(((int) $cycle_name) > 202206) break;
                    $rsDis = $this->handleDiscount($dataNuoc, $debit->bdc_apartment_id, $cycle_name, $tiendatt, $debit->id);
                    if ($rsDis) {
                        $checkDis = $this->sqlSelect("SELECT COALESCE(sum(discount),0) as tong from bdc_v2_debit_detail WHERE discount_note = 'convert|" . $debit->id . "' ");
                        if ($tiendatt <= $checkDis->tong) {
                            $i = 100;
                        }
                    }
                    $i++;
                    Log::dump($checkDis);
                } while ($i < 35 && ((int) $cycle_name) < 202206);

                if ($tiendatt - $checkDis->tong > 0) {
                    Log::dump("con thieu 2: " . ($tiendatt - $checkDis->tong));

                    echo "</br>";
                    echo "<a href='/admin/dev/forceDiscount?oldKy=1&debitId=" . $debit->id . "' target='_blank'>xu ly dac biet</a>";
                    echo "</br>";
                }
                if ($tiendatt <= 0) Log::dump("xong2: ");
            }
            if($apartmentId) Cache::store('redis')->set(env('REDIS_PREFIX') . 'index_convert_discount', $debit->id, 60 * 60);
            $count++;
        }
    }

    public function convertDiscountNew(Request $request, ReceiptRepository $ReceiptRepository)
    {
        ini_set('memory_limit', '-1');

        $buildingId = $request->get("buildingId", false);
        $apartmentId = $request->get("apartmentId", false);
        if (!$buildingId) {
            echo "thiếu tham số buildingId";
            die;
        }
        $this->handleDis($buildingId, $apartmentId);
        dd("ok");
    }


    function convertDiscountPay($buildingId, $apartmentId = false)
    {
        if ($buildingId) $sql = "SELECT * from bdc_v2_debit_detail WHERE bdc_building_id = " . $buildingId . " AND discount_type = 0 AND discount > 0 AND deleted_at is null ORDER BY id ASC";
        if ($apartmentId) $sql = "SELECT * from bdc_v2_debit_detail WHERE bdc_apartment_id = " . $apartmentId . " AND discount_type = 0 AND discount > 0 AND deleted_at is null ORDER BY id ASC";

        //        $sql = "SELECT * from bdc_receipts WHERE type != 'phieu_thu_truoc' AND type != 'phieu_chi_khac' AND deleted_at is null ORDER BY id ASC";
        $data = DB::select(DB::raw($sql));
        foreach ($data as $item => $debit) {
            $sql = "SELECT sum(paid) as tong from bdc_v2_payment_detail WHERE bdc_debit_detail_id = " . $debit->id;
            $check = DB::select(DB::raw($sql));
            if (!$check[0]->tong) continue;
            if ($debit->sumery >= $check[0]->tong) continue;
            $sql = "SELECT * from bdc_v2_payment_detail WHERE bdc_debit_detail_id = " . $debit->id;
            $dataPayment = DB::select(DB::raw($sql));
            $paid = $debit->discount;
            foreach ($dataPayment as $item => $payment) {
                if ($paid <= 0) continue;
                $paidUpdate = $payment->paid;
                if ($payment->paid > $paid) {
                    $paidUpdate = $payment->paid - $paid;
                    $paid = 0;
                } else {
                    $paidUpdate = 0;
                    $paid = $paid - $payment->paid;
                }
                $sql = "UPDATE bdc_v2_payment_detail SET paid = " . $paidUpdate . " WHERE id = " . $payment->id;
                echo $sql;
                echo "</br>";
                DB::update($sql);
            }
        }
    }

    public function convertDiscountPayment(Request $request, ReceiptRepository $ReceiptRepository)
    {
        dd("fail");
        ini_set('memory_limit', '-1');
        $buildingId = $request->get("buildingId", false);
        $apartmentId = $request->get("apartmentId", false);
        if (!$buildingId && !$apartmentId) {
            echo "thiếu tham số buildingId hoặc apartmentId";
            die;
        }

        $this->convertDiscountPay($buildingId, $apartmentId);
        echo "ok";
    }

    public function convertDiscountPaymentForce(Request $request, ReceiptRepository $ReceiptRepository)
    {
        ini_set('memory_limit', '-1');
        $debitId = $request->get("debitId", false);
        if (!$debitId) {
            echo "thiếu tham số debitId";
            die;
        }
        if ($debitId) $sql = "SELECT * from bdc_v2_debit_detail WHERE id = " . $debitId . " AND discount_type = 0 AND discount > 0 AND deleted_at is null ORDER BY id ASC";
        //        $sql = "SELECT * from bdc_receipts WHERE type != 'phieu_thu_truoc' AND type != 'phieu_chi_khac' AND deleted_at is null ORDER BY id ASC";
        $data = DB::select(DB::raw($sql));
        foreach ($data as $item => $debit) {
            $sql = "SELECT sum(paid) as tong from bdc_v2_payment_detail WHERE bdc_debit_detail_id = " . $debit->id;
            $check = DB::select(DB::raw($sql));
            if (!$check[0]->tong) continue;
            if ($debit->sumery >= $check[0]->tong) continue;
            $sql = "SELECT * from bdc_v2_payment_detail WHERE bdc_debit_detail_id = " . $debit->id;
            $dataPayment = DB::select(DB::raw($sql));
            $i = 1;
            foreach ($dataPayment as $item => $payment) {
                if (count($dataPayment) == $i) {
                    $sql = "UPDATE bdc_v2_payment_detail SET paid = " . $debit->sumery . " WHERE id = " . $payment->id;
                } else {
                    $sql = "UPDATE bdc_v2_payment_detail SET paid = 0 WHERE id = " . $payment->id;
                }
                echo $sql;
                echo "</br>";
                DB::update($sql);
                $i++;
            }
        }
        $this->updateStatPay($data[0]->bdc_apartment_id);
        echo "ok";
    }

    public function convertDiscountPaymentForceNotPaySuccess(Request $request, ReceiptRepository $ReceiptRepository)
    {
        ini_set('memory_limit', '-1');
        $debitId = $request->get("debitId", false);
        if (!$debitId) {
            echo "thiếu tham số debitId";
            die;
        }
        if ($debitId) $sql = "SELECT * from bdc_v2_debit_detail WHERE id = " . $debitId . " AND discount_type = 0 AND discount > 0 AND deleted_at is null ORDER BY id ASC";
        //        $sql = "SELECT * from bdc_receipts WHERE type != 'phieu_thu_truoc' AND type != 'phieu_chi_khac' AND deleted_at is null ORDER BY id ASC";
        $data = DB::select(DB::raw($sql));
        foreach ($data as $item => $debit) {
            $sql = "SELECT sum(paid) as tong from bdc_v2_payment_detail WHERE bdc_debit_detail_id = " . $debit->id;
            $check = DB::select(DB::raw($sql));
            if (!$check[0]->tong) continue;
            $sql = "SELECT * from bdc_v2_payment_detail WHERE bdc_debit_detail_id = " . $debit->id;
            $dataPayment = DB::select(DB::raw($sql));
            $paid = $debit->discount;
            foreach ($dataPayment as $item => $payment) {
                if ($paid <= 0) continue;
                $paidUpdate = $payment->paid;
                if ($payment->paid > $paid) {
                    $paidUpdate = $payment->paid - $paid;
                    $paid = 0;
                } else {
                    $paidUpdate = 0;
                    $paid = $paid - $payment->paid;
                }
                $sql = "UPDATE bdc_v2_payment_detail SET paid = " . $paidUpdate . " WHERE id = " . $payment->id;
                echo $sql;
                echo "</br>";
                DB::update($sql);
            }
        }
        $this->updateStatPay($data[0]->bdc_apartment_id);
        echo "ok";
    }

    public function customCoin(Request $request, ReceiptRepository $ReceiptRepository)
    {
        $logId = $request->get("logId", false);
        $type = $request->get("type", false);
        $coin = $request->get("coin", false);
        if (!$logId || !$type || !$coin) {
            echo "thiếu tham số logId, type, coin";
            die;
        }

        if ($type != "add" && $type != "sub") {
            echo "type ko ho tro";
            die;
        }
        $checkPayment = $this->sqlSelect("SELECT * from bdc_v2_log_coin_detail WHERE id = " . $logId);

        if(!$checkPayment){
            dd("không tồn tại logcoin này ". $logId);
        }

        if ($type == "add") {
            $paidUpdate = $checkPayment->coin + (int)$coin;
        } else {
            $paidUpdate = $checkPayment->coin - (int)$coin;
            if ($paidUpdate < 0) {
                dd("Số tiền quá lớn ko đủ để trừ" . $logId);
            }
        }

        $sql = "UPDATE bdc_v2_log_coin_detail SET coin=".$paidUpdate." WHERE id = " . $logId;
        DB::update($sql);
        $this->updateStatPay($checkPayment->bdc_apartment_id);
        echo "ok";
        die;
    }

    public function customPayment(Request $request, ReceiptRepository $ReceiptRepository)
    {
        $debitId = $request->get("debitId", false);
        $receiptCode = $request->get("receiptCode", false);
        $type = $request->get("type", false);
        $coin = $request->get("coin", false);
        if ($debitId === false || !$receiptCode || !$type || !$coin) {
            echo "thiếu tham số debitId, receiptCode, type, coin";
            die;
        }

        if ($type != "add" && $type != "sub") {
            echo "type ko ho tro";
            die;
        }

        $sql = "SELECT * from bdc_receipts WHERE receipt_code = '" . $receiptCode . "' and type != 'phieu_thu_truoc' AND type != 'phieu_chi_khac' AND deleted_at is null ORDER BY id ASC";

        $checkReceipt = $this->sqlSelect($sql);

        if (!$checkReceipt) {
            dd("Không có phiếu thu này " . $receiptCode);
        }


        $checkPayment = $this->sqlSelect("SELECT * from bdc_v2_payment_detail WHERE bdc_debit_detail_id = " . $debitId . " AND bdc_receipt_id = " . $checkReceipt->id);


        if (!$checkPayment) {
            dd("Không có thanh toan này " . $debitId);
        }


        if ($type == "add") {
            $paidUpdate = $checkPayment->paid + (int)$coin;
        } else {
            $paidUpdate = $checkPayment->paid - (int)$coin;
            if ($paidUpdate < 0) {
                dd("Số tiền quá lớn " . $debitId);
            }
        }

        $sql = "UPDATE bdc_v2_payment_detail SET paid = " . $paidUpdate . " WHERE id = " . $checkPayment->id;
        echo $sql;
        echo "</br>";
        DB::update($sql);
        $this->updateStatPay($checkPayment->bdc_apartment_id);
        dd("ok");
    }


    public function delPayError(Request $request, ReceiptRepository $ReceiptRepository)
    {
        dd(123);
        $dataPayment = DB::select(DB::raw("SELECT * from bdc_v2_payment_detail WHERE bdc_building_id = 72 ORDER BY id DESC LIMIT 3000 OFFSET 0"));
        foreach ($dataPayment as $item ){
            $checkPayment = $this->sqlSelect("SELECT * from bdc_v2_debit_detail WHERE id = " . $item->bdc_debit_detail_id);
            if(!$checkPayment && $item->paid !=0){
                $sql = "UPDATE bdc_v2_payment_detail SET paid = 0 WHERE id = " . $item->id;
                echo $sql;
                echo "</br>";
                DB::update($sql);
                $this->updateStatPay($item->bdc_apartment_id);
            }
        }
        dd("ok");
    }

    public function viewAllPaymentDebit(Request $request, ReceiptRepository $ReceiptRepository)
    {
        $debitId = $request->get("debitId", false);
        if (!$debitId) {
            echo "thiếu tham số debitId";
            die;
        }
        $dataPayment = DB::select(DB::raw("SELECT * from bdc_v2_payment_detail WHERE bdc_debit_detail_id = " . $debitId));
        foreach ($dataPayment as $payment) {
            $sql = "SELECT * from bdc_receipts WHERE id = '" . $payment->bdc_receipt_id . "' AND deleted_at is null ORDER BY id ASC";
            $checkReceipt = $this->sqlSelect($sql);
            Log::dump($checkReceipt);
            echo "</br>";
            echo "</br>";
        }
        dd("ok");
    }

    public function delLogPay(Request $request, ReceiptRepository $ReceiptRepository)
    {
        dd(123);
        LogCoinDetail::where([
            'bdc_building_id' => 68,
            'by' => 0,
        ])->where("note", "like", "%v1->v2%")->forceDelete();

        PaymentDetail::where([
            'bdc_building_id' => 68,
        ])->where("paid", "<", 0)->where("created_at", "<=", "2022-06-05 01:54:30")->forceDelete();
        echo "ok";

        PaymentDetail::where([
            'bdc_building_id' => 68,
        ])->whereIn("id", [225844, 225845, 225886, 225887, 225888, 225889, 226324])->where("created_at", "<=", "2022-06-05 01:54:30")->get();
        echo "ok";
    }

    public function logCoinToPayment(Request $request, ReceiptRepository $ReceiptRepository)
    {
        die;
        $logCoinDetail = LogCoinDetail::where(['from_type' => 2, 'bdc_building_id' => 68])->get();
        foreach ($logCoinDetail as $item => $log) {
            $check = DebitDetail::where(['bdc_building_id' => 68, 'bdc_apartment_id' => $log->bdc_apartment_id, 'bdc_apartment_service_price_id' => $log->bdc_apartment_service_price_id, 'cycle_name' => 202206])->get();
            if (!$check) continue;
            $check = $check[0];
            PaymentDetailRepository::createPayment(
                $log->bdc_building_id,
                $log->bdc_apartment_id,
                $log->bdc_apartment_service_price_id,
                $log->cycle_name,
                $check->id,
                $log->coin,
                $log->created_at,
                0,
                $log->id
            );
        }
        dd("ok");
        $logCoinDetail = LogCoinDetail::where(['from_type' => 2, 'bdc_building_id' => 68])->get();
        foreach ($logCoinDetail as $item => $log) {
            $check = PaymentDetail::where(['bdc_building_id' => 68, 'bdc_apartment_id' => $log->bdc_apartment_id, 'bdc_log_coin_id' => $log->id])->get();
            if ($check && count($check) > 1) {
                $next = true;
                foreach ($check as $item => $pay) {
                    if ($next) {
                        $next = false;
                        echo "sfsf";
                        continue;
                    }
                    PaymentDetail::where(['id' => $pay->id])->forceDelete();
                }
            }
        }
        dd("ok");
    }

    public function updateCycleNamePayment(Request $request, ReceiptRepository $ReceiptRepository)
    {

        ini_set('memory_limit', '-1');

        $sql3 = "SELECT * from bdc_v2_payment_detail WHERE bdc_building_id = 86";
        $data = DB::select(DB::raw($sql3));
        foreach ($data as $a => $payment) {
            $receipt = $this->sqlSelect("SELECT * from bdc_receipts WHERE id = " . $payment->bdc_receipt_id);
            if (Carbon::parse($receipt->create_date)->format('Ym') != $payment->cycle_name) {
                $sql2 = "UPDATE bdc_v2_payment_detail SET cycle_name = '" . Carbon::parse($receipt->create_date)->format('Ym') . "' WHERE id = " . $payment->id;
                DB::update($sql2);

                echo Carbon::parse($receipt->create_date)->format('Ym');
                echo "</br>";
                echo $payment->cycle_name;
                echo "</br>";
                echo " ------------------------- ";
                echo "</br>";
//dd(123);
            }
        }
        dd("ok");
    }

    function handleDiscount($dataNuoc, $bdc_apartment_id, $cycle_name, $tiendatt, $id_dis)
    {
        echo "kỳ " . $cycle_name;
        echo "</br>";
        $totalSub = 0;
        if (!$dataNuoc) {
            $dataNuoc = [0];
        }
        $sql = "SELECT * from bdc_v2_debit_detail WHERE deleted_at is null AND (discount = 0 or discount is null) AND sumery > 0 AND bdc_apartment_id = " . $bdc_apartment_id . " AND cycle_name = " . $cycle_name . " AND bdc_apartment_service_price_id in (" . implode(",", $dataNuoc) . ")";
        $getDebitNuoc = $this->sqlSelect($sql);
        if ($getDebitNuoc) {
            $checkPay = $this->sqlSelect("SELECT COALESCE(sum(paid),0) as tong from bdc_v2_payment_detail WHERE bdc_debit_detail_id = " . $getDebitNuoc->id);
            if ($checkPay->tong < $getDebitNuoc->sumery) { // phải thanh toán hết mới dc
                echo "Giao dịch nước này chưa thanh toán hết ".$getDebitNuoc->id;
                echo "</br>";
                goto getOther;
            }
            $debit = $getDebitNuoc;
            $checkDis = $this->sqlSelect("SELECT COALESCE(sum(discount),0) as tong from bdc_v2_debit_detail WHERE discount_note = 'convert|" . $id_dis . "' ");

            if ($checkPay->tong > $tiendatt - $checkDis->tong) {
                $paidSub = $tiendatt - $checkDis->tong;
            } else {
                $paidSub = $checkPay->tong;
            }

            if ($paidSub <= 0) {
                return $checkDis->tong;
            }

            $sql = "UPDATE bdc_v2_debit_detail SET sumery = " . ($debit->sumery - $paidSub) . " , discount = " . $paidSub . " , discount_type = 0 , discount_note = 'convert|" . $id_dis . "' WHERE id = " . $debit->id;
            echo $sql;
            echo "</br>";
            DB::update($sql);
            $this->convertDiscountPay(false, $bdc_apartment_id);
//            $this->updateStatPay($bdc_apartment_id);
            $totalSub = $totalSub + $paidSub;

            if ($checkDis->tong + $paidSub >= $tiendatt) {
                return $checkDis->tong;
            }

            goto getOther;

        } else {
            getOther:
            $sql = "SELECT * from bdc_v2_debit_detail WHERE deleted_at is null AND (discount = 0 or discount is null) AND sumery > 0 AND bdc_apartment_id = " . $bdc_apartment_id . " AND cycle_name = " . $cycle_name . " AND bdc_apartment_service_price_id not in (" . implode(",", $dataNuoc) . ")";
            $dataDebitOther = DB::select(DB::raw($sql));
            if ($dataDebitOther) {
                foreach ($dataDebitOther as $debit) {
                    $checkPay = $this->sqlSelect("SELECT COALESCE(sum(paid),0) as tong from bdc_v2_payment_detail WHERE bdc_debit_detail_id = " . $debit->id);

                    Log::dump($checkPay->tong);
                    if ($checkPay->tong < $debit->sumery) { // phải thanh toán hết mới dc
                        echo "Giao dịch nước này chưa thanh toán hết ".$debit->id;
                        echo "</br>";
                        continue;
                    }

                    $checkDis = $this->sqlSelect("SELECT COALESCE(sum(discount),0) as tong from bdc_v2_debit_detail WHERE discount_note = 'convert|" . $id_dis . "' ");

                    if ($checkPay->tong > $tiendatt - $checkDis->tong) {
                        $paidSub = $tiendatt - $checkDis->tong;
                    } else {
                        $paidSub = $checkPay->tong;
                    }

                    if ($paidSub <= 0) {
                        return $checkDis->tong;
                    }

                    $sql = "UPDATE bdc_v2_debit_detail SET sumery = " . ($debit->sumery - $paidSub) . " , discount = " . $paidSub . " , discount_type = 0 , discount_note = 'convert|" . $id_dis . "' WHERE id = " . $debit->id;
                    echo $sql;
                    echo "</br>";
                    DB::update($sql);
                    $this->convertDiscountPay(false, $bdc_apartment_id);
//                    $this->updateStatPay($bdc_apartment_id);
                    echo "</br>";
                    $totalSub = $totalSub + $paidSub;

                    if ($checkDis->tong + $paidSub >= $tiendatt) {
                        return $checkDis->tong;
                    }

                }
                return $totalSub;
            } else {
                return false;
            }
        }
        return false;
    }

    function handleDiscount2($dataNuoc, $bdc_apartment_id, $cycle_name, $tiendatt, $id_dis)
    {
        echo "kỳ " . $cycle_name;
        echo "</br>";
        $totalSub = 0;
        if (!$dataNuoc) {
            $dataNuoc = [0];
        }
        $sql = "SELECT * from bdc_v2_debit_detail WHERE deleted_at is null AND (discount = 0 or discount is null) AND sumery > 0 AND bdc_apartment_id = " . $bdc_apartment_id . " AND cycle_name = " . $cycle_name . " AND bdc_apartment_service_price_id in (" . implode(",", $dataNuoc) . ")";
        $getDebitNuoc = $this->sqlSelect($sql);
        if ($getDebitNuoc) {
            $checkPay = $this->sqlSelect("SELECT COALESCE(sum(paid),0) as tong from bdc_v2_payment_detail WHERE bdc_debit_detail_id = " . $getDebitNuoc->id);
            if ($checkPay->tong >= $getDebitNuoc->sumery) { // phải còn chưa thanh toán
                echo "Giao dịch nước này chưa thanh toán hết ".$getDebitNuoc->id;
                echo "</br>";
                goto getOther;
            }
            $debit = $getDebitNuoc;

            $thieuchuatt = $getDebitNuoc->sumery - $checkPay->tong;
            $checkDis = $this->sqlSelect("SELECT COALESCE(sum(discount),0) as tong from bdc_v2_debit_detail WHERE discount_note = 'convert|" . $id_dis . "' ");

            if ($thieuchuatt > ($tiendatt - $checkDis->tong)) {
                $paidSub = $tiendatt - $checkDis->tong;
            } else {
                $paidSub = $thieuchuatt;
            }
            if ($paidSub <= 0) {
                return $checkDis->tong;
            }
            $sql = "UPDATE bdc_v2_debit_detail SET sumery = " . ($debit->sumery - $paidSub) . " , discount = " . $paidSub . " , discount_type = 0 , discount_note = 'convert|" . $id_dis . "' WHERE id = " . $debit->id;
            echo "số tiền trước: " . $debit->sumery;
            echo "</br>";
            echo $sql;
            DB::update($sql);
            echo "</br>";
            $totalSub = $totalSub + $paidSub;

            if ($checkDis->tong + $paidSub >= $tiendatt) {
                return $checkDis->tong;
            }

            goto getOther;

        } else {
            getOther:
            $sql = "SELECT * from bdc_v2_debit_detail WHERE deleted_at is null AND (discount = 0 or discount is null) AND sumery > 0 AND bdc_apartment_id = " . $bdc_apartment_id . " AND cycle_name = " . $cycle_name . " AND bdc_apartment_service_price_id not in (" . implode(",", $dataNuoc) . ")";
            $dataDebitOther = DB::select(DB::raw($sql));
            if ($dataDebitOther) {
                foreach ($dataDebitOther as $debit) {
                    $checkPay = $this->sqlSelect("SELECT COALESCE(sum(paid),0) as tong from bdc_v2_payment_detail WHERE bdc_debit_detail_id = " . $debit->id);

                    Log::dump($checkPay->tong);
                    if ($checkPay->tong >= $debit->sumery) { // phải còn chưa thanh toán
                        echo "Giao dịch nước này chưa thanh toán hết ".$debit->id;
                        echo "</br>";
                        continue;
                    }
                    $checkDis = $this->sqlSelect("SELECT COALESCE(sum(discount),0) as tong from bdc_v2_debit_detail WHERE discount_note = 'convert|" . $id_dis . "' ");

                    $thieuchuatt = $debit->sumery - $checkPay->tong;
                    if ($thieuchuatt > ($tiendatt - $checkDis->tong)) {
                        $paidSub = $tiendatt - $checkDis->tong;
                    } else {
                        $paidSub = $thieuchuatt;
                    }
                    if ($paidSub <= 0) {
                        return $checkDis->tong;
                    }
                    $sql = "UPDATE bdc_v2_debit_detail SET sumery = " . ($debit->sumery - $paidSub) . " , discount = " . $paidSub . " , discount_type = 0 , discount_note = 'convert|" . $id_dis . "' WHERE id = " . $debit->id;
                    echo "số tiền trước: " . $debit->sumery;
                    echo "</br>";
                    echo $sql;
                    DB::update($sql);
                    echo "</br>";
                    $totalSub = $totalSub + $paidSub;

                    if ($checkDis->tong + $paidSub >= $tiendatt) {
                        return $checkDis->tong;
                    }
                }
                return $totalSub;
            } else {
                return false;
            }
        }
        return false;
    }

    function handleDiscountForce($dataNuoc, $bdc_apartment_id, $cycle_name, $tiendatt, $id_dis)
    {
        echo "kỳ " . $cycle_name;
        echo "</br>";
        $totalSub = 0;
        if (!$dataNuoc) {
            $dataNuoc = [0];
        }
        $sql = "SELECT * from bdc_v2_debit_detail WHERE deleted_at is null AND (discount = 0 or discount is null) AND sumery > 0 AND bdc_apartment_id = " . $bdc_apartment_id . " AND cycle_name = " . $cycle_name . " AND bdc_apartment_service_price_id in (" . implode(",", $dataNuoc) . ")";
        $getDebitNuoc = $this->sqlSelect($sql);
        if ($getDebitNuoc) {
            $checkPay = $this->sqlSelect("SELECT COALESCE(sum(paid),0) as tong from bdc_v2_payment_detail WHERE bdc_debit_detail_id = " . $getDebitNuoc->id);
            if ($checkPay->tong <= 0) { // phải co thanh toán mới dc
                echo "chưa có thanh toan1: " . $getDebitNuoc->id;
                echo "</br>";
                goto getOther;
            }
            $debit = $getDebitNuoc;
            $checkDis = $this->sqlSelect("SELECT COALESCE(sum(discount),0) as tong from bdc_v2_debit_detail WHERE discount_note = 'convert|" . $id_dis . "' ");

            if ($checkPay->tong > $tiendatt - $checkDis->tong) {
                $paidSub = $tiendatt - $checkDis->tong;
            } else {
                $paidSub = $checkPay->tong;
            }

            if ($paidSub <= 0) {
                return $checkDis->tong;
            }

            $sql = "UPDATE bdc_v2_debit_detail SET sumery = " . ($debit->sumery - $paidSub) . " , discount = " . $paidSub . " , discount_type = 0 , discount_note = 'convert|" . $id_dis . "' WHERE id = " . $debit->id;
            echo "số tiền trước: " . $debit->sumery;
            echo "</br>";
            echo $sql;
            DB::update($sql);


            // giảm payment
            $sql = "SELECT * from bdc_v2_payment_detail WHERE bdc_debit_detail_id = " . $getDebitNuoc->id;
            $dataPayment = DB::select(DB::raw($sql));
            $paid = $paidSub;
            foreach ($dataPayment as $item => $payment) {
                if ($paid <= 0) {
                    break;
                }
                if ($payment->paid > $paid) {
                    $paidUpdate = $payment->paid - $paid;
                    $paid = 0;
                } else {
                    $paidUpdate = 0;
                    $paid = $paid - $payment->paid;
                }
                $sql = "UPDATE bdc_v2_payment_detail SET paid = " . $paidUpdate . " WHERE id = " . $payment->id;
                echo $sql;
                echo "</br>";
                DB::update($sql);
            }

//            $this->updateStatPay($bdc_apartment_id);
            echo "</br>";
            $totalSub = $totalSub + $paidSub;

            if ($checkDis->tong + $paidSub >= $tiendatt) {
                return $checkDis->tong;
            }

            goto getOther;

        } else {
            getOther:
            $sql = "SELECT * from bdc_v2_debit_detail WHERE deleted_at is null AND (discount = 0 or discount is null) AND sumery > 0 AND bdc_apartment_id = " . $bdc_apartment_id . " AND cycle_name = " . $cycle_name . " AND bdc_apartment_service_price_id not in (" . implode(",", $dataNuoc) . ")";
            $dataDebitOther = DB::select(DB::raw($sql));
            if ($dataDebitOther) {
                foreach ($dataDebitOther as $debit) {
                    $checkPay = $this->sqlSelect("SELECT COALESCE(sum(paid),0) as tong from bdc_v2_payment_detail WHERE bdc_debit_detail_id = " . $debit->id);

                    Log::dump($checkPay->tong);
                    if ($checkPay->tong <= 0) { // phải thanh toán mới dc

                        echo "chưa có thanh toan2: " . $debit->id;
                        echo "</br>";

                        continue;
                    }

                    $checkDis = $this->sqlSelect("SELECT COALESCE(sum(discount),0) as tong from bdc_v2_debit_detail WHERE discount_note = 'convert|" . $id_dis . "' ");

                    if ($checkPay->tong > $tiendatt - $checkDis->tong) {
                        $paidSub = $tiendatt - $checkDis->tong;
                    } else {
                        $paidSub = $checkPay->tong;
                    }

                    if ($paidSub <= 0) {
                        return $checkDis->tong;
                    }

                    // giảm payment
                    $sql = "UPDATE bdc_v2_debit_detail SET sumery = " . ($debit->sumery - $paidSub) . " , discount = " . $paidSub . " , discount_type = 0 , discount_note = 'convert|" . $id_dis . "' WHERE id = " . $debit->id;
                    echo "số tiền trước: " . $debit->sumery;
                    echo "</br>";
                    echo $sql;
                    DB::update($sql);

                    // giảm payment
                    $sql = "SELECT * from bdc_v2_payment_detail WHERE bdc_debit_detail_id = " . $debit->id;
                    $dataPayment = DB::select(DB::raw($sql));
                    $paid = $paidSub;
                    foreach ($dataPayment as $item => $payment) {
                        if ($paid <= 0) {
                            break;
                        }
                        if ($payment->paid > $paid) {
                            $paidUpdate = $payment->paid - $paid;
                            $paid = 0;
                        } else {
                            $paidUpdate = 0;
                            $paid = $paid - $payment->paid;
                        }
                        $sql = "UPDATE bdc_v2_payment_detail SET paid = " . $paidUpdate . " WHERE id = " . $payment->id;
                        echo $sql;
                        echo "</br>";
                        DB::update($sql);
                    }

                    $totalSub = $totalSub + $paidSub;
                    if ($checkDis->tong + $paidSub >= $tiendatt) {
                        return $checkDis->tong;
                    }
                }
                return $totalSub;
            } else {
                return false;
            }
        }
        $this->updateStatPay($bdc_apartment_id);
        return false;
    }

    public function forceDiscount(Request $request, ReceiptRepository $ReceiptRepository)
    {
        $debitId = $request->get("debitId", false);
        $oldKy = $request->get("oldKy", false);
        if (!$debitId) {
            echo "thiếu tham số debitId";
            die;
        }

        $sql = "SELECT * from bdc_debit_detail WHERE id = " . $debitId . " AND sumery < 0 AND version = 0 AND deleted_at is null ORDER BY id ASC";
        $data = DB::select(DB::raw($sql));

        if (!$data) {
            dd("không tìm thấy debit này");
        }
        $buildingId = $data[0]->bdc_building_id;
        $sql = "SELECT * from bdc_services WHERE name like '%nuoc%' AND deleted_at is null AND bdc_building_id = " . $buildingId;
        $dataNuoc = DB::table(DB::raw("($sql) as tb1"))->pluck('id')->toArray();
        if ($dataNuoc) {
            $sql = "SELECT * from bdc_apartment_service_price WHERE deleted_at is null AND bdc_service_id in (" . implode(",", $dataNuoc) . ")";
            $dataNuoc = DB::table(DB::raw("($sql) as tb1"))->pluck('id')->toArray();
        } else {
            $dataNuoc = [];
        }
        $debit = $data[0];

        $checkType = $this->sqlSelect("SELECT COALESCE(sum(paid),0) as tongsd from bdc_debit_detail WHERE version > 0 AND deleted_at is null AND bdc_bill_id = " . $debit->bdc_bill_id . " AND bdc_apartment_service_price_id = " . $debit->bdc_apartment_service_price_id);

        $checkDis = $this->sqlSelect("SELECT COALESCE(sum(discount),0) as tong from bdc_v2_debit_detail WHERE discount_note = 'convert|" . $debit->id . "' ");

        Log::dump($debit->sumery . " || " . $debit->bdc_apartment_id . " || " . $checkDis->tong);
        if ($checkDis->tong != abs($debit->sumery)) {
            echo "</br>";
            echo "xem lại";
            echo "</br>";
        }

        if ($checkType->tongsd == 0) {
            // chua thanh toan
            $tienchuatt = abs($debit->sumery);
            if ($checkDis->tong >= $tienchuatt) dd("xong");
//            $tienchuatt = $tienchuatt - $checkDis->tong;
            $i = 0;
            $datee = explode(".", (string)($debit->cycle_name / 100));
            $date = Carbon::createFromDate($datee[0], $datee[1], 1);
            do {
                $cycle_name = $date/*->subMonth(2)*/ ->addMonths(1)->format("Ym");
                $rsDis = $this->handleDiscount2($dataNuoc, $debit->bdc_apartment_id, $cycle_name, $tienchuatt, $debit->id);
                if ($rsDis) {
                    $checkDis = $this->sqlSelect("SELECT COALESCE(sum(discount),0) as tong from bdc_v2_debit_detail WHERE discount_note = 'convert|" . $debit->id . "' ");
                    if ($tienchuatt <= $checkDis->tong) $i = 100;
                }
                Log::dump($rsDis);
                $i++;
            } while ($i < 10 && (int) $cycle_name < 202207);

            if ($tienchuatt > 0) Log::dump("con thieu 1: " . $tienchuatt);
            if ($tienchuatt <= 0) Log::dump("xong: ");
        } else { // da tt
            $tiendatt = abs($debit->sumery);
            if ($checkDis->tong >= $tiendatt) dd("xong");
           /* if ($debit->sumery != $checkType->tongsd) {
                $tiendatt = abs($checkType->tongsd);
                if ($tiendatt > abs($debit->sumery)) {
                    $tiendatt = abs($debit->sumery);
                }
            }*/
//            $tiendatt = $tiendatt - $checkDis->tong;
            $i = 0;
            $datee = explode(".", (string)($debit->cycle_name / 100));
            $date = Carbon::createFromDate($datee[0], $datee[1], 1);
            if($oldKy) $date->subMonth(2);
            do {
                $cycle_name = $date->addMonths(1)->format("Ym");
                $rsDis = $this->handleDiscountForce($dataNuoc, $debit->bdc_apartment_id, $cycle_name, $tiendatt, $debit->id);
                if ($rsDis) {
                    $checkDis = $this->sqlSelect("SELECT COALESCE(sum(discount),0) as tong from bdc_v2_debit_detail WHERE discount_note = 'convert|" . $debit->id . "' ");
                    if ($tiendatt <= $checkDis->tong) $i = 100;
                }
                Log::dump($rsDis);
                $i++;
            } while ($i < 30 && ((int) $cycle_name) < 202207);

            if ($tiendatt - $checkDis->tong > 0) Log::dump("con thieu 2: " . $tiendatt);
            if ($tiendatt <= 0) Log::dump("xong: ");
        }

        dd("ok");
    }

    public function test1(Request $request, ReceiptRepository $ReceiptRepository)
    {
        $allKey = Redis::zRANGE("warningUpdatePaidByCycleNameFromReceipt", 0, -1);
        dd(count($allKey));

        $data = QueueRedis::getItemForQueue("add_log_action");
        if (!empty($data) && isset($data['type'])) {

            switch ($data['type']) {
                case 1:
                    $rLog = [
                        "tool_id" => $data['toolId'] ?? 0,
                        "action" => $data['action'] ?? "",
                        "by" => $data['by'] ?? 0,
                        "time" => isset($data['time']) ? Carbon::createFromTimestamp($data['time'])->toDateTimeString() : "",
                        "url" => $data['url'] ?? "",
                        "param" => $data['param'] ?? "",
                        "building_id" => $data['buildingId'] ?? 0,
                        "status" => $data['status'] ?? 1,
                        "error" => "",
                        "request_id" => $data['requestId'] ?? 0,
                        "type" => 0,
                        "timestamp" => $data['time'] ?? 0
                    ];
                    if($data['action'] === "view"){
                        break;
                    }
                    $rs = RequestLog::create($rLog);
                    Log::dump($rs);
                    break;
                case 2:
                    $rs = RequestLog::where(["request_id" => $data['requestId']])->update(["error" => $data['mess'], "status" => 0]);
                    Log::dump($rs);
                    break;
                case 3:
                    $dLog = [
                        "table"=>$data['table'] ?? "",
                        "action"=>$data['action'] ?? "",
                        "by"=>$data['by'] ?? 0,
                        "time"=>isset($data['time']) ? Carbon::createFromTimestamp($data['time'])->toDateTimeString() : "",
                        "data_old"=>$data['dataOld'] ?? "",
                        "data_new"=>$data['dataNew'] ?? "",
                        "building_id"=>$data['buildingId'] ?? 0,
                        "request_id"=>$data['requestId'] ?? 0,
                        "sql"=>$data['sql'] ?? "",
                        "row_id"=>$data['rowId'] ?? 0,
                        "timestamp"=>$data['time'] ?? 0
                    ];
                    $rs = DatabaseLog::create($dLog);
                    Log::dump($rs);
                    break;
                default:
                    dd("k ho tro");
                    break;
            }
            dd("done");
        }

        dd(123);
        $buildingId = $request->get("buildingId", false);
        $apartmentId = $request->get("apartmentId", false);
        $receipt_code = $request->get("receiptCode", false);
        if (!$buildingId && !$apartmentId && !$receipt_code) {
            echo "thiếu tham số buildingId hoặc apartmentId hoặc receiptCode";
            die;
        }

//        $building_id = 37;
        if ($buildingId) $sql = "SELECT * from bdc_receipts WHERE bdc_building_id = " . $buildingId . " AND type != 'phieu_thu_truoc' AND type != 'phieu_chi_khac' AND deleted_at is null ORDER BY id ASC";
        if ($apartmentId) $sql = "SELECT * from bdc_receipts WHERE bdc_apartment_id = " . $apartmentId . " and type != 'phieu_thu_truoc' AND type != 'phieu_chi_khac' AND deleted_at is null ORDER BY id ASC";
        if ($receipt_code) $sql = "SELECT * from bdc_receipts WHERE receipt_code = '" . $receipt_code . "' and type != 'phieu_thu_truoc' AND type != 'phieu_chi_khac' AND deleted_at is null ORDER BY id ASC";
        //        $sql = "SELECT * from bdc_receipts WHERE type != 'phieu_thu_truoc' AND type != 'phieu_chi_khac' AND deleted_at is null ORDER BY id ASC";
//        $sql = "SELECT * from bdc_receipts WHERE bdc_apartment_id in (14784,14796,14797,14798,14799,14801,14810,14816,14825,14837,14839,14845,14847,14854,14863,14867,14870,14873,14875,14877,14894,14897,14899,14904,14905,14917,14927,14936,14939,14942,14945,14948,14952,14958,14959,14961,14962,14963,14966,14977,14978,14981,14983,14984,14986,14987,14988,14991,14994,14998,14999,15000,15001,15003,15004,15006,15013,15014,15019,15024,15031,15032,15039,15050,15051,15053,15054,15062,15066,15067,15069,15073,15078,15079,15085,15095,15099,15101,15107,15108,15110,15111,15112,15114,15116,15120,15124,15127,15129,15138,15139,15140,15141,15151,15845,15846,15953,15954) and type != 'phieu_thu_truoc' AND type != 'phieu_chi_khac' AND deleted_at is null ORDER BY id ASC";

        $data = DB::select(DB::raw($sql));
//        $flg = Cache::store('redis')->get(env('REDIS_PREFIX') . 'index_convert', 0);
//        $flg = 0;
        foreach ($data as $item => $receipt) {
            QueueRedis::setItemForQueue('add_queue_convert_payment', $receipt);

            /*if(!$receipt->data || $receipt->id < $flg) continue;
            try {
                $arr_id = [];
                $arr_hachtoan = [];
                $data_debit = unserialize($receipt->data);
                $total_sub = 0;
                $total = 0;
//                dd($data_debit);
                foreach ($data_debit as $item2 => $value2) {
                    $bill_id = isset($value2["bill_id"]) ? $value2["bill_id"] : false;
                    $apartment_service_price_id = isset($value2["apartment_service_price_id"]) ? $value2["apartment_service_price_id"] : false;
                    $service_id = isset($value2["service_id"]) ? $value2["service_id"] : false;
                    $version = isset($value2["version"]) ? $value2["version"] : false;
                    if($apartment_service_price_id){
                        $sql2 = "SELECT * from bdc_debit_detail WHERE bdc_building_id = ".$building_id." AND deleted_at is null AND bdc_bill_id = ".$bill_id." AND bdc_apartment_service_price_id = ".$apartment_service_price_id." AND version = ".$version;
                    }else{
                        $sql2 = "SELECT * from bdc_debit_detail WHERE bdc_building_id = ".$building_id." AND deleted_at is null AND bdc_bill_id = ".$bill_id." AND bdc_service_id = ".$service_id." AND version = ".$version;
                    }
                    $debitDetail = DB::select(DB::raw($sql2));
                    if(!$debitDetail) continue;
                    $debitDetail = $debitDetail[0];
                    $arr_id[] = $debitDetail->id;
                    if($debitDetail->paid == 0) continue;
                    if($debitDetail->paid < 0) $total_sub += abs($debitDetail->paid); else {

                        $checkDebitV2 = DebitDetailRepository::getDebitByApartmentAndServiceAndCyclename($debitDetail->bdc_apartment_id, $debitDetail->bdc_apartment_service_price_id, $debitDetail->cycle_name);

                        if(!$checkDebitV2) {
                            echo " <p style='color: red'>---------------------------------------------------------------------------------------------------- kiểm tra lỗi ko có debit v2 ------".$receipt->id."</p>";
                            echo "</br>";
                            dd($debitDetail);
                        }
                        $sql3 = "SELECT * from bdc_v2_payment_detail WHERE bdc_building_id =  ".$building_id." AND bdc_apartment_service_price_id = ".$debitDetail->bdc_apartment_service_price_id." AND cycle_name = ".$debitDetail->cycle_name." AND bdc_receipt_id = ".$receipt->id." AND created_at <= '".\Carbon\Carbon::now()->subSeconds(3)->format('Y-m-d H:i:s')."'";
                        $checkPayDetail = DB::select(DB::raw($sql3));

                        if(!$checkPayDetail) {
                            PaymentDetailRepository::createPayment(
                                $debitDetail->bdc_building_id,
                                $debitDetail->bdc_apartment_id,
                                $debitDetail->bdc_apartment_service_price_id, // dịch vụ được cấn trừ
                                $debitDetail->cycle_name,
                                $checkDebitV2->id,
                                $debitDetail->paid, // chú ý
                                $receipt->create_date,
                                $receipt->id,
                                0
                            );
                        } else {
                            echo " not insert ";
                            echo "</br>";
                        }

                        $arr_hachtoan[] = $debitDetail;
                        echo "Thanh toán payment : ".$debitDetail->paid;
                        echo "</br>";
                        $total += $debitDetail->paid;
                    }
                }

//                if(false){
                if($total_sub > 0){
//                    echo $total_sub;
//                    echo "</br>";
//                    dd($arr_hachtoan);

                    foreach ($arr_hachtoan as $item => $value) {
                        $debitDetail = $value;
                        if($value->paid > $total_sub) {
                            $paid = $total_sub;
                            $total_sub = 0;
                        } else {
                            $paid = $value->paid;
                            $total_sub -= $value->paid;
                        }
                        $total -= $paid;



                        $_customer = CustomersRespository::findApartmentIdV2($debitDetail->bdc_apartment_id, 0);

                        $note = "v1->v2-" . $receipt->id;
                        $sql3 = "SELECT * from bdc_v2_log_coin_detail WHERE from_type = 4 AND bdc_building_id = " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND cycle_name = " . $debitDetail->cycle_name . " AND note = '" . $note."'";
                        $checkLogCoin = DB::select(DB::raw($sql3));

                        if (!$checkLogCoin) $log = LogCoinDetailRepository::createLogCoin(
                            $debitDetail->bdc_building_id,
                            $debitDetail->bdc_apartment_id,
                            $debitDetail->bdc_apartment_service_price_id, // dịch vụ được cấn trừ
                            $debitDetail->cycle_name,
                            $_customer->pub_user_profile_id,
                            abs($paid), 0, 0, 4, 0, "", $note);

                        echo "<p style='color: orange'>------------------------------------------------------------> Thanh toán log coid : ".$paid."</p>";
                        echo "</br>";
                        if($total_sub <= 0) break;
                    }

                }
                echo " --------------------------------------------------------(".$receipt->cost.")--------------(".$total.")----------------- ";
                echo "</br>";

                $list_next = [91158];
                if ($receipt->cost != $total && $receipt->cost > 0 && !in_array($receipt->id,$list_next)) {
                    echo " <p style='color: red'>---------------------------------------------------------------------------------------------------- kiểm tra lỗi ------".$receipt->id."</p>";
                    echo "</br>";
                    dd($arr_id);
                }
            } catch (Exception $e) {

            }
            Cache::store('redis')->set(env('REDIS_PREFIX') . 'index_convert', $receipt->id,60*60);*/
        }
        echo "run cronjob";
//        Artisan::call("convert_payment_process:cron");
        die;
//        $abc = PaymentDetailRepository::getSumPaidByCycleName(16153,16863,202201);
//        echo $abc;
        Log::info(false, $abc);
//        echo 1;
    }


    public function deletePayment(Request $request, \App\Repositories\BdcReceipts\V2\ReceiptRepository $receiptRepository)
    {
        $apartmentId = $request->get("apartmentId", false);
        if (!$apartmentId) {
            echo "thiếu tham số buildingId hoặc apartmentId";
            die;
        }
        $this->delPay($apartmentId);
        echo "ok";
        die;
    }

    public function LogCoinCus(Request $request, \App\Repositories\BdcReceipts\V2\ReceiptRepository $receiptRepository)
    {

        $logId = $request->get("logId", false);
        $coin = $request->get("coin", false);
        $apartmentId = $request->get("apartmentId", false);
        if (!$logId || $coin === false || !$apartmentId) {
            echo "thiếu tham số logId hoặc Coin apartmentId";
            die;
        }
        $sql = "UPDATE bdc_v2_log_coin_detail SET coin=".$coin." WHERE id = " . $logId;
        DB::update($sql);
        $this->updateStatPay($apartmentId);
        echo "ok";
        die;
    }

    function delPay($apartmentId)
    {
        LogCoinDetail::where(['bdc_apartment_id' => $apartmentId])->where("from_id", "<", 142736)->where("from_type", "!=", 8)->delete(); // xóa
        PaymentDetail::where(['bdc_apartment_id' => $apartmentId])->where("bdc_receipt_id", "<", 142736)->delete(); // xóa
    }

    public function deletePayment2(Request $request, \App\Repositories\BdcReceipts\V2\ReceiptRepository $receiptRepository)
    {
        $receipt_code = $request->get("receipt_code", false);
        if (!$receipt_code) {
            echo "thiếu tham số buildingId hoặc receipt_code";
            die;
        }
        $sql = "SELECT * from bdc_receipts WHERE receipt_code = '" . $receipt_code . "' AND type != 'phieu_thu_truoc' AND type != 'phieu_chi_khac' AND deleted_at is null ORDER BY id ASC";
        $data = DB::select(DB::raw($sql));
        if (!$data) dd("not found");
        foreach ($data as $item => $receipt) {
            $sql = "DELETE FROM bdc_v2_payment_detail WHERE bdc_building_id = 68 AND bdc_receipt_id = " . $receipt->id;
            DB::delete($sql);
        }
        echo "ok";
        die;
    }

    public function paymentShow(Request $request, \App\Repositories\BdcReceipts\V2\ReceiptRepository $receiptRepository)
    {
        $apartmentId = $request->get("apartmentId", false);
        $service_price_id = $request->get("service_price_id", false);
        $cycle_name = $request->get("cycle_name", false);
        if (!$apartmentId || $service_price_id === false || !$cycle_name) {
            echo "thiếu tham số apartmentId hoặc service_price_id hoặc cycle_name";
            die;
        }

        $ls_type_log_coin = [
            1 => "Tiền thừa",
            5 => "Hủy tiền thừa",
            6 => "Điều chỉnh",
            9 => "Chi trả cư dân",
        ];
        $ls_type_add_coin = [
            1 => "Cộng",
            0 => "Trừ",
        ];
        $sumCoin = LogCoinDetailRepository::getPaidLogCoinByCycleName($apartmentId, $service_price_id, $cycle_name, true); // tiền từ phiếu thu log coin
        $sumCoin = $sumCoin->toArray();
        if(count($sumCoin)){
            echo "</br>";
            echo "------------------- tiền thừa từ phiếu thu --------------------";
            echo "</br>";
            foreach ($sumCoin as $item) {
                $item =  (object) $item;
//                dd($item);
                $sql = "SELECT * from bdc_receipts WHERE id = " . $item->from_id;
                $checkReceipt = $this->sqlSelect($sql);
                echo $ls_type_add_coin[$item->type] . "  $item->coin ".$ls_type_log_coin[$item->from_type]." || ".$checkReceipt->receipt_code." || ".$checkReceipt->id;
                echo "</br>";
            }
        }

        $sumPayment = PaymentDetailRepository::getPaidByCycleName($apartmentId, $service_price_id,$cycle_name,true); // tiền từ phiếu thu bảng payment
        $sumPayment = $sumPayment->toArray();
        if(count($sumPayment)){
            echo "</br>";
            echo "------------------- tiền từ phiếu thu đã hạch toán --------------------";
            echo "</br>";
            foreach ($sumPayment as $item) {
                $item =  (object) $item;
                $sql = "SELECT * from bdc_receipts WHERE id = " . $item->bdc_receipt_id;
                $checkReceipt = $this->sqlSelect($sql);
                echo  " Hạch toán:  $item->paid "." || ".$checkReceipt->receipt_code." || ".$checkReceipt->id.($item->bdc_log_coin_id ? " || Có tiền thừa vào ví" : "");
                echo "</br>";
            }
        }


        $addSumCoin = LogCoinDetailRepository::getPaidLogCoinByCycleNameFormType($apartmentId, $service_price_id, $cycle_name, 3, 1); // số coin từ phân bổ được cộng
        $addSumCoin = $addSumCoin->toArray();
        if (count($addSumCoin)) {
            echo "</br>";
            echo "------------------- Phân bổ được cộng --------------------";
            echo "</br>";
            foreach ($addSumCoin as $item) {
                $item = (object)$item;
                echo $ls_type_add_coin[$item->type] . "  $item->coin ";
                echo "</br>";
            }
        }

        $addSumCoin = LogCoinDetailRepository::getPaidLogCoinByCycleNameFormType($apartmentId, $service_price_id, $cycle_name, 3, 0); // số coin từ phân bổ được cộng
        $addSumCoin = $addSumCoin->toArray();
        if (count($addSumCoin)) {
            echo "</br>";
            echo "------------------- Phân bổ bị trừ --------------------";
            echo "</br>";
            foreach ($addSumCoin as $item) {
                $item = (object)$item;
                echo $ls_type_add_coin[$item->type] . "  $item->coin ";
                echo "</br>";
            }
        }

        $addSumCoin = LogCoinDetailRepository::getPaidLogCoinByCycleNameFormTypeIs4($apartmentId, $service_price_id,$cycle_name); // số coin bị trừ cho hạch toán dịch vụ
        $addSumCoin = $addSumCoin->toArray();
        if (count($addSumCoin)) {
            echo "</br>";
            echo "------------------- bị trừ do hạch toán dịch vụ--------------------";
            echo "</br>";
            foreach ($addSumCoin as $item) {
                $item = (object)$item;
                echo $ls_type_add_coin[$item->type] . "  $item->coin ";
                echo "</br>";
            }
        }

        $totalByCycleName = PaymentDetailRepository::getSumPaidByCycleNameFromReceipt($apartmentId, $service_price_id, $cycle_name);

        echo "</br>";
        echo "------------ Tổng tiền: ".$totalByCycleName;
        echo "</br>";
        return "";
    }

    public function warningPaymentShow(Request $request, \App\Repositories\BdcReceipts\V2\ReceiptRepository $receiptRepository)
    {
        $debit_id = $request->get("debit_id", false);
        $handle = $request->get("handle", false);
        if (!$debit_id) {
            echo "thiếu tham số debit_id";
            die;
        }
        $bc = DebitDetail::withTrashed()->where(['id' => $debit_id])->orderBy('id', 'asc')->first();
        if(!$bc) {
            dd("không có hoặc debit này đã bị xóa....");
        }

        if ($handle) {
            DebitDetailRepository::updatePaidByCycleNameFromReceipt($bc->bdc_apartment_id, $bc->bdc_apartment_service_price_id, $bc->cycle_name, false);
        }

        $check = DebitDetailRepository::warningUpdatePaidByCycleNameFromReceipt($bc->bdc_apartment_id, $bc->bdc_apartment_service_price_id, $bc->cycle_name, false, true);

        if (!$check && !$handle) {
            echo "</br>";
            echo "<a href='/admin/dev/warningPaymentShow?debit_id=" . $debit_id ."&handle=1'>Xử lý lỗi</a>";
            echo "</br>";
        }
        return "";
    }

    public function pushStatByDay(Request $request)
    {
        $date = $request->get("date", false); //"2022-09-22"

        if (!$date) {
            dd("thiếu tham số date");
        }

        try {
            $timeCurrent = Carbon::parse($date . " 20:00:00");
        } catch (\Exception $e) {
            dd("Định dạng ngày sai ví dụ: 2022-09-22");
        }
//        $timeCurrent = Carbon::parse($date." 20:00:00");

        $keyCheck = "checkPushStatDate_" . $timeCurrent->toDateString();
        $check = Redis::get($keyCheck);
        if (!$check) {
            Redis::setAndExpire($keyCheck, 1, 60 * 60 * 1);
            $tempCheck = [];
            $timeCurrentTo = $timeCurrent->addDay(1);
            $to = $timeCurrentTo->toDateString();
            $timeCurrentFrom = $timeCurrent->subDay(1);
            $from = $timeCurrentFrom->toDateString();

            Log::dump("from: $from");
            Log::dump("to: $to");

            // bill
            $abc = Bills::withTrashed()->whereBetween('updated_at', [$from, $to])->get();
            foreach ($abc as $item2) {
                $bc = DebitDetail::where(['bdc_bill_id' => $item2->id])->get();
                foreach ($bc as $item) {
                    $dataPush2 = [
                        "apartmentId" => $item->bdc_apartment_id,
                        "service_price_id" => $item->bdc_apartment_service_price_id,
                        "cycle_name" => $item->cycle_name,
                    ];
                    QueueRedis::setItemForQueue('add_queue_stat_payment_', $dataPush2);
                }
            }
            // debit

            $bc = DebitDetail::withTrashed()->whereBetween('updated_at', [$from, $to])->get();
            foreach ($bc as $item) {
                $dataPush2 = [
                    "apartmentId" => $item->bdc_apartment_id,
                    "service_price_id" => $item->bdc_apartment_service_price_id,
                    "cycle_name" => $item->cycle_name,
                ];

                if (!in_array(serialize($dataPush2), $tempCheck)) {
                    QueueRedis::setItemForQueue('add_queue_stat_payment_', $dataPush2);
                }
            }
            // payment

            $bc = PaymentDetail::withTrashed()->whereBetween('updated_at', [$from, $to])->get();
            foreach ($bc as $item) {
                $dataPush2 = [
                    "apartmentId" => $item->bdc_apartment_id,
                    "service_price_id" => $item->bdc_apartment_service_price_id,
                    "cycle_name" => $item->cycle_name,
                ];

                if (!in_array(serialize($dataPush2), $tempCheck)) {
                    QueueRedis::setItemForQueue('add_queue_stat_payment_', $dataPush2);
                    $tempCheck[] = serialize($dataPush2);
                }
            }

            // log coin

            $bc = LogCoinDetail::withTrashed()->whereBetween('updated_at', [$from, $to])->get();
            foreach ($bc as $item) {
                $dataPush2 = [
                    "apartmentId" => $item->bdc_apartment_id,
                    "service_price_id" => $item->bdc_apartment_service_price_id,
                    "cycle_name" => $item->cycle_name,
                ];
                if (!in_array(serialize($dataPush2), $tempCheck)) {
                    QueueRedis::setItemForQueue('add_queue_stat_payment_', $dataPush2);
                    $tempCheck[] = serialize($dataPush2);
                }
            }
        } else {
            dd("đã chạy, chờ 1h sau chạy lại!");
        }
        dd("xong.");
    }


    public function delRecieptConvertLogCoin(Request $request)
    {
        $idLog = $request->get("idLog", false);

        if (!$idLog) {
            dd("thiếu tham số idLog");
        }

        $sql = "SELECT * FROM bdc_v2_log_coin_detail WHERE from_type = 1 AND id = " . $idLog;
        $dataCoin = $this->sqlSelect($sql);
        if (!$dataCoin) {
            dd("không tồn tại idlog");
        }
        $sql = "SELECT * FROM bdc_v2_log_coin_detail WHERE from_type = 5 AND from_id = " . $dataCoin->from_id;

        $check = $this->sqlSelect($sql);

        if ($check) {
            dd("Đã được xử lý");
        }
        $logCoin = $dataCoin;
        $rsSub = BdcCoinRepository::subCoin($logCoin->bdc_building_id, $logCoin->bdc_apartment_id, $logCoin->bdc_apartment_service_price_id, $logCoin->cycle_name, $logCoin->user_id, $logCoin->coin, Auth::user()->id, 5, $logCoin->from_id, $logCoin->note);
        if ($rsSub["status"] !== 0) {
            dd("Hủy thất bại! ví không đủ tiền!");
        }
        $_add_queue_stat_payment = [
            "apartmentId" => $logCoin->bdc_apartment_id,
            "service_price_id" => $logCoin->bdc_apartment_service_price_id,
            "cycle_name" => $logCoin->cycle_name,
        ];
        QueueRedis::setItemForQueue('add_queue_stat_payment_', $_add_queue_stat_payment);

        dd("xong");

    }

        public function test2(Request $request)
        {

            $abc = PromotionApartmentRepository::getPromotionApartment(17489,1282,202212);
            $abc2 = PromotionRepository::getPromotionById($abc->promotion_id);
dd($abc2->toArray());

        dd("ok2");
        $seconds = (int)floor(microtime(true));
        log::dump($seconds);
        DebitDetailRepository::warningUpdatePaidByCycleNameFromReceipt(22604, 108646, "202209" , false, true);

        $seconds = (int)floor(microtime(true));
        log::dump($seconds);

//        $allKey = Redis::zRANGE("warningUpdatePaidByCycleNameFromReceipt", 0, -1);
//        dd($allKey);

        /*foreach ($allKey as $v) {
            Redis::zRem("warningUpdatePaidByCycleNameFromReceipt", $v);
            dd(unserialize($v));
        }
        dd($allKey);*/


        return 1;
        dd(123);
        $seconds = (int)floor(microtime(true));
        $allKey = Redis::zRANGEBYSCORE("warningUpdatePaidByCycleNameFromReceipt", $seconds - 30 * 24 * 60 * 60, $seconds - 15 * 60);
        foreach ($allKey as $v) {
            $data = unserialize($v);
            DebitDetailRepository::warningUpdatePaidByCycleNameFromReceipt($data['apartment_id'], $data['service_price_id'], $data['cycle_name']);
            Redis::zRem("warningUpdatePaidByCycleNameFromReceipt", $v);
            dd($data);
        }

        dd(123);
        $seconds = (int)floor(microtime(true));
        $data = [
            "time" => $seconds,
            "apartment_id" => 2104,
            "service_price_id" => 107381,
            "cycle_name" => 202205,
        ];
        Redis::zAdd("warningUpdatePaidByCycleNameFromReceipt", $seconds, serialize($data)); // add key to list

        dd("ok");
        DebitDetailRepository::warningUpdatePaidByCycleNameFromReceipt(2104, 107381, "202205");
        return 1;
/*        dBug::pushNotification("<strong>Tòa: </strong><pre>17</pre>
<strong>Mã căn: </strong><pre>1235</pre>
<strong>Mã dịch vụ: </strong><pre>107585</pre>
<strong>Kỳ: </strong><pre>202206</pre>
<strong>Lỗi: </strong><pre>số liệu cuối kỳ sai</pre>", \config('app.telegram_warning_pay'));*/

        dd("ok");
//        $bc = DebitDetail::where('discount_note',"like",'%tay%')->orderBy('id', 'asc')->get();
        $sql2 = "SELECT * FROM ( SELECT * from bdc_v2_debit_detail WHERE after_cycle_name < 0 ) as tb1 GROUP BY tb1.bdc_apartment_id";
        $bc = DB::select(DB::raw($sql2));
        foreach ($bc as $item) {
            $this->updateStatPayToQueue($item->bdc_apartment_id);

            /*QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                "apartmentId" => $item->bdc_apartment_id,
                "service_price_id" => $item->bdc_apartment_service_price_id,
                "cycle_name" => $item->cycle_name,
                "update_before_after" => false,
            ]);*/
        }
        dd("ok1");


        /*$sql2 = "SELECT * FROM bdc_receipts WHERE id IN (137269, 137363, 137370, 137372, 137384, 137387, 137389, 137390, 137391, 137392, 137393, 137394, 137395, 137396, 137397, 137398, 137399, 137400, 137401, 137402, 137403, 137404, 137405, 137406, 137407, 137408, 137410, 137411, 137412, 137415, 137417, 137418, 137419, 137420, 137423, 137424, 137425, 137426, 137427, 137428, 137429, 137430, 137431, 137432, 137434, 137435, 137436, 137438, 137441, 137443, 137444, 137446, 137447, 137448, 137449, 137450, 137452, 137453, 137454, 137457, 137458, 137459, 137463, 137464, 137466, 137472, 137473, 137474, 137475, 137477, 137478, 137479, 137480, 137481, 137482, 137483, 137484, 137487, 137488, 137489, 137490, 137491, 137492, 137493, 137494, 137495, 137496, 137497, 137498, 137499, 137500, 137508, 137509, 137510, 137511, 137514, 137517, 137518, 137519, 137520, 137521, 137522, 137523, 137524, 137525, 137526, 137528, 137529, 137530, 137531, 137532, 137533, 137534, 137535, 137536, 137537, 137538, 137539, 137540, 137541, 137542, 137543, 137544, 137545, 137546, 137547, 137549, 137551, 137552, 137553, 137559, 137821, 137862, 137864, 137915, 137927, 137939, 137945, 137963, 137965, 137966, 137967, 137968, 137969, 137970, 137971, 137972, 138062, 138107, 138108, 138109, 138110, 138111, 138113, 138114, 138119, 138121, 138123, 138126, 138128, 138131, 138132, 138133, 138134, 138137, 138138, 138140, 138142, 138143, 138145, 138150, 138152, 138153, 138154, 138155, 138156, 138157, 138158, 138159, 138160, 138161, 138163, 138164, 138166, 138168, 138169, 138175, 138177, 138178, 138179, 138180, 138181, 138182, 138183, 138210, 138212, 138214, 138215, 138222, 138223, 138226, 138228, 138230, 138232, 138234, 138235, 138237, 138238, 138242, 138244, 138247, 138248, 138252, 138275, 138277, 138278, 138279, 138281, 138282, 138287, 138293)";
        $data = DB::select(DB::raw($sql2));
        $flg = Cache::store('redis')->get(env('REDIS_PREFIX') . 'aaa2', 0);
        foreach ($data as $receipt) {
            if ($receipt->id < $flg) continue;
            if ($receipt->data) continue;
            $this->updateStatPay($receipt->bdc_apartment_id);
            Cache::store('redis')->set(env('REDIS_PREFIX') . 'aaa2', $receipt->id, 60 * 60);
//            $abc2 = Receipts::where(['id' => $receipt->id])->delete();
//            $abc = PaymentDetail::where(['bdc_receipt_id' => $receipt->id])->delete();
        }
        dd("ok");*/

        ini_set('memory_limit', '-1');
        $building_id = $request->get("buildingId", false);
        $apartmentId = $request->get("apartmentId", false);

        if ($apartmentId) {
            $getApart = $this->sqlSelect("SELECT * FROM bdc_apartments WHERE deleted_at is null AND id =" . $apartmentId);
            if (!$getApart) {
                dd("không tìm thấy căn hộ này!");
            }
            $building_id = $getApart->building_id;
        }

        if (!$building_id && !$apartmentId) {
            echo "thiếu tham số buildingId hoặc apartmentId";
            die;
        }
        if ($building_id) $sql = "SELECT * from bdc_receipts WHERE bdc_building_id = " . $building_id . " AND type != 'phieu_thu_truoc' AND type != 'phieu_chi_khac' AND deleted_at is null ORDER BY id ASC";
        if ($apartmentId) $sql = "SELECT * from bdc_receipts WHERE bdc_apartment_id = " . $apartmentId . " AND type != 'phieu_thu_truoc' AND type != 'phieu_chi_khac' AND deleted_at is null ORDER BY id ASC";
        $data = DB::select(DB::raw($sql));
//        $flg = Cache::store('redis')->get(env('REDIS_PREFIX') . 'index_convert', 0);
        $flg = 0;
        $total_reciept = 0;
        $total_coin = 0;
        $total_payment = 0;
//        dd($data);
        foreach ($data as $item => $receipt) {
            if (!$receipt->data || $receipt->id < $flg) continue;
            $sql2 = "SELECT sum(paid) as tong from bdc_v2_payment_detail WHERE bdc_building_id = " . $building_id . " AND deleted_at is null AND bdc_receipt_id = " . $receipt->id;
            $sql3 = "SELECT count(id) as demtong from bdc_v2_payment_detail WHERE bdc_building_id = " . $building_id . " AND deleted_at is null AND bdc_receipt_id = " . $receipt->id;
            $debitDetail = DB::select(DB::raw($sql2));
            $countPayment = DB::select(DB::raw($sql3));
            $total_reciept += $receipt->cost;
            $total_payment += $debitDetail[0]->tong;

//dd($countPayment[0]->demtong);

            if ($receipt->cost != $debitDetail[0]->tong || true) {
                echo "</br>";
                echo "Thanh toán ở bảng reciept: " . $receipt->cost . " -- " . $receipt->receipt_code;
                echo "</br>";
                echo "Thanh toán ở bảng payment: " . $debitDetail[0]->tong;
                echo "</br>";
                $arr_id = [];
                $data_debit = unserialize($receipt->data);

                $count = 0;
                $sub = 0;
                foreach ($data_debit as $item2 => $value2) {
                    $bill_id = isset($value2["bill_id"]) ? $value2["bill_id"] : false;
                    $apartment_service_price_id = isset($value2["apartment_service_price_id"]) ? $value2["apartment_service_price_id"] : false;
                    $service_id = isset($value2["service_id"]) ? $value2["service_id"] : false;
                    $version = isset($value2["version"]) ? $value2["version"] : false;
                    $new_debit_id = isset($value2["new_debit_id"]) ? $value2["new_debit_id"] : false;

                    if ($new_debit_id) {
                        $sql2 = "SELECT * from bdc_debit_detail WHERE id = " . $new_debit_id . " AND deleted_at is null  ";
                        $index = 0;
                    } elseif ($apartment_service_price_id) {
                        $sql2 = "SELECT * from bdc_debit_detail WHERE bdc_building_id = " . $building_id . " AND deleted_at is null AND bdc_bill_id = " . $bill_id . " AND bdc_apartment_service_price_id = " . $apartment_service_price_id . " AND version = " . $version;
                        if (isset($listDebitSelect["_" . $building_id . $bill_id . $apartment_service_price_id . $version])) {
                            $index = count($listDebitSelect["_" . $building_id . $bill_id . $apartment_service_price_id . $version]);
                        } else {
                            $index = 0;
                        }
                        $listDebitSelect["_" . $building_id . $bill_id . $apartment_service_price_id . $version][] = 1;
                    } else {
                        $sql2 = "SELECT * from bdc_debit_detail WHERE bdc_building_id = " . $building_id . " AND deleted_at is null AND bdc_bill_id = " . $bill_id . " AND bdc_service_id = " . $service_id . " AND version = " . $version;
                        $check_service = DB::table('bdc_services')->where('id', $service_id)->first();
                        $check_pt_type = $check_service ? strpos($check_service->name, "Phí dịch vụ - Xe") : false;

                        if ($building_id == 71 && $check_service && ($check_service->type == 4 || $check_pt_type !== false)) {
//                            $check_pt = DB::select(DB::raw($sql2)); // check convert phuong tien
                            if ($building_id == 71) {
                                $sql3 = "SELECT * FROM receipt_logs WHERE bill_id = " . $bill_id . " AND bdc_service_id = " . $service_id;
                                $check_reciept_log = DB::select(DB::raw($sql3)); // check convert phuong tien

                                if (!$check_reciept_log) continue;
                                $input_reciept_log = json_decode($check_reciept_log[0]->input);

                                $apartment_service_price_id = $input_reciept_log->apartment_service_price_id;
                                $sql2 = "SELECT * from bdc_debit_detail WHERE bdc_building_id = " . $building_id . " AND deleted_at is null AND bdc_bill_id = " . $bill_id . " AND bdc_apartment_service_price_id = " . $apartment_service_price_id . " AND version = " . $version;


//                                echo "sql ----service_id : ".$service_id;
//                                echo "</br>";
//                                echo "sql ---- : ".$sql2;
//                                echo "</br>";

                            }
                        }

                        if (isset($listDebitSelect["_" . $building_id . $bill_id . $service_id . $version])) {
                            $index = count($listDebitSelect["_" . $building_id . $bill_id . $service_id . $version]);
                        } else {
                            $index = 0;
                        }
                        $listDebitSelect["_" . $building_id . $bill_id . $service_id . $version][] = 1;
                    }
                    $debitDetail2 = DB::select(DB::raw($sql2));
                    if (!$debitDetail2) {
                        dd($sql2);
                        continue;
                    }
                    $debitDetail2 = $debitDetail2[$index] ?? $debitDetail2[0];
                    $arr_id[] = $debitDetail2->id;
                    if ($debitDetail2->paid > 0) $count++;
                    if ($debitDetail2->paid < 0) $sub = $sub + $debitDetail2->paid;
                }

                $note = "v1->v2-" . $receipt->id;
                $sql3 = "SELECT sum(coin) as tong from bdc_v2_log_coin_detail WHERE from_type = 4 AND bdc_building_id = " . $building_id . " AND note = '" . $note . "'";
                $checkLogCoin = DB::select(DB::raw($sql3));

                if ($checkLogCoin) {
                    $total_coin += $checkLogCoin[0]->tong;
                    echo "Thanh toán bảng log: " . $checkLogCoin[0]->tong;
                    echo "</br>";
                }
                echo " ---- Thanh toán tổng : (" . ($debitDetail[0]->tong - $checkLogCoin[0]->tong) . ") ------ (" . $receipt->cost . ")";
                echo "</br>";

                echo "</br>";
                echo "</br>";
                echo "log debitv1 id : " . implode(",", $arr_id);
//                echo "log debitv1 id : ".implode(",",$arr_id);
                if (($debitDetail[0]->tong - $checkLogCoin[0]->tong + $sub) != $receipt->cost) {
                    echo "</br>";
                    echo "log debitv1 id : " . implode(",", $arr_id);
                    echo "</br>";
                    echo " <p style='color: red'>-----------------------------------------------" . ($receipt->cost - ($debitDetail[0]->tong - $checkLogCoin[0]->tong - $sub)) . "----------------------------------------------------- số liệu ko khớp ------" . $receipt->id . " || " . $receipt->receipt_code . "</p>";
                    echo "</br>";
                }
                if ($count != $countPayment[0]->demtong) {
                    echo "</br>";
                    echo " <p style='color: orange'>------------payment: " . ($countPayment[0]->demtong) . "----------------------------------------------------- thiếu chi tiết ------debitv1: " . $count . "  |  " . $receipt->bdc_apartment_id . "</p>";
                    echo "</br>";
                    echo "<a href='/admin/dev/convertPayment?apartmentId=" . $receipt->bdc_apartment_id . "' target='_blank'>update payment</a>";
                    echo "</br>";
                }
            } else {
                $note = "v1->v2-" . $receipt->id;
                $sql3 = "SELECT sum(coin) as tong from bdc_v2_log_coin_detail WHERE from_type = 4 AND bdc_building_id = " . $building_id . " AND note = '" . $note . "'";
                $checkLogCoin = DB::select(DB::raw($sql3));
                if ($checkLogCoin) {
                    $total_coin += $checkLogCoin[0]->tong;
                }
            }
        }

        echo "Thanh toán bảng total_reciept: " . $total_reciept;
        echo "</br>";
        echo "Thanh toán bảng total_payment: " . $total_payment;
        echo "</br>";
        echo "Thanh toán bảng total_coin: " . $total_coin;
        echo "</br>";

        die;
        DebitDetailRepository::updatePaidByCycleNameFromReceipt(6624, 42512, "202108");
        DebitDetailRepository::updatePaidByCycleNameFromReceipt(6624, 42512, "202109");
        DebitDetailRepository::updatePaidByCycleNameFromReceipt(6624, 42512, "202110");
        DebitDetailRepository::updatePaidByCycleNameFromReceipt(6624, 42512, "202111");
//        $dauky_da_thanhtoan = PaymentDetailRepository::getSumPaidByCycleNameFromReceiptCus(16236,74858,"202203","<"); // lấy số liệu đã thanh toán đầu kỳ
//        dd($dauky_da_thanhtoan);
        return 2;
        $abc = PaymentDetailRepository::getSumPaidByCycleNameFromReceiptCus(16236, 0, "202204", "<");
//        $abc = PaymentDetailRepository::getSumPaidByCycleNameFromReceipt(16238, 74869, "202202");
        return $abc;

        // tiền phân bổ from_type = 3
        $addSumCoin = LogCoinDetailRepository::getSumPaidLogCoinByCycleNameFormType(16225, 74815, "202203", 3, 1); // số coin từ phân bổ được cộng
        $SubSumCoin = LogCoinDetailRepository::getSumPaidLogCoinByCycleNameFormType(16225, 74815, "202203", 3, 0); // số coin từ phân bổ bị trừ
//        DebitDetailRepository::updatePaidByCycleNameFromReceipt(16221, 74805, 202203);

        var_dump($addSumCoin - $SubSumCoin);
        return 1;
        die;
        $totalByCycleName = LogCoinDetailRepository::getSumPaidLogCoinByCycleName(16216, 0, "202203");

        var_dump($totalByCycleName);

        return 1;


        $totalByCycleName = PaymentDetailRepository::getSumPaidByCycleNameFromReceipt(16216, false, "202203");
//        $totalPaid = PaymentDetailRepository::getSumPaidByCycleName(16216, 74749, "202203");
        $totalPaid = PaymentDetailRepository::getSumPaidByDebitId(4123, 74749, "202203");
        Log::dump($totalByCycleName);
        Log::dump($totalPaid);

        return null;
//        $abc = BdcCoinRepository::getCoinTotal(16214, false);
        $abc = DebitDetailRepository::getTotalSumeryByCycleNameCus(89, "202209");
//        dd($abc);
        /*$allDebit = DebitDetailRepository::getAllByBillId([215418,215420]);
        if($allDebit) foreach ($allDebit as $item){
            Log::info(false, $item->bdc_building_id);
        }*/
//        $abc = DebitDetailRepository::createDebit(1,1, 1, 1,"202202","","","",12,12000,24000,0);
        Log::info(false, $abc ? (object)$abc->toArray() : $abc);
        echo 1;
    }

    public function cleanCache(Request $request)
    {
        $allKey = RedisLaravel::keys('*get*');
        $allKey && RedisLaravel::del($allKey);
        Log::dump($allKey);
        return null;
    }

    public function updatePaidCoin(Request $request)
    {
        $bc = LogCoinDetail::where(['bdc_building_id' => 94, 'cycle_name' => '202204'])->get();

        foreach ($bc as $item) {
            DebitDetailRepository::updatePaidByCycleNameFromReceipt($item->bdc_apartment_id, $item->bdc_apartment_service_price_id, $item->cycle_name);

            /*$info = ApartmentsRespository::getInfoApartmentsById($item->bdc_apartment_id);
            LogCoinDetail::updateOrInsert(['id' => $item->id], [
                'bdc_building_id' => $info->building_id,
            ]);*/
        }
        return $bc->count();
    }

    public function pushStat(Request $request)
    {
        $apartmentId = $request->get("apartmentId", false);
        $service_price_id = $request->get("service_price_id", false);
        $cycle_name = $request->get("cycle_name", false);
        if ($apartmentId === false || $service_price_id === false  || $cycle_name === false ) {
            echo "Thiếu tham số apartmentId";
            die;
        }

        if(((int) $cycle_name) >= 209001) {
            echo "ok";
            die;
        }

        QueueRedis::setItemForQueue('add_queue_stat_payment_', [
            "apartmentId" => $apartmentId,
            "service_price_id" => $service_price_id,
            "cycle_name" => $cycle_name,
        ]);
        echo "ok";
        die;
    }

    public function pushCreateReceipt(Request $request)
    {
        $receipt_id = $request->get("receipt_id", false);
        if ($receipt_id === false) {
            echo "Thiếu tham số receipt_id";
            die;
        }
        $seconds = (int)floor(microtime(true));
        $bc = PaymentDetail::where(['bdc_receipt_id' => $receipt_id])->orderBy('id', 'asc')->get();
        foreach ($bc as $item) {
            $data = [
                "time" => $seconds,
                "apartment_id" =>  $item->bdc_apartment_id,
                "service_price_id" => $item->bdc_apartment_service_price_id,
                "cycle_name" => $item->cycle_name,
            ];
            Redis::zAdd("warningUpdatePaidByCycleNameFromReceipt", $seconds, serialize($data)); // add key to list
        }

        $bc = LogCoinDetail::where(['from_id' => $receipt_id])->whereIn("from_type", [1,5,6,9])->get();
        foreach ($bc as $item) {
            $data = [
                "time" => $seconds,
                "apartment_id" =>  $item->bdc_apartment_id,
                "service_price_id" => $item->bdc_apartment_service_price_id,
                "cycle_name" => $item->cycle_name,
            ];
            Redis::zAdd("warningUpdatePaidByCycleNameFromReceipt", $seconds, serialize($data)); // add key to list
        }
        echo "ok";
        die;
    }

    public function runStat(Request $request)
    {
        $buildingId = $request->get("buildingId", false);
        $type = $request->get("type", false);
        if (!$buildingId) {
            echo "thiếu tham số buildingId";
            die;
        }
        ini_set('memory_limit', '-1');
        $queue = true;
//        $buildingId = 68;
        $flg = Cache::store('redis')->get(env('REDIS_PREFIX') . 'runStat', 0);

        if ($type) {
//            $sql = "UPDATE bdc_v2_debit_detail SET paid=0,paid_by_cycle_name=0, before_cycle_name=0, after_cycle_name=0 WHERE bdc_building_id = " . $buildingId;
//            DB::update($sql);
            $bc = PaymentDetail::where(['bdc_building_id' => $buildingId])->orderBy('id', 'asc')->get();
        } else {
            $bc = DebitDetail::where(['bdc_building_id' => $buildingId])->orderBy('id', 'asc')->get();
        }

        $i = 0;
        foreach ($bc as $item) {
//            if($i > 1000) break;
//            DebitDetailRepository::updatePaidByCycleNameFromReceipt($item->bdc_apartment_id, $item->bdc_apartment_service_price_id, $item->cycle_name);
            if ($queue) QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                "apartmentId" => $item->bdc_apartment_id,
                "service_price_id" => $item->bdc_apartment_service_price_id,
                "cycle_name" => $item->cycle_name,
                "update_before_after" => false,
            ]); else {
                if ($item->id < $flg) continue;
                DebitDetailRepository::updatePaidByCycleNameFromReceipt($item->bdc_apartment_id, $item->bdc_apartment_service_price_id, $item->cycle_name);
                Cache::store('redis')->set(env('REDIS_PREFIX') . 'runStat', $item->id, 60 * 60);
            }
            $i++;
        }
        /*if ($queue) {
            CronJobManager::create([
                'building_id' => $buildingId,
                'user_id' => 0,
                'signature' => 'create_stat_payment_process:cron',
                'status' => 0
            ]);
        }*/
        return 1;
    }

    public function runStatWarning(Request $request)
    {
        $buildingId = $request->get("buildingId", false);
        $cycle_name = $request->get("cycle_name", false);
        $type = $request->get("type", false);
        if (!$buildingId || !$cycle_name) {
            echo "thiếu tham số buildingId hoặc cycle_name";
            die;
        }
        if (false && $type) {
//            $sql = "UPDATE bdc_v2_debit_detail SET paid=0,paid_by_cycle_name=0, before_cycle_name=0, after_cycle_name=0 WHERE bdc_building_id = " . $buildingId;
//            DB::update($sql);
            $bc = PaymentDetail::where(['bdc_building_id' => $buildingId])->orderBy('id', 'asc')->get();
        } else {
            $bc = DebitDetail::where(['bdc_building_id' => $buildingId, 'cycle_name' => $cycle_name])->orderBy('id', 'asc')->get();
        }
        $seconds = time() - 2 * 60 * 60;
        foreach ($bc as $item) {
            $dataPush2 = [
                "time" => $seconds,
                "apartment_id" =>  $item->bdc_apartment_id,
                "service_price_id" => $item->bdc_apartment_service_price_id,
                "cycle_name" => $item->cycle_name,
            ];
            Redis::zAdd("warningUpdatePaidByCycleNameFromReceipt", $seconds, serialize($dataPush2)); // add key to list
        }
        return 1;
    }

    public function updateDebit2(Request $request)
    {
        try {
        $bdc_apartment_id = $request->get("apartmentId", false);
        if (!$bdc_apartment_id) {
            echo "thiếu tham số apartmentId";
            die;
        }
        $this->updateStatPay($bdc_apartment_id);
        echo 'cập nhập thành công.';
    }
    catch(Exception $e)
    {
        SendTelegram::SupersendTelegramMessage('Fail Update Debit: '.$e->getMessage().':'.$e->getLine());
    }
    }

    function updateStatPay($bdc_apartment_id)
    {
        try {
        $sql = "UPDATE bdc_v2_debit_detail SET paid=0,paid_by_cycle_name=0, before_cycle_name=0, after_cycle_name=0 WHERE bdc_apartment_id = " . $bdc_apartment_id;
        DB::update($sql);
        $bc = PaymentDetail::where(['bdc_apartment_id' => $bdc_apartment_id])->orderBy('id', 'asc')->get();
        foreach ($bc as $item) {
            DebitDetailRepository::updatePaidByCycleNameFromReceipt($item->bdc_apartment_id, $item->bdc_apartment_service_price_id, $item->cycle_name, false);
        }
        $bc = DebitDetail::withTrashed()->where(['bdc_apartment_id' => $bdc_apartment_id])->get();
        foreach ($bc as $item) {
            DebitDetailRepository::updatePaidByCycleNameFromReceipt($item->bdc_apartment_id, $item->bdc_apartment_service_price_id, $item->cycle_name, false);
        }
        $bc = LogCoinDetail::where(['bdc_apartment_id' => $bdc_apartment_id])->get();
        foreach ($bc as $item) {
            DebitDetailRepository::updatePaidByCycleNameFromReceipt($item->bdc_apartment_id, $item->bdc_apartment_service_price_id, $item->cycle_name, false);
        }
        }
            catch(Exception $e)
        {
            SendTelegram::SupersendTelegramMessage('Fail updateStatPay: '.$e->getMessage().':'.$e->getLine());
        }
    }

    function updatePaidByCycleNameFromReceipt_DEV(Request $request)
    {
        try {
            DebitDetailRepository::updatePaidByCycleNameFromReceipt($request->bdc_apartment_id, $request->bdc_apartment_service_price_id, $request->cycle_name, false);
        }
        catch (Exception $e)
        {
            SendTelegram::SupersendTelegramMessage('Fail: '.$e->getMessage().':'.$e->getLine());
        }
    }

    function updateStatPayToQueue($bdc_apartment_id)
    {
        $bc = PaymentDetail::where(['bdc_apartment_id' => $bdc_apartment_id])->orderBy('id', 'asc')->get();
        foreach ($bc as $item) {
            QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                "apartmentId" => $item->bdc_apartment_id,
                "service_price_id" => $item->bdc_apartment_service_price_id,
                "cycle_name" => $item->cycle_name,
                "update_before_after" => false,
            ]);
        }
        $bc = DebitDetail::where(['bdc_apartment_id' => $bdc_apartment_id])->get();
        foreach ($bc as $item) {
            QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                "apartmentId" => $item->bdc_apartment_id,
                "service_price_id" => $item->bdc_apartment_service_price_id,
                "cycle_name" => $item->cycle_name,
                "update_before_after" => false,
            ]);
        }
        $bc = LogCoinDetail::where(['bdc_apartment_id' => $bdc_apartment_id])->get();
        foreach ($bc as $item) {
            QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                "apartmentId" => $item->bdc_apartment_id,
                "service_price_id" => $item->bdc_apartment_service_price_id,
                "cycle_name" => $item->cycle_name,
                "update_before_after" => false,
            ]);
        }
    }

    public function delKey(Request $request)
    {
        $key = $request->get("key", false);
        if (!$key) {
            echo "thiếu tham số key";
            die;
        }
        Redis::del($key);
        dd("ok");
    }

    public function viewQueue(Request $request)
    {
        $key = $request->get("key", false);
        $detail = $request->get("detail", false);
        if (!$key) {
            echo "thiếu tham số key";
            die;
        }
        $count = Redis::getLenList($key);
        if ($detail) {
            $data = Redis::getDataList($key, 0, -1);
            Log::dump($data);
        } else {
            echo "Số lượng: " . $count;
        }
//        $data = Redis::getDataList($key, 0, -1);
//        Log::dump($data);
//        dd($data);
    }

    public function viewKeyRedis(Request $request)
    {
        $key = $request->get("key", false);
        if (!$key) {
            echo "thiếu tham số key";
            die;
        }
        $data = Redis::get($key);
        dd($data);
    }

    public function viewKeyRedis2(Request $request)
    {
        $key = $request->get("key", false);
        if (!$key) {
            echo "thiếu tham số key";
            die;
        }
        $data = Cache::store('redis')->get(env('REDIS_PREFIX') . $key);
        dd($data);
    }

    public function delKey2(Request $request)
    {
        $key = $request->get("key", false);
        if (!$key) {
            echo "thiếu tham số key";
            die;
        }
        Cache::store('redis')->delete(env('REDIS_PREFIX') . $key);
        dd("ok");
    }

    public function xoanophatsinhle(Request $request)
    {
        $buildingId = $request->get("buildingId", false);
        if (!$buildingId) {
            dd("Thiếu tham số buildingId");
        }
        $sql = "SELECT * FROM bdc_v2_payment_detail WHERE deleted_at is null AND paid < 0 AND paid > -1000 AND bdc_building_id = " . $buildingId;
        $data = DB::select(DB::raw($sql));
        foreach ($data as $dataDel) {
            $sql = "SELECT * FROM bdc_v2_payment_detail WHERE paid > 0 AND bdc_debit_detail_id = " . $dataDel->bdc_debit_detail_id;
            $data = DB::select(DB::raw($sql));
            $sub = abs($dataDel->paid);
            foreach ($data as $item) {
                $sql = "SELECT * FROM bdc_v2_log_coin_detail WHERE from_type = 1 AND from_id = " . $item->bdc_receipt_id;
                $dataCoin = DB::select(DB::raw($sql));
                foreach ($dataCoin as $itemCoin) {

                    if ($itemCoin->coin >= $sub) {
                        $coinUpdate = $itemCoin->coin - $sub;
                        $sub = 0;
                    } else {
                        $sub = $sub - $itemCoin->coin;
                        $coinUpdate = 0;
                    }
                    $sql = "UPDATE bdc_v2_log_coin_detail SET coin= " . $coinUpdate . " WHERE id = " . $itemCoin->id;
                    DB::update($sql);
                    if ($sub <= 0) break;
                }
                if ($sub <= 0) break;
            }
            if ($sub != $dataDel->paid) {
                PaymentDetail::where(['id' => $dataDel->id])->delete();
                $sql = "UPDATE bdc_v2_debit_detail SET paid=0,paid_by_cycle_name=0, before_cycle_name=0, after_cycle_name=0 WHERE bdc_apartment_id = " . $dataDel->bdc_apartment_id;
                DB::update($sql);
                $bc = DebitDetail::where(['bdc_apartment_id' => $dataDel->bdc_apartment_id])->get();
                foreach ($bc as $item) {
                    DebitDetailRepository::updatePaidByCycleNameFromReceipt($item->bdc_apartment_id, $item->bdc_apartment_service_price_id, $item->cycle_name, false);
                }
                $bc = PaymentDetail::where(['bdc_apartment_id' => $dataDel->bdc_apartment_id])->orderBy('id', 'asc')->get();
                foreach ($bc as $item) {
                    DebitDetailRepository::updatePaidByCycleNameFromReceipt($item->bdc_apartment_id, $item->bdc_apartment_service_price_id, $item->cycle_name, false);
                }
                echo "ok";
                echo "</br>";
            } else {
                echo "ko tìm thấy log coin để xóa";
                echo "</br>";
            }
        }
        dd("ok");
    }

    public function convertPayment(Request $request)
    {
        $listAllow = [];

        $apartmentId = $request->get("apartmentId", false);
        if (!$apartmentId) {
            echo "thiếu tham số apartmentId";
            die;
        }
        $getApart = $this->sqlSelect("SELECT * FROM bdc_apartments WHERE deleted_at is null AND id =" . $apartmentId);
        if (!$getApart) {
            dd("không tìm thấy căn hộ này!");
        }
        if (!in_array($getApart->building_id, $listAllow)) {
            dd("tòa này ko được hỗ trợ");
        }
        $this->delPay($apartmentId);
        $sql = "SELECT * from bdc_receipts WHERE bdc_apartment_id = " . $apartmentId . " and type != 'phieu_thu_truoc' AND type != 'phieu_chi_khac' AND deleted_at is null ORDER BY id ASC";
        $data = DB::select(DB::raw($sql));
        foreach ($data as $item => $receipt) {
            $this->handleConvert($receipt);
        }
//        $this->handleDis($getApart->building_id, $apartmentId);
        $this->updateStatPay($apartmentId);
        dd("xong");
    }

    function handleConvert($receipt)
    {
        if ($receipt) {
//                        dd(123);
            $receipt = (object)$receipt;
            if (!$receipt->data || !$receipt->id) return;
            $building_id = $receipt->bdc_building_id;

            try {
                $arr_id = [];
                $arr_hachtoan = [];
                $data_debit = unserialize($receipt->data);
                $total_sub = 0;
                $total = 0;
//                dd($data_debit);
                $next = false;
                $listDebitSelect = [];
                foreach ($data_debit as $item2 => $value2) {
                    if ($next) continue;
                    $bill_id = isset($value2["bill_id"]) ? $value2["bill_id"] : false;
                    $apartment_service_price_id = isset($value2["apartment_service_price_id"]) ? $value2["apartment_service_price_id"] : false;
                    $service_id = isset($value2["service_id"]) ? $value2["service_id"] : false;
                    $version = isset($value2["version"]) ? $value2["version"] : false;
                    $new_debit_id = isset($value2["new_debit_id"]) ? $value2["new_debit_id"] : false;
                    if ($new_debit_id) {
                        $sql2 = "SELECT * from bdc_debit_detail WHERE id = " . $new_debit_id . " AND deleted_at is null  ";
                        $index = 0;
                    } elseif ($apartment_service_price_id) {
                        $sql2 = "SELECT * from bdc_debit_detail WHERE bdc_building_id = " . $building_id . " AND deleted_at is null AND bdc_bill_id = " . $bill_id . " AND bdc_apartment_service_price_id = " . $apartment_service_price_id . " AND version = " . $version;
                        if (isset($listDebitSelect["_" . $building_id . $bill_id . $apartment_service_price_id . $version])) {
                            $index = count($listDebitSelect["_" . $building_id . $bill_id . $apartment_service_price_id . $version]);
                        } else {
                            $index = 0;
                        }
                        $listDebitSelect["_" . $building_id . $bill_id . $apartment_service_price_id . $version][] = 1;
                    } else {
                        $sql2 = "SELECT * from bdc_debit_detail WHERE bdc_building_id = " . $building_id . " AND deleted_at is null AND bdc_bill_id = " . $bill_id . " AND bdc_service_id = " . $service_id . " AND version = " . $version;
                        $check_service = DB::table('bdc_services')->where('id', $service_id)->first();
                        $check_pt_type = $check_service ? strpos($check_service->name, "Phí dịch vụ - Xe") : false;
                        if ($building_id == 71 && $check_service && ($check_service->type == 4 || $check_pt_type !== false)) {
                            $sql3 = "SELECT * FROM receipt_logs WHERE bill_id = " . $bill_id . " AND bdc_service_id = " . $service_id;
                            $check_reciept_log = DB::select(DB::raw($sql3)); // check convert phuong tien
                            if (!$check_reciept_log) continue;
                            $input_reciept_log = json_decode($check_reciept_log[0]->input);
                            $apartment_service_price_id = $input_reciept_log->apartment_service_price_id;
                            $sql2 = "SELECT * from bdc_debit_detail WHERE bdc_building_id = " . $building_id . " AND deleted_at is null AND bdc_bill_id = " . $bill_id . " AND bdc_apartment_service_price_id = " . $apartment_service_price_id . " AND version = " . $version;
                        }

                        if (isset($listDebitSelect["_" . $building_id . $bill_id . $service_id . $version])) {
                            $index = count($listDebitSelect["_" . $building_id . $bill_id . $service_id . $version]);
                        } else {
                            $index = 0;
                        }
                        $listDebitSelect["_" . $building_id . $bill_id . $service_id . $version][] = 1;
                    }

                    $debitDetail = DB::select(DB::raw($sql2));
                    if (!$debitDetail) continue;

//                                $debitDetail = $debitDetail[$index];
                    $debitDetail = $debitDetail[$index] ?? $debitDetail[0];

                    $arr_id[] = $debitDetail->id;

                    if ($debitDetail->paid == 0) continue;

                    if ($debitDetail->paid > 0) { // bill bỏ qua
                        $sql3 = "SELECT * from bdc_bills WHERE id =  " . $debitDetail->bdc_bill_id . " AND deleted_at is null ";
                        $checkBill = DB::select(DB::raw($sql3));
                        if (!$checkBill) continue;
                        $debitbillCheck = $checkBill[0];
                        if (!($debitbillCheck->status >= -2)) continue;
                    }

                    /*QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                        "apartmentId" => $debitDetail->bdc_apartment_id,
                        "service_price_id" => $debitDetail->bdc_apartment_service_price_id,
                        "cycle_name" => $debitDetail->cycle_name,
                    ]);*/

                    if ($receipt->cost < 0) { // bỏ túi đồng lẻ
                        $sql3 = "SELECT * from bdc_v2_payment_detail WHERE bdc_building_id =  " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND cycle_name = " . $debitDetail->cycle_name . " AND bdc_receipt_id = " . $receipt->id . " AND created_at <= '" . \Carbon\Carbon::now()->subSeconds(3)->format('Y-m-d H:i:s') . "'";
                        $checkPayDetail = DB::select(DB::raw($sql3));
                        if (!$checkPayDetail) {

                            // tìm loại bỏ coin thừa
                            $sql3 = "SELECT * from bdc_v2_log_coin_detail WHERE from_type = 1 AND bdc_building_id = " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND coin = " . abs($receipt->cost);
                            $checkLogCoin = DB::select(DB::raw($sql3));
                            if ($checkLogCoin) { // nếu có thì triệt tiêu
                                $checkLogCoin = $checkLogCoin[0];
                                LogCoinDetail::where(['id' => $checkLogCoin->id])->delete(); // xóa
                            } else {
                                $checkDebitV2 = DebitDetailRepository::getDebitByApartmentAndServiceAndCyclename($debitDetail->bdc_apartment_id, $debitDetail->bdc_apartment_service_price_id, $debitDetail->cycle_name, false);
                                PaymentDetailRepository::createPayment(
                                    $debitDetail->bdc_building_id,
                                    $debitDetail->bdc_apartment_id,
                                    $debitDetail->bdc_apartment_service_price_id, // dịch vụ được cấn trừ
                                    Carbon::parse($receipt->create_date)->format('Ym'),
                                    $checkDebitV2 ? $checkDebitV2->id : 0,
                                    $receipt->cost, // chú ý
                                    $receipt->create_date,
                                    $receipt->id,
                                    0
                                );
                            }

                        } else {
                            $checkPayDetail = $checkPayDetail[0];
                            if ($checkPayDetail->bdc_debit_detail_id == 0) {
                                $checkDebitV2 = DebitDetailRepository::getDebitByApartmentAndServiceAndCyclename($debitDetail->bdc_apartment_id, $debitDetail->bdc_apartment_service_price_id, $debitDetail->cycle_name, false);
                                if ($checkDebitV2) {
                                    $sql = "UPDATE bdc_v2_payment_detail SET bdc_debit_detail_id = " . $checkDebitV2->id . " WHERE id = " . $checkPayDetail->id;
                                    DB::update($sql);
                                }
                            }
                            $sql3 = "SELECT * from bdc_v2_log_coin_detail WHERE from_type = 1 AND bdc_building_id = " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND coin = " . abs($receipt->cost);
                            $checkLogCoin = DB::select(DB::raw($sql3));
                            if ($checkLogCoin) { // nếu có thì triệt tiêu
                                $checkLogCoin = $checkLogCoin[0];
                                LogCoinDetail::where(['id' => $checkLogCoin->id])->delete(); // xóa
                                PaymentDetail::where(['id' => $checkPayDetail->id])->delete(); // xóa
                            }
                        }

                        /*$note = "v1->v2-" . $receipt->id;
                        $sql3 = "SELECT * from bdc_v2_log_coin_detail WHERE from_type = 4 AND bdc_building_id = " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND cycle_name = " . $debitDetail->cycle_name . " AND note = '" . $note . "'";
                        $checkLogCoin = DB::select(DB::raw($sql3));
                        $_customer = CustomersRespository::findApartmentIdV2($debitDetail->bdc_apartment_id, 0);

                        if (!$checkLogCoin) $log = LogCoinDetailRepository::createLogCoin(
                            $debitDetail->bdc_building_id,
                            $debitDetail->bdc_apartment_id,
                            $debitDetail->bdc_apartment_service_price_id, // dịch vụ được cấn trừ
                            $debitDetail->cycle_name,
                            $_customer ? $_customer->pub_user_profile_id : "",
                            abs($receipt->cost), 0, 0, 4, 0, "", $note);*/

                        $next = true;
                        continue;
                    }

                    if ($debitDetail->paid < 0 && $debitDetail->sumery < 0) {
                        continue;
                    }

                    if ($debitDetail->paid < 0) $total_sub += abs($debitDetail->paid); else {

                        $checkDebitV2 = DebitDetailRepository::getDebitByApartmentAndServiceAndCyclename($debitDetail->bdc_apartment_id, $debitDetail->bdc_apartment_service_price_id, $debitDetail->cycle_name, false);

                        if (!$checkDebitV2) {
                            echo " <p style='color: red'>---------------------------------------------------------------------------------------------------- kiểm tra lỗi ko có debit v2 ------" . $receipt->id . "</p>";
                            echo "</br>";

                            $paid = $debitDetail->paid;
                            $paidCoin = 0;
                            if ($debitDetail->new_sumery < 0) {
                                $paid = ($debitDetail->paid + $debitDetail->new_sumery);
                                $paidCoin = abs($debitDetail->new_sumery);
                                if ($paid < 0) { // nạp thêm tiền
                                    $paid = 0;
                                    $paidCoin = $debitDetail->paid;
                                }
                            }

                            if ($paid != 0) PaymentDetailRepository::createPayment(
                                $debitDetail->bdc_building_id,
                                $debitDetail->bdc_apartment_id,
                                $debitDetail->bdc_apartment_service_price_id, // dịch vụ được cấn trừ
                                Carbon::parse($receipt->create_date)->format('Ym'),
                                0,
                                $paid, // chú ý
                                $receipt->create_date,
                                $receipt->id,
                                0
                            );

                            if ($debitDetail->new_sumery < 0) {
                                $note = "v1->v2-" . $receipt->id;
                                $sql3 = "SELECT * from bdc_v2_log_coin_detail WHERE from_type = 1 AND bdc_building_id = " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND cycle_name = " . $debitDetail->cycle_name . " AND note = '" . $note . "'";
                                $checkLogCoin = DB::select(DB::raw($sql3));
                                if (!$checkLogCoin) {
                                    $_customer = CustomersRespository::findApartmentIdV2($debitDetail->bdc_apartment_id, 0);
                                    LogCoinDetailRepository::createLogCoin(
                                        $debitDetail->bdc_building_id,
                                        $debitDetail->bdc_apartment_id,
                                        $debitDetail->bdc_apartment_service_price_id, // dịch vụ được cấn trừ
                                        $debitDetail->cycle_name,
                                        $_customer ? $_customer->pub_user_profile_id : "",
                                        $paidCoin, 1, 0, 1, $receipt->id, "", $note);
                                }
                            }

                            continue;
                        }

                        $sql3 = "SELECT * from bdc_v2_payment_detail WHERE bdc_building_id =  " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND cycle_name = " . $debitDetail->cycle_name . " AND bdc_receipt_id = " . $receipt->id . " AND created_at <= '" . \Carbon\Carbon::now()->subSeconds(3)->format('Y-m-d H:i:s') . "'";
                        $checkPayDetail = DB::select(DB::raw($sql3));

                        if (!$checkPayDetail) {
                            $paid = $debitDetail->paid;
                            $paidCoin = 0;
                            if ($debitDetail->new_sumery < 0) {
                                $paid = ($debitDetail->paid + $debitDetail->new_sumery);
                                $paidCoin = abs($debitDetail->new_sumery);
                                if ($paid < 0) { // nạp thêm tiền
                                    $paid = 0;
                                    $paidCoin = $debitDetail->paid;
                                }
                            }

                            if ($paid != 0) PaymentDetailRepository::createPayment(
                                $debitDetail->bdc_building_id,
                                $debitDetail->bdc_apartment_id,
                                $debitDetail->bdc_apartment_service_price_id, // dịch vụ được cấn trừ
                                Carbon::parse($receipt->create_date)->format('Ym'),
                                $checkDebitV2->id,
                                $paid, // chú ý
                                $receipt->create_date,
                                $receipt->id,
                                0
                            );

                            if ($debitDetail->new_sumery < 0) {
                                $note = "v1->v2-" . $receipt->id;
                                $sql3 = "SELECT * from bdc_v2_log_coin_detail WHERE from_type = 1 AND bdc_building_id = " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND cycle_name = " . $debitDetail->cycle_name . " AND note = '" . $note . "'";
                                $checkLogCoin = DB::select(DB::raw($sql3));
                                if (!$checkLogCoin) {
                                    $_customer = CustomersRespository::findApartmentIdV2($debitDetail->bdc_apartment_id, 0);
                                    LogCoinDetailRepository::createLogCoin(
                                        $debitDetail->bdc_building_id,
                                        $debitDetail->bdc_apartment_id,
                                        $debitDetail->bdc_apartment_service_price_id, // dịch vụ được cấn trừ
                                        $debitDetail->cycle_name,
                                        $_customer ? $_customer->pub_user_profile_id : "",
                                        $paidCoin, 1, 0, 1, $receipt->id, "", $note);
                                }
                            }
                        } else {
                            if ($debitDetail->new_sumery < 0) {

                                $paid = ($debitDetail->paid + $debitDetail->new_sumery);
                                $paidCoin = abs($debitDetail->new_sumery);
                                if ($paid < 0) { // nạp thêm tiền
                                    $paid = 0;
                                    $paidCoin = $debitDetail->paid;
                                }

                                $checkPayDetail = $checkPayDetail[0];
                                $sql = "UPDATE bdc_v2_payment_detail SET paid = " . $paid . " WHERE id = " . $checkPayDetail->id;
                                if ($paid != $checkPayDetail->paid) DB::update($sql);


                                $note = "v1->v2-" . $receipt->id;
                                $sql3 = "SELECT * from bdc_v2_log_coin_detail WHERE from_type = 1 AND bdc_building_id = " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND cycle_name = " . $debitDetail->cycle_name . " AND note = '" . $note . "'";
                                $checkLogCoin = DB::select(DB::raw($sql3));
                                if (!$checkLogCoin) {
                                    $_customer = CustomersRespository::findApartmentIdV2($debitDetail->bdc_apartment_id, 0);
                                    LogCoinDetailRepository::createLogCoin(
                                        $debitDetail->bdc_building_id,
                                        $debitDetail->bdc_apartment_id,
                                        $debitDetail->bdc_apartment_service_price_id, // dịch vụ được cấn trừ
                                        $debitDetail->cycle_name,
                                        $_customer ? $_customer->pub_user_profile_id : "",
                                        $paidCoin, 1, 0, 1, $receipt->id, "", $note);
                                } else {
                                    $checkLogCoin = $checkLogCoin[0];
                                    $sql = "UPDATE bdc_v2_log_coin_detail SET coin = " . $paidCoin . " WHERE id = " . $checkLogCoin->id;
                                    if ($paidCoin != $checkLogCoin->coin) DB::update($sql);
                                }
                            }
//                                        echo " not insert ";
//                                        echo "</br>";
                        }

                        $arr_hachtoan[] = $debitDetail;
//                                    echo "Thanh toán payment : " . $debitDetail->paid;
//                                    echo "</br>";
                        $total += $debitDetail->paid;
                    }
                }

//                if(false){
                if ($total_sub > 0) {
//                    echo $total_sub;
//                    echo "</br>";
//                    dd($arr_hachtoan);

                    foreach ($arr_hachtoan as $item => $value) {
                        $debitDetail = $value;
                        if ($value->paid > $total_sub) {
                            $paid = $total_sub;
                            $total_sub = 0;
                        } else {
                            $paid = $value->paid;
                            $total_sub -= $value->paid;
                        }
                        $total -= $paid;


                        $_customer = CustomersRespository::findApartmentIdV2($debitDetail->bdc_apartment_id, 0);

                        $note = "v1->v2-" . $receipt->id;
                        $sql3 = "SELECT * from bdc_v2_log_coin_detail WHERE from_type = 4 AND bdc_building_id = " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND cycle_name = " . $debitDetail->cycle_name . " AND note = '" . $note . "'";
                        $checkLogCoin = DB::select(DB::raw($sql3));

                        if (!$checkLogCoin) $log = LogCoinDetailRepository::createLogCoin(
                            $debitDetail->bdc_building_id,
                            $debitDetail->bdc_apartment_id,
                            $debitDetail->bdc_apartment_service_price_id, // dịch vụ được cấn trừ
                            $debitDetail->cycle_name,
                            $_customer ? $_customer->pub_user_profile_id : "",
                            abs($paid), 0, 0, 4, 0, "", $note);

//                                    echo "<p style='color: orange'>------------------------------------------------------------> Thanh toán log coid : " . $paid . "</p>";
//                                    echo "</br>";
                        if ($total_sub <= 0) break;
                    }

                }
//                            echo " --------------------------------------------------------(" . $receipt->cost . ")--------------(" . $total . ")----------------- ";
//                            echo "</br>";

                $list_next = [91158];
                if ($receipt->cost != $total && $receipt->cost > 0 && !in_array($receipt->id, $list_next)) {
                    echo " <p style='color: red'>---------------------------------------------------------------------------------------------------- kiểm tra lỗi ------" . $receipt->id . "</p>";
                    echo "</br>";
                    return;
                }
            } catch (Exception $e) {

            }
        }

    }

    public function xoanophatsinh(Request $request)
    {
        $debitId = $request->get("debitId", false);
        if (!$debitId) {
            dd("Thiếu tham số debitId");
        }

        $sql = "SELECT * FROM bdc_v2_payment_detail WHERE deleted_at is null AND paid < 0 AND bdc_debit_detail_id = " . $debitId;

        $dataDel = $this->sqlSelect($sql);

        if (!$dataDel) {
            dd("debit này không có tiền thừa lẻ");
        }

        $sql = "SELECT * FROM bdc_v2_payment_detail WHERE paid > 0 AND bdc_debit_detail_id = " . $debitId;
        $data = DB::select(DB::raw($sql));
        $sub = abs($dataDel->paid);
        foreach ($data as $item) {
            $sql = "SELECT * FROM bdc_v2_log_coin_detail WHERE from_type = 1 AND from_id = " . $item->bdc_receipt_id;
            $dataCoin = DB::select(DB::raw($sql));
            foreach ($dataCoin as $itemCoin) {

                if ($itemCoin->coin >= $sub) {
                    $coinUpdate = $itemCoin->coin - $sub;
                    $sub = 0;
                } else {
                    $sub = $sub - $itemCoin->coin;
                    $coinUpdate = 0;
                }
                $sql = "UPDATE bdc_v2_log_coin_detail SET coin= " . $coinUpdate . " WHERE id = " . $itemCoin->id;
                DB::update($sql);
                if ($sub <= 0) break;
            }
            if ($sub <= 0) break;
        }
        if ($sub != $dataDel->paid) {
            PaymentDetail::where(['id' => $dataDel->id])->delete();
            $sql = "UPDATE bdc_v2_debit_detail SET paid=0,paid_by_cycle_name=0, before_cycle_name=0, after_cycle_name=0 WHERE bdc_apartment_id = " . $dataDel->bdc_apartment_id;
            DB::update($sql);
            $bc = DebitDetail::where(['bdc_apartment_id' => $dataDel->bdc_apartment_id])->get();
            foreach ($bc as $item) {
                DebitDetailRepository::updatePaidByCycleNameFromReceipt($item->bdc_apartment_id, $item->bdc_apartment_service_price_id, $item->cycle_name, false);
            }
            $bc = PaymentDetail::where(['bdc_apartment_id' => $dataDel->bdc_apartment_id])->orderBy('id', 'asc')->get();
            foreach ($bc as $item) {
                DebitDetailRepository::updatePaidByCycleNameFromReceipt($item->bdc_apartment_id, $item->bdc_apartment_service_price_id, $item->cycle_name, false);
            }
            dd("done");
        } else {
            dd("ko tìm thấy log coin để xóa");
        }
    }

    public function userConvert(Request $request)
    {
        log::info("tandc","userConvert");

        $list_email_next = ["daonam0304@gmail.com", "Phamtranquangha@gmail.com", "thuytt@gmail.com", "leminh1307@yahoo.com", "lekimthanhviwase@gmail.com", "nam.vuduc1977@gmail.com", "Cuongdongy68@gmail.com", "tannv.ptit@gmail.com", "saosa2209@gmail.com", "shanenguyen.04@gmail.com", "trung3110@gmail.com", "thanhloc1806@gmail.com", "jun@diphong.com", "nganguyen.t07@gmail.com", "nvthanhhd@gmail.com", "trung.bts1498@gmail.com", "minhnguyenclvc@gmail.com", "nttv54@gmail.com", "hueleduc@gmail.com", "ngc.pham@gmail.com", "huulenh@gmail.com", "nguyenducvn1978@gmail.com", "h2t.fly@gmail.com", "chau.tanquang@gmail.com", "doanhanh2016vn@gmail.com", "huynhkimphu88@yahoo.com", "Pham.hue1402@gmail.com", "yentrinhulaw@gmail.com", "thaothuongng92@gmai.com", "anhtramdu@gmail.com", "gachtranhviethungphat@gmail.com", "ceo.rocketvn@gmail.com", "minhnguyetle0912@gmail.com", "Sarahle46@gmail.com", "huongtran.040170@gmail.com", "phuongnamtk96@gmail.com", "hieule.vit@gmail.com", "nguyendat0238@gmail.com", "truongngan8995@gmail.com", "khanh2684@gmail.com", "hoangthaihahvnh@gmail.com", "Nhuhue063@gmail.com", "hi3uhm@gmail.com", "thaikhucthi1977@gmail.com", "tuandh.ttud@gmail.com", "anhductmu@gmail.com", "Lochmu108@gmail.com", "vuvanvan77@gmail.com", "Daoquang.nvm@gmail.com", "sbktuan@gmail.com", "phamhung42514@gmail.com", "bomchiu@yahoo.com", "thehue68@gmail.com", "nghiemthanhha67@gmail.com", "cnyd.tuannguyen@gmail.com", "dom.buidue@gmail.com", "Maivanthuyet86@gmail.com", "Hoangndbk92@gmail.com", "chienvx0608@gmail.com", "phamhien8588@gmail.com", "daotuananh84@gmail.com", "Phuongvu0286@gmail.com", "tieuloha206@gmail.com", "binhxom@gmail.com", "Dungnhj.91@gmail.com", "thanhluan88@gmail.com", "huongktlas@gmail.com", "dangyen2410@gmail.com", "dunghv.vn123@gmail.com", "Tuyenvc1.mma@gmail.com", "khanhlinhhl1976@gmail.com", "linhktmtk56@gmail.com", "vuthihoaikt@gmail.com", "thieuthuhien@gmail.com", "Dungvv.neu@gmail.com", "ltxuan87@gmail.com", "dongphucconggiaodep@gmail.com", "dolinhdieu@gmail.com", "manhhungleyusen@gmail.com", "phuc.na.hp@gmail.com", "hong.phamvanhong@gmail.com", "tuananh29971@gmail.com", "Maichi.kt06@gmail.com", "kuku060789@gmail.com", "duytruongtb1411@gmail.com", "nguyenhong0988@gmail.com", "Truonggzone@gmail.com", "nguyenanhducvip@gmail.com", "nguyenthihieu.hanam1991@gmail.com", "trinhtrang92hup@gmail.com", "minhtamttr@gmail.com", "thaoitelco@gmail.com", "tienxntd.icon@gmail.com", "Hoangquy591@gmail.com", "nguyenthu1027@gmail.com", "Phamhanh151093@gmail.com", "kienvl91@gmail.com", "tranthaonb88@gmail.com", "Taphuong142@gmail.com", "trunglinh1711@gmail.com", "thuy1093.ntt@gmail.com", "cuongnguyenksct@gmail.com", "yencanh86@gmail.com", "manh.phamdinh0207@gmail.com", "nguyenvinhtruong@thaco.com.vn", "qcduchao@gmail.com", "anhhoang911007@gmail.com", "Anhtuan10071@gmail.com", "minhhn29@gmail.com", "ngoc.leo2607@gmail.com", "sharetobetter.xyz@gmail.com", "tuananh64dccd2002@gmail.com", "nguyetttdhb@gmail.com", "taidd3012@gmail.com", "vanhaet8@gmail.com", "thanhhaibka@gmail.com", "anhntads14@gmail.com", "nguyenthuylinh888@gmail.com", "lephuongnam@outlook.com", "kimchang87.sbm@gmail.com", "nguyendangtuanstd2@gmail.com", "lehongkytq@gmail.com", "vinhshome@gmail.com", "caovangiang81@gmail.com", "shane.xmai@gmail.com", "Dhvinh1411@gmail.com", "Pleanhkt@gmail.com", "nguyenhuong222232@gmail.com", "hoanghuutrang1987@gmail.com", "mtbox85@gmail.com", "vulen2001@gmail.com", "nguyenhaly161@gmail.com", "hiendd@alphanam.com", "Letrongtuandat@gmail.com", "lkthinh12@gmail.com", "Bacnhkt@gmail.com", "anhtuanst2@gmail.com", "Phiet270985@gmail.com", "andy.duong84@yahoo.com.sg", "manhcuong1810@gmail.com", "nguyentien01051975@gmail.com", "huyhoang.nus@gmail.com", "tuanmusicsp@gmail.com", "cuonla@gmail.com", "voan1302@gmail.com", "Nambk.bui@gmail.com", "dinhviethung308@gmail.com", "bathao30041996@gmail.com", "quangnam699@gmail.com", "dovantu8787@gmail.com", "lethanhtrung124@gmail.com", "Doanthoa@gmail.com", "toan.arc08@gmail.com", "vuanhtuan939@gmail.com", "hoanguyenbm8@gmail.com", "haphuong081099@gmail.com", "ngagyu.9@gmail.com", "HaThang6869@gmail.com", "Mai.anbinhexpress@gmail.com", "baongan.luule@gmail.com", "anhau27@gmail.com", "huongluucgh@gamil.com", "vuthiminhhuong668@gmail.com", "trhien82@gmail.com", "hungsctv123@gmail.com", "lequangthanh0889@gmail.com", "manhthang2014@gmial.com", "congvv.ptit@gmail.com", "thuyluongvu@gmail.com", "chauthanh12@gmail.com", "giangnguyenxd10@gmail.com", "hanknguyen81@gmail.com", "vutthang312@gmail.com", "huylam1706@gmail.com", "trantrongluan87@gmail.com", "trancuong040894@gmail.com", "sirtran87@gmail.com", "huypq70t36@gmail.com", "hohoang76@gmail.com", "ngocminh2990@gmail.com", "dangtrankhailuan@gmail.com", "ttuyetnhung2213@gmail.com", "Duongthu@suwaylavofirim.com", "lebinh.hbc@gmail.com", "Kentkent.to@gmail.com", "lesonm2@gmail.com", "tnminhanh368@gmail.com", "hoanhq2007@gmail.com", "hoangthuhang300398@gmail.com", "Thesu187@gmail.com", "quynhrich@gmail.com", "buihuyhoangneu55@gmail.com", "Tuyetnga411@gmail.com", "lhthaitran97@gmail.com", "nguyenanhtuan237@gmail.com", "thanhlamqlbv4@gmail.com", "htrang7ldh@gmail.com", "nguyenthanhtung1980@gmail.com", "dangminh2711@gmail.com", "thanhnh@asahijapan.com", "gianghoangvov@gmail.com", "anhkd5@gmail.com", "trdaisy@gmail.com", "ttduyen2009@gmail.com", "kiaanhthanh@gmail.com", "anhtuannguyen0811@gmail.com", "linhbk84@gmail.com", "anhpn@vdb.gov.vn", "trang.huyen310185@gmail.com", "quynhhtn@dxmb.vn", "tranvanduong.vt@gmail.com", "haittxpn@gmail.com", "luongt88@gmail.com", "linhttt13@gmail.com", "biquan97.qv@gmail.com", "civil.thanhtrung@gmail.com", "nguyenminhhai.190582guard@gmail.com", "trinhkhanhduy3009@gmail.com", "daongoctu80@gmail.com", "phanvanphuclkt@gmail.com", "tranhuypccc@gmail.com", "dohung58nuce@gmail.com", "Ducleanh88@gmail.com", "dhluan16405@gmail.com", "daonganht@gmail.com", "dtungya@gmail.com", "ir.batdongsan@gmail.com", "cuongtt2109@gmail.com", "hoisgtd@gmail.com", "hqhoan@gmail.com", "congvandoan@gmail.com", "tranthihue2405@yahoo.com.vn", "trungnguyen1805171@gmail.com", "vananh0610@gmail.com", "bluestan2010.pham@gmail.com", "tranthidiemphuong1807@gmail.com", "thuongnguyen4u@gmail.com", "dinhngocthang.vn@gmail.com", "danhloc120890@gmail.com", "trungvnshcm@gmail.com", "nguyenduy.sakuraevil@gmail.com", "hoanganh631996@gmail.com", "nickho2212@gmail.com", "ngado.designer@gmail.com", "chau.bui248@gmail.com", "quangtrung182592@gmail.com", "vinhmau148@gmail.com", "windboi@gmail.com", "nguyenanngan@gmail.com", "hanggg@gmail.com", "test2@dxmb.vn", "test3@dxmb.vn"];
        $list_phone_next = ["0334333902", "0964769482", "0904288969", "0918291665", "0909090386", "0984958888", "0389911967", "0903284327", "0326498058", "0986619689", "0932244558", "0985521550", "0986557859", "0987489468", "0982143703", "0978311970", "0326397401", "0888006900", "0919400043", "0909676762", "0906961716", "0984487249", "0909992925", "0981800293", "0982668232", "0913901498", "0909085030", "0978893924", "0919512831", "0911189069", "0914974771", "0934050786", "0987346889", "0983309230", "0977331146", "0903994586", "0938373068", "0909208796", "0767208000", "0977423389", "0909240293", "0963197300", "0962020137", "0988193912", "0919726318", "0901466677", "0983002767", "0908236845", "0903132772", "0908458828", "0907993970", "0376380257", "0968796006", "0903762802", "0986164129", "0346404065", "0911000070", "0388098686", "0366794063", "0988857909", "0904826339", "0788378196", "0972148556", "0974021587", "0918578328", "0913329108", "0936222523", "0949390108", "0965251422", "0912069306", "0917520610", "0988919907", "0355067114", "0379784927", "0968627680", "0989262755", "0364566690", "0989347041", "0935532286", "0948170546", "0913006507", "0816619999", "0972808313", "0378937062", "0948409577", "0931921995", "0983087207", "0966969909", "0397273858", "0979090647", "0988930452", "0909775178", "0974838782", "0977019792", "0971912662", "0904881813", "0818081188", "0912305918", "0934388558", "0359288246", "0985196786", "0973504820", "0984383559", "0984285220", "0902222939", "0344201903", "0981124688", "0989780467", "0936004885", "0365463304", "0925013953", "0969618345", "0384347779", "0989197391", "0936403644", "0986763311", "0385328192", "0985769827", "0978868272", "0988278328", "0397144560", "0938808250", "0984484593", "0913510981", "0932540456", "0987999801", "0936390070", "0972833616", "0986793930", "0915131787", "0968912368", "0973774568", "0348989237", "0989640702", "0984111812", "0987229281", "0935641359", "0399263663", "0382660467", "0988519879", "0974138000", "0968001086", "0376958762", "0964103232", "0328222232", "0385944021", "0909638428", "0888083568", "0366562926", "0335765597", "0374462308", "0917414973", "0932381486", "0986372735", "0904463068", "0968909029", "0936804331", "0985885844", "0936693575", "0979682402", "0984882640", "0963696376", "0918223162", "0984418175", "0975862569", "0913069109", "0922013688", "0912284686", "0914358268", "0901338883", "0988146848", "0986043111", "0971081099", "0975649514", "0974224868", "0866447247", "0986282086", "0356258796", "0973375560", "0988810965", "0963316084", "0989262112", "0987102181", "0907150949", "0356920011", "0985626288", "0387475259", "0989100458", "0908558668", "0983797879", "0768107297", "0982739972", "0868789335", "0983760822", "0984800064", "0909577269", "0933854089", "0989106223", "0988584383", "0906901899", "0703914575", "0973107030", "0986888372", "0986486866", "0983056888", "0973333683", "0986949368", "0909491838", "0961915519", "0972420269", "0374928584", "0358963921", "0911039877", "0912290456", "0911725556", "0775127597", "0363809068", "0938604490", "0986163417", "0903647850", "0986650519", "0964512130", "0982340040", "0964905766", "0945867177", "0976681568", "0979384380", "0946931165", "0983313370", "0978784250", "0982017311", "0909942618", "0901430368", "0971074979", "0989003772", "0908480515", "0934112824", "0977628356", "0986814664", "0969074590", "0794185853", "0916023450", "0902950972", "0977958099", "0828995858", "0983931698", "0987131898", "0936757377", "0967224765", "0938910634", "0937109092", "0382905901", "0966866529", "0919098998", "0914358479", "0982152316", "0913066196", "0984699347", "0975812448", "0912947796", "0982139289", "0983006638", "0932627672", "0984746455", "0934113242", "0989300308", "0931157801", "0961514138", "0933710489", "0962036680", "0918951500", "0904699447", "0985896008", "0979595770", "0918566891", "0919515866", "0379822358", "0985579620", "0855214294", "0901565643", "0933117449", "0901218679", "0936040692", "0904128088", "0904929657", "0909769392", "0964124374", "0933989777", "0965609669", "0942701203", "0967129002", "0988932773", "0943206435", "0972960797", "0939600077", "0399633589", "0983756427", "0765089100", "0903025127", "0915159910", "0906990460", "0934328714", "0968860368", "0906602656", "0989708965", "0943032755", "0798888819", "0355325950", "0908302949", "0383688369", "0938212343", "0328974725", "0978669968", "0792814632", "0383688369", "0908599325", "0975002332", "0388222607", "0976842847", "0983777729", "0816825555", "0948798958", "0974118144", "0931038886", "0866050809", "0908963611", "0365279682", "0901504018", "0901886879", "0988720517", "0388446260", "0936300789", "0914323858", "0903845207", "0909859497", "0919602192", "0908475665", "0903135484", "0972873962", "0938631005", "0934030084", "0984825546", "0945657154", "0943661772", "0915431433", "0915633542", "07002579127", "0964329987", "0908397998", "0903725713", "0979664543", "0977482372", "0793438746", "0938410811", "0904548216", "0969000001", "0822823231"];

        $list_email_fix = [
            "daisiaquocte@gmail.com" => "daigiaquocte@gmail.com",
        ];

        $list_phone_fix = [
            "0937628879; 0903208667" => "0937628879",
            "0913257852 - mẹ" => "0913257852",
            "0913346992/0912966518" => "0913346992",
            "093449287/0912213219" => "093449287",
            "0931989292/0936113666" => "0931989292",
            "0362224892/0346826062" => "0362224892",
            "0981955565/0985538123" => "0981955565",
            "0914565678/0913565678" => "0914565678",
            "0972408789/0976295785" => "0972408789",
            "0989142992/0987699292" => "0989142992",
            "0949634868/0868888504" => "0949634868",
            "0978914173/0969927193" => "0978914173",
            "0902407668-0938069" => "0902407668",
            "0986499163-0916818" => "0986499163",
            "0986499163-0916818464" => "0986499163",
            "0908281801 - 09168" => "0908281801",
            "0392156666-0937356" => "0392156666",
            "0903852611-0907228" => "0903852611",
            "+861390 6619 777" => "+8613906619777",
            "báođãchuyểnnhượngchưatìmđcsốđt" => ""
        ];

        $limit = $request->get("limit", false);
        if (!$limit) {
            echo "thiếu tham số limit";
            die;
        }
//        47805
        $listBuildAllow = [17, 28, 30, 37, 62, 63, 64, 66, 67, 68, 69, 70, 71, 72, 73, 77, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 94, 95, 96, 97, 98];
        ini_set('memory_limit', '-1');
//        $sql = "SELECT * FROM bdc_customers WHERE deleted_at is null ORDER BY id ASC LIMIT " . $limit;
//        $sql = "SELECT * FROM bdc_customers WHERE id <= 48252 AND deleted_at is null ORDER BY id ASC LIMIT " . $limit;
        $sql = "SELECT * FROM bdc_customers WHERE id > 48661 AND deleted_at is null ORDER BY id ASC LIMIT " . $limit;

//        $sql = "SELECT * FROM bdc_customers WHERE id = 12203 AND deleted_at is null ORDER BY id ASC LIMIT " . $limit;
        $data = DB::select(DB::raw($sql));

        $flg = Redis::get('userConvert');
        $run = Redis::get('userConvertRun');
        if($run) dd("đang chạy rồi");
        if (!$flg) $flg = 48661;
        $flg = 48661;
//        $flg = 0;
        $count = 0;
        Redis::setAndExpire('userConvertRun', 123, 30 * 24 * 60 * 60);

        foreach ($data as $item) {
            if ($item->id <= $flg) continue;
            $flg = $item->id;
            Redis::setAndExpire('userConvert', $item->id, 30 * 24 * 60 * 60);

//            if($item->bdc_apartment_id === 2982) echo "ok || ".$item->bdc_apartment_id;

//            continue;


            $user_id = 0;
            $AprtmentDetail = ApartmentsRespository::getInfoApartmentsById($item->bdc_apartment_id);
            if (!$AprtmentDetail) continue;

            if (!in_array($AprtmentDetail->building_id, $listBuildAllow)) continue;

//            $sql2 = "SELECT * from bdc_v2_user_apartment WHERE apartment_id = ".$item->bdc_apartment_id." AND deleted_at is null AND type = ".$item->type." AND bdc_service_id = ";
//            $debitDetail = DB::select(DB::raw($sql2));

            $sql2 = "SELECT * FROM pub_user_profile WHERE deleted_at is null AND id = " . $item->pub_user_profile_id;
            $dataProfile = DB::select(DB::raw($sql2));

            if (!$dataProfile) {
                echo "<p style='color: red'> ------------- ko tồn tại profile id: " . $item->pub_user_profile_id . "</p>";
                echo "</br>";
                continue;
            }

//            echo "có thông tin profile id: " . $item->pub_user_profile_id;
//            echo "</br>";
            $dataProfile = $dataProfile[0];

//            if($dataProfile->phone != "0937628879; 090") continue;
//            if(strpos($dataProfile->phone, "0937628879; 090") === false) continue;
//dd($dataProfile->phone);

            if (!$dataProfile->pub_user_id) {
                echo " --- ko tồn tại pub_user_id : " . $item->pub_user_profile_id;
                echo "</br>";
                $user_id_profile = $this->insertUserProfile($user_id, $dataProfile->display_name, $dataProfile->address, $dataProfile->cmt, $dataProfile->cmt_nc, $dataProfile->cmt_address, "", "", $dataProfile->avatar, $dataProfile->birthday, $dataProfile->gender, 0, $dataProfile->phone, $dataProfile->email);
            } else {
                $sql3 = "SELECT * FROM pub_users WHERE deleted_at is null AND id = " . $dataProfile->pub_user_id;
                $dataUser = DB::select(DB::raw($sql3));
                if (!$dataUser) {
                    echo "-------- ko tồn tại tk : " . $dataProfile->pub_user_id;
                    echo "</br>";
                    $user_id_profile = $this->insertUserProfile($user_id, $dataProfile->display_name, $dataProfile->address, $dataProfile->cmt, $dataProfile->cmt_nc, $dataProfile->cmt_address, "", "", $dataProfile->avatar, $dataProfile->birthday, $dataProfile->gender, 0, $dataProfile->phone, $dataProfile->email);
                    $countCheck = $this->sqlSelect("SELECT count(*) as tong FROM bdc_customers WHERE bdc_apartment_id = " . $item->bdc_apartment_id . " AND deleted_at is null");
                    if ($countCheck->tong <= 1) {
                        $dataUser = (object) [];
                        $dataUser->mobile = trim($dataProfile->phone);
                        $dataUser->email = trim($dataProfile->email);
                        $checkEmailInPhone = strpos($dataUser->mobile, "@");
                        if ($checkEmailInPhone !== false) {
                            $dataUser->mobile = "";
                        } else {
                            $dataUser->mobile = str_replace("'", "", $dataUser->mobile);
                            $dataUser->mobile = str_replace(".", "", $dataUser->mobile);
                            $dataUser->mobile = str_replace("+84", "0", $dataUser->mobile);
                        }

                        if (isset($list_phone_fix[$dataUser->mobile])) $dataUser->mobile = $list_phone_fix[$dataUser->mobile];
                        if (isset($list_email_fix[$dataUser->email])) $dataUser->email = $list_email_fix[$dataUser->email];
                        if (strlen($dataUser->mobile) < 9) $dataUser->mobile = "";

                        if (in_array($dataUser->email, $list_email_next)) goto aaa;
                        if (in_array($dataUser->mobile, $list_phone_next)) goto aaa;

                        if (!$dataUser->mobile && !$dataUser->email) goto aaa;
                        if ($dataUser->email) {
                            $sql = "SELECT * from bdc_v2_user WHERE phone = '" . $dataUser->mobile . "' AND ( email like '%" . $dataUser->email . "%' OR email = '' ) AND deleted_at is null ORDER BY id ASC LIMIT 1";
                        } else {
                            $sql = "SELECT * from bdc_v2_user WHERE phone = '" . $dataUser->mobile . "' AND deleted_at is null ORDER BY id ASC LIMIT 1";
                        }
                        $dataCheck = $this->sqlSelect($sql);
                        if (!$dataCheck) {
                            if ($dataUser->mobile) {
                                $sql = "SELECT * from bdc_v2_user WHERE phone = '" . $dataUser->mobile . "' AND deleted_at is null ORDER BY id ASC LIMIT 1";
                                $dataCheck2 = $this->sqlSelect($sql);
                                if ($dataCheck2) {
                                    $dataUser->mobile = "";
                                }
                            }
                            if ($dataUser->email) {
                                $sql = "SELECT * from bdc_v2_user WHERE email = '" . $dataUser->email . "' AND deleted_at is null ORDER BY id ASC LIMIT 1";
                                $dataCheck2 = $this->sqlSelect($sql);
                                if ($dataCheck2) {
                                    $dataUser->email = "";
                                }
                            }
                            if (!$dataUser->mobile && !$dataUser->email) goto aaa;
                            $user_id = $this->insertUser($dataUser->email, "$2y$08$.q9zLpfTFH6Aef75aOfsp.GR6uWmhmGNoyLIEK7clGN5PBQCY639C", 1, $dataUser->mobile, "", "+84", 0, 0);
                            $sql = "UPDATE bdc_v2_user_info SET user_id = " . $user_id . " WHERE id=" . $user_id_profile;
                            DB::update($sql);
                        }
                    }
                } else {
                    $dataUser = $dataUser[0];
                    $dataUser->mobile = trim($dataUser->mobile);
                    $dataUser->email = trim($dataUser->email);
                    $checkEmailInPhone = strpos($dataUser->mobile, "@");

                    if ($checkEmailInPhone !== false) {
                        $dataUser->mobile = "";
                    } else {
                        $dataUser->mobile = str_replace("'", "", $dataUser->mobile);
                        $dataUser->mobile = str_replace(".", "", $dataUser->mobile);
                        $dataUser->mobile = str_replace("+84", "0", $dataUser->mobile);
                    }

                    if (isset($list_phone_fix[$dataUser->mobile])) $dataUser->mobile = $list_phone_fix[$dataUser->mobile];
                    if (isset($list_email_fix[$dataUser->email])) $dataUser->email = $list_email_fix[$dataUser->email];

//                    $dataUser->mobile = str_replace(" ","",$dataUser->mobile);
                    if (strlen($dataUser->mobile) < 9) $dataUser->mobile = "";

                    if (in_array($dataUser->email, $list_email_next)) continue;
                    if (in_array($dataUser->mobile, $list_phone_next)) continue;

                    if (!$dataUser->mobile && !$dataUser->email) continue;
                    if ($dataUser->email) {
                        $sql = "SELECT * from bdc_v2_user WHERE phone = '" . $dataUser->mobile . "' AND ( email like '%" . $dataUser->email . "%' OR email = '' ) AND deleted_at is null ORDER BY id ASC LIMIT 1";
                    } else {
                        $sql = "SELECT * from bdc_v2_user WHERE phone = '" . $dataUser->mobile . "' AND deleted_at is null ORDER BY id ASC LIMIT 1";
                    }
                    $dataCheck = $this->sqlSelect($sql);
                    if (!$dataCheck) {
                        if ($dataUser->mobile) {
                            $sql = "SELECT * from bdc_v2_user WHERE phone = '" . $dataUser->mobile . "' AND deleted_at is null ORDER BY id ASC LIMIT 1";
                            $dataCheck2 = $this->sqlSelect($sql);
                            if ($dataCheck2) {
                                $dataUser->mobile = "";
                            }
                        }
                        if ($dataUser->email) {
                            $sql = "SELECT * from bdc_v2_user WHERE email = '" . $dataUser->email . "' AND deleted_at is null ORDER BY id ASC LIMIT 1";
                            $dataCheck2 = $this->sqlSelect($sql);
                            if ($dataCheck2) {
                                $dataUser->email = "";
                            }
                        }
                        if (!$dataUser->mobile && !$dataUser->email) continue;
                        $user_id = $this->insertUser($dataUser->email, $dataUser->password, $dataUser->status, $dataUser->mobile, "", "+84", $dataUser->mobile_active, 0);
                        $user_id_profile = $this->insertUserProfile($user_id, $dataProfile->display_name, $dataProfile->address, $dataProfile->cmt, $dataProfile->cmt_nc, $dataProfile->cmt_address, "", "", $dataProfile->avatar, $dataProfile->birthday, $dataProfile->gender, 0, $dataProfile->phone, $dataProfile->email);
                    } else {

                        if (!$dataCheck->email && $dataUser->email) { // update email vào
                            $sql = "UPDATE bdc_v2_user SET email = '" . $dataUser->email . "' WHERE id=" . $dataCheck->id;
                            DB::update($sql);
                        }

                        $sql = "SELECT * from bdc_v2_user_info WHERE user_id = " . $dataCheck->id . " LIMIT 1";
                        $dataCheck = $this->sqlSelect($sql);
                        $user_id_profile = $dataCheck->id;
                    }
                }
            }
            aaa:
            $this->insertUserAprartment($AprtmentDetail->building_id, $item->bdc_apartment_id, $user_id_profile, $item->type);
            echo "-------- xong : " . $item->id;
            echo "</br>";
            $count++;
            if($count >= 5000) break;
        }
        Redis::del('userConvertRun');
        echo "ok";
        dd($flg);
    }

    function sqlSelect($sql)
    {
        return DB::table(DB::raw("($sql) as tb1"))->first();
    }

    public function sqlquerySelect(Request $request)
    {
        return DB::table(DB::raw($request->sql))->first();
    }

    public function insertUserAprartment($building_id, $apartment_id, $user_info_id, $type)
    {
        DB::insert('insert into bdc_v2_user_apartment (building_id, apartment_id,user_info_id, type, created_at, updated_at) values (?, ?, ?, ?, ?, ?)',
            [$building_id, $apartment_id, $user_info_id, $type, \Carbon\Carbon::now()->format('Y-m-d H:i:s'), \Carbon\Carbon::now()->format('Y-m-d H:i:s')]);
        return DB::getPdo()->lastInsertId();
    }

    public function insertUserProfile($user_id, $full_name, $address, $cmt_number, $cmt_date, $cmt_address, $cmt_province, $cmt_image, $avatar, $birthday, $gender, $cmt_status, $phone_contact, $email_contact)
    {
        DB::insert('insert into bdc_v2_user_info (user_id, full_name,address, cmt_number, cmt_date, cmt_address, cmt_province, cmt_image, avatar, birthday, gender, cmt_status, phone_contact, email_contact, created_at, updated_at) values (?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?)',
            [$user_id, $full_name, $address, $cmt_number, $cmt_date, $cmt_address, $cmt_province, $cmt_image, $avatar, $birthday, $gender, $cmt_status, $phone_contact, $email_contact, \Carbon\Carbon::now()->format('Y-m-d H:i:s'), \Carbon\Carbon::now()->format('Y-m-d H:i:s')]);
        return DB::getPdo()->lastInsertId();
    }

    public function insertUser($email, $pword, $status, $phone, $username, $calling_code, $phone_status, $email_status)
    {
        if (!$pword) $pword = "";
        if (!$email) $email = "";
        if (!$phone) $phone = "";
        DB::insert('insert into bdc_v2_user (email, pword,status, phone, username, calling_code, phone_status, email_status, created_at, updated_at) values (?, ?, ?, ?, ?,?, ?, ?, ?, ?)',
            [$email, $pword, $status, $phone, $username, $calling_code, $phone_status, $email_status, \Carbon\Carbon::now()->format('Y-m-d H:i:s'), \Carbon\Carbon::now()->format('Y-m-d H:i:s')]);
        return DB::getPdo()->lastInsertId();
    }

    public function exportUserApartment(Request $request)
    {
        $building_id = $request->get("building_id", false);
        if (!$building_id) {
            echo "thiếu tham số building_id.";
            die;
        }

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $result = Excel::create('userApartment_toa' . $building_id, function ($excel) use ($building_id) {
            $excel->setTitle('danh sách user');
            $excel->sheet('danh sách', function ($sheet) use ($building_id) {
                $sql = "SELECT * FROM bdc_v2_user_apartment WHERE building_id = " . $building_id . " AND deleted_at is null ORDER BY building_id asc, apartment_id asc";
                $data = DB::select(DB::raw($sql));
                $contents = [];
                $listType = ["Chủ hộ", "Vợ/Chồng", "Con", "Bố mẹ", "Anh chị em", "Khác", "Khách thuê", "Chủ hộ cũ"];
                $i = 0;
                foreach ($data as $item) {

//                    if($i >= 100) break;

                    $user = $this->sqlSelect("SELECT * FROM bdc_v2_user_info WHERE id = ".$item->user_info_id);
                    $userLogin = false;
                    if($user->user_id) $userLogin = $this->sqlSelect("SELECT * FROM bdc_v2_user WHERE id = ".$user->user_id);

                    $apartments = ApartmentsRespository::getInfoApartmentsById($item->apartment_id);

                    $temp = [
                        'Mã tòa' => (string) $item->building_id,
                        'Mã căn' => (string) $item->apartment_id,
                        'Tên căn' => $apartments ? $apartments->name : "",
                        'Họ tên' => (string) $user->full_name,
                        'Quan hệ với chủ hộ' => isset($listType[$item->type]) ? (string) $listType[$item->type] : $item->type,
                        'sdt liên hệ' => $user ? $user->phone_contact : "",
                        'id đăng nhập' => $userLogin ? $userLogin->id : "",
                        'sdt đăng nhập' => $userLogin ? $userLogin->phone : "",
                        'email đăng nhập' => $userLogin ? $userLogin->email : "",
                    ];
                    $contents[] = $temp;
                    $i++;
                }
                $sheet->setAutoSize(true);
                // data of excel
                if ($contents) {
                    $sheet->fromArray($contents);
                }
                // add header
            });
        })->store('xlsx', storage_path('exports/'));
        $file = storage_path('exports/' . $result->filename . '.' . $result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
    }

    public function clearUserConvert(Request $request)
    {
        $sql = "DELETE FROM bdc_v2_user WHERE created_at >= '2022-07-11 10:53:10' ";
        DB::delete($sql);
        $sql = "DELETE FROM bdc_v2_user_apartment WHERE created_at >= '2022-07-11 10:53:10' ";
        DB::delete($sql);
        $sql = "DELETE FROM bdc_v2_user_info WHERE created_at >= '2022-07-11 10:53:10' ";
        DB::delete($sql);
        dd("ok");
        $sql = "DELETE FROM bdc_v2_user WHERE id not in (1, 2, 2217, 3882, 3888, 3973, 4120, 4124, 4196, 4201, 4207, 4464, 4490, 5144, 5452, 5617, 6642, 6662, 6714, 6785, 6841, 6842, 7180, 7252, 7254, 7282, 7362, 7497, 7537, 7649, 7713, 7849, 7871, 7914, 7926, 7963, 7980, 7994, 7997, 8005, 8086, 8096, 8149, 8168, 8177, 8213, 8248, 8269, 8281, 8283, 8331, 8365, 8382, 8388, 8453, 8462, 8467, 8491, 8495, 8498, 8504, 8507, 8523, 8542, 8546, 8560, 8571, 8589, 8597, 8602, 8603, 8624, 8642, 8651, 8670, 8678, 8684, 8688, 8693, 8695, 8714, 8725, 8729, 8742, 8748, 8755, 8760, 8772, 8795, 8806, 8820, 8823, 8836, 8852, 8857, 8875, 8890, 8892, 8904, 8911, 8914, 8920, 8924, 8952, 8976, 8996, 9006, 9024, 9034, 9038, 9097, 9109, 9111, 9125, 9134, 9153, 9162, 9169, 9186, 9193, 9210, 9240, 9241, 9254, 9264, 9271, 9290, 9305, 9312, 9316, 9322, 9339, 9340, 9345, 9351, 9352, 9372, 9376, 9386, 9394, 9400, 9402, 9409, 9412, 9425, 9428, 9436, 9438, 9439, 9457, 9459, 9467, 9474, 9480, 9503, 9507, 9512, 9527, 9537, 9538, 9548, 9549, 9573, 9584, 9590, 9605, 9613, 9624, 9639, 9647, 9656, 9657, 9672, 9675, 9763, 9781, 9844, 9962, 10056, 10267, 10408, 10476, 10508, 10579, 10592, 10664, 10726, 10785, 10801, 10845, 10874, 10878, 10989, 10992, 11184, 11215, 11221, 11303, 11312, 11414, 11420, 11426, 11499, 11527, 11584, 11611, 11708, 11710, 11856, 11911, 11925, 11988, 12047, 12137, 12153, 12265, 12342, 12346, 12414, 12444, 12459, 12473, 12474, 12509, 12519, 12537, 12559, 12656, 12697, 12718, 12736, 12739, 12787, 12818, 12839, 13218, 13227, 13250, 13301, 13340, 13345, 13387, 13412, 13451, 13532, 13598, 13725, 13740, 13846, 13867, 13922, 13959, 13961, 14008, 14036, 14089, 14189, 14266, 14426, 14438, 14445, 14447, 14519, 14546, 14573, 14625, 14678, 14700, 14706, 14710, 14816, 14818, 14830, 14840, 14917, 14923, 14937, 15003, 15016, 15028, 15070, 15078, 15091, 15102, 15103, 15166, 15228, 15248, 15273, 15350, 15375, 15434, 15437, 15448, 15476, 15491, 15625, 15644, 15712, 15713, 15718, 15773, 15782, 15826, 15899, 15966, 15978, 16128, 16149, 16190, 16216, 16271, 16273, 17389, 18643, 18689, 18809, 18821, 20920, 20948, 20953, 20969, 23034, 25317, 25334, 25515, 25530, 25639, 25688, 25773, 25807, 25878, 25917, 25992, 26001, 26021, 26085, 26141, 26238, 26267, 26309, 26341, 26364, 26369, 26402, 26409, 26441, 26452, 26462, 26463, 26485, 26489, 26521, 26527, 26592, 26593, 26594, 26595, 26604) ";
        DB::delete($sql);
        $sql = "DELETE FROM bdc_v2_user_info WHERE user_id not in (1, 2, 2217, 3882, 3888, 3973, 4120, 4124, 4196, 4201, 4207, 4464, 4490, 5144, 5452, 5617, 6642, 6662, 6714, 6785, 6841, 6842, 7180, 7252, 7254, 7282, 7362, 7497, 7537, 7649, 7713, 7849, 7871, 7914, 7926, 7963, 7980, 7994, 7997, 8005, 8086, 8096, 8149, 8168, 8177, 8213, 8248, 8269, 8281, 8283, 8331, 8365, 8382, 8388, 8453, 8462, 8467, 8491, 8495, 8498, 8504, 8507, 8523, 8542, 8546, 8560, 8571, 8589, 8597, 8602, 8603, 8624, 8642, 8651, 8670, 8678, 8684, 8688, 8693, 8695, 8714, 8725, 8729, 8742, 8748, 8755, 8760, 8772, 8795, 8806, 8820, 8823, 8836, 8852, 8857, 8875, 8890, 8892, 8904, 8911, 8914, 8920, 8924, 8952, 8976, 8996, 9006, 9024, 9034, 9038, 9097, 9109, 9111, 9125, 9134, 9153, 9162, 9169, 9186, 9193, 9210, 9240, 9241, 9254, 9264, 9271, 9290, 9305, 9312, 9316, 9322, 9339, 9340, 9345, 9351, 9352, 9372, 9376, 9386, 9394, 9400, 9402, 9409, 9412, 9425, 9428, 9436, 9438, 9439, 9457, 9459, 9467, 9474, 9480, 9503, 9507, 9512, 9527, 9537, 9538, 9548, 9549, 9573, 9584, 9590, 9605, 9613, 9624, 9639, 9647, 9656, 9657, 9672, 9675, 9763, 9781, 9844, 9962, 10056, 10267, 10408, 10476, 10508, 10579, 10592, 10664, 10726, 10785, 10801, 10845, 10874, 10878, 10989, 10992, 11184, 11215, 11221, 11303, 11312, 11414, 11420, 11426, 11499, 11527, 11584, 11611, 11708, 11710, 11856, 11911, 11925, 11988, 12047, 12137, 12153, 12265, 12342, 12346, 12414, 12444, 12459, 12473, 12474, 12509, 12519, 12537, 12559, 12656, 12697, 12718, 12736, 12739, 12787, 12818, 12839, 13218, 13227, 13250, 13301, 13340, 13345, 13387, 13412, 13451, 13532, 13598, 13725, 13740, 13846, 13867, 13922, 13959, 13961, 14008, 14036, 14089, 14189, 14266, 14426, 14438, 14445, 14447, 14519, 14546, 14573, 14625, 14678, 14700, 14706, 14710, 14816, 14818, 14830, 14840, 14917, 14923, 14937, 15003, 15016, 15028, 15070, 15078, 15091, 15102, 15103, 15166, 15228, 15248, 15273, 15350, 15375, 15434, 15437, 15448, 15476, 15491, 15625, 15644, 15712, 15713, 15718, 15773, 15782, 15826, 15899, 15966, 15978, 16128, 16149, 16190, 16216, 16271, 16273, 17389, 18643, 18689, 18809, 18821, 20920, 20948, 20953, 20969, 23034, 25317, 25334, 25515, 25530, 25639, 25688, 25773, 25807, 25878, 25917, 25992, 26001, 26021, 26085, 26141, 26238, 26267, 26309, 26341, 26364, 26369, 26402, 26409, 26441, 26452, 26462, 26463, 26485, 26489, 26521, 26527, 26592, 26593, 26594, 26595, 26604) ";
        DB::delete($sql);
        $sql = "DELETE FROM bdc_v2_user_apartment WHERE user_info_id not in ( 1,2,2861,4538,4544,4629,4776,4780,4852,4857,4863,5120,5146,5800,6108,6273,7298,7318,7370,7441,7497,7498,7836,7908,7910,7938,8018,8153,8193,8305,8369,8505,8527,8570,8582,8619,8636,8650,8653,8661,8742,8752,8805,8824,8833,8869,8904,8925,8937,8939,8987,9021,9038,9044,9109,9118,9123,9147,9151,9154,9160,9163,9179,9198,9202,9216,9227,9245,9253,9258,9259,9280,9298,9307,9326,9334,9340,9344,9349,9351,9370,9381,9385,9398,9404,9411,9416,9428,9451,9462,9476,9479,9492,9508,9513,9531,9546,9548,9560,9567,9570,9576,9580,9608,9632,9652,9662,9680,9690,9694,9753,9765,9767,9781,9790,9809,9818,9825,9842,9849,9866,9896,9897,9910,9920,9927,9946,9961,9968,9972,9978,9995,9996,10001,10007,10008,10028,10032,10042,10050,10056,10058,10065,10068,10081,10084,10092,10094,10095,10113,10115,10123,10130,10136,10159,10163,10168,10183,10193,10194,10204,10205,10229,10240,10246,10261,10269,10280,10295,10303,10312,10313,10328,10331,10419,10440,10505,10671,10841,11168,11372,11468,11503,11628,11646,11754,11832,11891,11907,11951,11980,11984,12162,12165,12464,12498,12504,12627,12641,12836,12846,12853,12960,13022,13134,13164,13282,13284,13486,13549,13568,13656,13725,13843,13866,13988,14101,14111,14213,14277,14292,14306,14307,14346,14356,14374,14398,14514,14566,14588,14607,14610,14669,14700,14721,15183,15200,15223,15285,15328,15333,15381,15418,15483,15584,15657,15794,15809,15926,15947,16024,16082,16088,16138,16166,16219,16319,16396,16556,16568,16581,16584,16659,16689,16720,16777,16830,16857,16863,16867,16923,16985,16987,16999,17009,17104,17111,17129,17222,17245,17259,17309,17317,17331,17342,17343,17434,17513,17533,17560,17646,17671,17732,17735,17746,17774,17789,17954,17978,18058,18060,18065,18137,18146,18193,18269,18343,18359,18579,18601,18618,18649,18677,18735,18739,19863,21129,21483,21803,21818,23924,23955,23962,23979,26049,28334,28354,28558,28580,28717,28769,28854,28888,28959,28999,29074,29085,29105,29169,29225,29322,29351,29394,29426,29449,29454,29498,29505,29542,29553,29563,29564,29587,29591,29630,29638,29704,29705,29706,29707,29716)";
        DB::delete($sql);
        dd("xong");
    }


    public function getMoreAccount(Request $request)
    {
        $email = $request->get("email", false);
        $phone = $request->get("phone", false);
        if (!$email && !$phone) {
            echo "thiếu tham số email hoặc phone.";
            die;
        }

        if($email) {
            $sql = "SELECT * FROM pub_users WHERE deleted_at is null AND email = '" . $email."' ORDER BY id ASC";
        }else{
            $sql = "SELECT * FROM pub_users WHERE deleted_at is null AND mobile = '" .$phone."' ORDER BY id ASC";
        }
        $data = DB::select(DB::raw($sql));
        foreach ($data as $item) {
            echo '</br>';
            $sql = "SELECT * FROM pub_user_profile WHERE deleted_at is null AND pub_user_id = " . $item->id;
            $profile = $this->sqlSelect($sql);
            if($profile){
                echo " <p style='color: black'>fullname: " . $profile->display_name . "</p>";
                echo " <p style='color: black'>user_id: " . $profile->pub_user_id . "</p>";
                echo " <p style='color: black'>user id profile: " . $profile->id . "</p>";
                echo " <p style='color: black'>---------------------------------------------------------------------------</p>";
                echo '</br>';
            }
        }
        dd("xong");
    }

    public function checkConvertUser(Request $request)
    {
        $listType = ["Chủ hộ", "Vợ/Chồng", "Con", "Bố mẹ", "Anh chị em", "Khác", "Khách thuê", "Chủ hộ cũ"];
        $apartmentId = $request->get("apartmentId", false);
        if (!$apartmentId) {
            echo "thiếu tham số apartmentId";
            die;
        }

        $sql = "SELECT * FROM bdc_customers WHERE deleted_at is null AND bdc_apartment_id = " . $apartmentId ;
        $apartments_old = DB::select(DB::raw($sql));
        foreach ($apartments_old as $item) {
            $sql = "SELECT * FROM pub_user_profile WHERE deleted_at is null AND id = " . $item->pub_user_profile_id;
            $profile = $this->sqlSelect($sql);

            if(!$profile){
                echo " <p style='color: red'>không tìm thấy user_info_id: " . $item->pub_user_profile_id . "</p>";
            } else {

                $user = false;
                if ($profile->pub_user_id) {
                    $sql = "SELECT * FROM pub_users WHERE deleted_at is null AND id = " . $profile->pub_user_id;
                    $user = $this->sqlSelect($sql);
                }

                $sql = "SELECT * FROM bdc_v2_user_apartment WHERE deleted_at is null AND apartment_id = " . $apartmentId." AND type =".$item->type;
                $apartments = DB::select(DB::raw($sql));
                $profile2 = false;
                foreach ($apartments as $item2) {
                    $sql = "SELECT * FROM bdc_v2_user_info WHERE deleted_at is null AND id = " . $item2->user_info_id;
                    $profile2 = $this->sqlSelect($sql);
                    $user2 = false;
                    if ($profile2->user_id) {
                        $sql = "SELECT * FROM bdc_v2_user WHERE deleted_at is null AND id = " . $profile2->user_id;
                        $user2 = $this->sqlSelect($sql);
                        if($user2->email && trim($user2->email) == trim($user->email)) break;
                        if($user2->phone && trim($user2->phone) == trim($user->mobile)) break;
                    }
                }
                echo " <p style='color: black'>fullname: " . $profile->display_name . "</p>";
                echo " <p style='color: black'>fullname v2: " . ($profile2->full_name ?? "" ). "</p>";
                echo " <p style='color: black'>user_id: " . $profile->pub_user_id . "</p>";
                echo " <p style='color: black'>sdt liên hệ bảng profile: " . $profile->phone . "</p>";
                echo " <p style='color: black'>sdt liên hệ bảng profile v2: " . ($profile2->phone_contact ?? "") . "</p>";
                echo " <p style='color: black'>user_info_id: " . $item->pub_user_profile_id . "</p>";
                echo " <p style='color: black'>Quan hệ: " . (isset($listType[$item->type]) ? $listType[$item->type] : $item->type) . "</p>";


                if(!$user){
                    echo " <p style='color: orange'>--------Không có tài khoản login---------------</p>";
                } else {
                    echo " <p style='color: black'>email đăng nhập:       " . $user->email . "</p>";
//                    echo " <p style='color: black'>email đăng nhập v2:       " . ($user2->email ?? "") . "</p>";
                    if($user2->email) {
                        echo "<a href='/admin/dev/getMoreAccount?email=" . $user2->email . "' target='_blank'>email đăng nhập v2: " . $user2->email . "</a>";
                        echo "</br>";
                        echo "</br>";
                    }

//                    echo " <p style='color: black'>sdt đăng nhập:         " . $user->mobile . "</p>";
                    if($user->mobile) {
                        echo "<a href='/admin/dev/getMoreAccount?phone=" . $user->mobile . "' target='_blank'>sdt đăng nhập: " . $user->mobile . "</a>";
                        echo "</br>";
                    }
                    echo " <p style='color: black'>sdt đăng nhập v2:         " . ($user2->phone ?? "") . "</p>";
                }
            }

            echo " <p style='color: black'>---------------------------------------------------------------------------</p>";

        }
        dd("xong");



        $sql = "SELECT * FROM bdc_v2_user_apartment WHERE deleted_at is null AND apartment_id = " . $apartmentId;
        $apartments = DB::select(DB::raw($sql));

        foreach ($apartments as $item){


            $sql = "SELECT * FROM bdc_v2_user_info WHERE deleted_at is null AND id = " . $item->user_info_id;
            $profile = $this->sqlSelect($sql);


            if(!$profile){
                echo " <p style='color: red'>không tìm thấy user_info_id: " . $item->user_info_id . "</p>";
            } else {
                echo " <p style='color: black'>fullname: " . $profile->full_name . "</p>";
                echo " <p style='color: black'>user_id: " . $profile->user_id . "</p>";
                echo " <p style='color: black'>user_info_id: " . $item->user_info_id . "</p>";
                echo " <p style='color: black'>Quan hệ: " . (isset($listType[$item->type]) ? $listType[$item->type] : $item->type) . "</p>";
                $user = false;
                if ($profile->user_id) {
                    $sql = "SELECT * FROM bdc_v2_user WHERE deleted_at is null AND id = " . $profile->user_id;
                    $user = $this->sqlSelect($sql);
                }

                if(!$user){
                    echo " <p style='color: orange'>--------Không có tài khoản login---------------</p>";
                } else {
                    echo " <p style='color: black'>email đăng nhập:       " . $user->email . "</p>";
                    echo " <p style='color: black'>sdt đăng nhập:         " . $user->phone . "</p>";
                }
            }


            echo " <p style='color: black'>---------------------------------------------------------------------------</p>";
        }

        dd("xong");
    }

    public function handleDupAutoPayment(Request $request)
    {
        $debitId = $request->get("debitId", false);
        if (!$debitId) {
            echo "thiếu tham số debitId";
            die;
        }
        $sql = "SELECT * FROM bdc_v2_debit_detail WHERE deleted_at is null AND id = " . $debitId;
        $debit = $this->sqlSelect($sql);
        if (!$debit) {
            dd("debit này ko có");
        }
//        dd($data->sumery);

        $sql = "SELECT * FROM bdc_v2_payment_detail WHERE deleted_at is null AND bdc_receipt_id = 0 AND bdc_log_coin_id != 0 AND bdc_debit_detail_id = " . $debitId;
        $data = DB::select(DB::raw($sql));
        if (count($data) < 2) {
            dd("debit này ko bị dup tự động hạch toán");
        }

        $sql = "SELECT sum(paid) as tong FROM bdc_v2_payment_detail WHERE deleted_at is null AND bdc_receipt_id = 0 AND bdc_log_coin_id != 0 AND bdc_debit_detail_id = " . $debitId;
        $pay = $this->sqlSelect($sql);
        if($debit->sumery >= $pay->tong){
            dd("debit này ko bị dup tự động hạch toán.");
        }
        $payBack = $data[1];
//        $sql = "DELETE FROM bdc_v2_log_coin_detail WHERE id = " . $payBack->bdc_log_coin_id;
//        DB::delete($sql);
        LogCoinDetail::where(['id'=>$payBack->bdc_log_coin_id])->delete();
        PaymentDetail::where(['id' => $payBack->id])->delete();
        $this->updateStatPay($payBack->bdc_apartment_id);
        BdcCoinRepository::addCoin($payBack->bdc_building_id, $payBack->bdc_apartment_id, $payBack->bdc_apartment_service_price_id, \Carbon\Carbon::now()->format('Ym'), 0, $payBack->paid, 0, 8, $payBack->id);
        dd("xong");
    }

    public function handleFixPaymentDelete(Request $request)
    {
        $bdc_receipt_id = $request->get("bdc_receipt_id", false);
        $bdc_apartment_id = $request->get("bdc_apartment_id", false);
        if (!$bdc_receipt_id || !$bdc_apartment_id) {
            echo "thiếu tham số bdc_receipt_id bdc_apartment_id";
            die;
        }

        $sql = "SELECT * from bdc_receipts WHERE id = '" . $bdc_receipt_id . "' AND deleted_at is null ORDER BY id ASC";
        $checkReceipt = $this->sqlSelect($sql);
        if($checkReceipt){
            dd("phiếu này vẫn tồn tại ko dc xóa");
        }

        PaymentDetail::where(['bdc_receipt_id' => $bdc_receipt_id])->delete(); // xóa
        $this->updateStatPay($bdc_apartment_id);
        dd("ok");
    }

    public function handleFixDebitDelete(Request $request)
    {
        $bdc_bill_id = $request->get("bdc_bill_id", false);
        $bdc_apartment_id = $request->get("bdc_apartment_id", false);
        if (!$bdc_bill_id || !$bdc_apartment_id) {
            echo "thiếu tham số bdc_bill_id bdc_apartment_id";
            die;
        }

        $sql = "SELECT * from bdc_bills WHERE id = '" . $bdc_bill_id . "' AND deleted_at is null ORDER BY id ASC";
        $checkReceipt = $this->sqlSelect($sql);
        if($checkReceipt){
            dd("phiếu này vẫn tồn tại ko dc xóa");
        }
        $data = [
            "deleted_at" => Carbon::now(),
            "updated_at" => Carbon::now(),
            "sumery" => 0,
            "discount" => 0,
        ];
        $rs = DebitDetail::where(['bdc_bill_id' => $bdc_bill_id])->update($data); // xóa
        $this->updateStatPay($bdc_apartment_id);
        dd("ok");
    }

    public function showDelDupUser(Request $request)
    {
        $listApart = [1301, 2661, 2406, 7204, 2632, 11809, 1146, 7000, 2495, 8824, 2632, 11812, 1246, 7088, 2529, 9010, 2632, 12097, 1301, 7204, 2529, 9052, 2641, 12532, 2543, 9058, 13548, 1320, 7204, 2564, 11017, 3468, 14095, 2049, 7909, 2572, 11118, 3764, 14250, 2162, 7989, 2606, 11429, 1130, 4615, 14366, 2168, 8113, 2625, 11436, 1135, 4629, 14367, 2392, 8559, 2626, 11462, 1146, 5391, 14368, 8647, 2632, 11735, 1146, 5953, 16035, 2470, 8673];
        $listUser = [32606, 31985, 32521, 35141, 32039, 37084, 32034, 33153, 32154, 35136, 32040, 37084, 29840, 33232, 32036, 35728, 32041, 38358, 32603, 35139, 32037, 36076, 32354, 35568, 32661, 35731, 33972, 29924, 35142, 32579, 44621, 31337, 39670, 34485, 34188, 32166, 44796, 31114, 39815, 30715, 34457, 31912, 36303, 29863, 31461, 39045, 32146, 34629, 32138, 36836, 30173, 31498, 39046, 32035, 35011, 32578, 44404, 30851, 32176, 39047, 35026, 32038, 46150, 32033, 32642, 48348, 31603, 32796];

        $i = 0;
        foreach ($listApart as $item) {
            /*if($i < 1) {
                $i++;
                continue;
            }*/
            $sql = "SELECT * FROM bdc_v2_user_apartment WHERE deleted_at is null AND apartment_id = ".$item." AND user_info_id = " . $listUser[$i]." ORDER BY updated_at ";
            $data = DB::select(DB::raw($sql));
            $i++;

            if(!$data || count($data) < 2) continue;

            foreach ($data as $item2) {
                echo " ---- ".$item2->type." ---- ".$item2->apartment_id." ----- ".$item2->user_info_id." ----- ".$item2->updated_at;
                echo "<a href='/admin/dev/handleDelUser?type=" . $item2->type ."&apartment_id=" . $item2->apartment_id."&user_info_id=" . $item2->user_info_id."&updated_at=" . $item2->updated_at . "' target='_blank'>-     xoa</a>";
                echo "</br>";
            }
            echo " ------------------------------------------------------------------------------------------------------------------------------ ";
            echo "</br>";
        }

        dd("xong");
    }

    public function handleDelUser(Request $request)
    {
        $type = $request->get("type", false);
        $apartment_id = $request->get("apartment_id", false);
        $user_info_id = $request->get("user_info_id", false);
        $updated_at = $request->get("updated_at", false);
        $sql = "UPDATE bdc_v2_user_apartment SET deleted_at = '" . Carbon::now() . "' WHERE type=" . $type . " AND apartment_id = '" . $apartment_id . "'". " AND user_info_id = '" . $user_info_id . "'". " AND updated_at = '" . $updated_at . "'";
        $rows = DB::update($sql);
        dd($rows);
    }
    public function duong_custom(Request $request)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://forum.vdevs.net/nossl/mtw.php?number=555269542',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        echo $response;
        die;
    }

    public function handleBackAutoPayment(Request $request)
    {
        $logId = $request->get("logId", false);
        if (!$logId) {
            return $this->sendResponse([
                'status' => 2,
                'mess' =>  "Thiếu tham số logId"
            ],'');
        }
        $sql = "SELECT * FROM bdc_v2_log_coin_detail WHERE deleted_at is null AND from_type = 2 AND id = " . $logId;
        $pay = $this->sqlSelect($sql);
        if(!$pay){
            return $this->sendResponse([
                'status' => 2,
                'mess' =>  "Không có tự động hạch toán này!"
            ],'');
        }
        $sql = "SELECT * FROM bdc_v2_payment_detail WHERE deleted_at is null AND bdc_log_coin_id = " . $logId;
        $payBack = $this->sqlSelect($sql);
        if(!$payBack){
            return $this->sendResponse([
                'status' => 2,
                'mess' =>  "Không có pay tự động hạch toán này!"
            ],'');
        }
        $action = Helper::getAction();
        if($action){
            $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id,$payBack->cycle_name,$action);
            if($check_lock_cycle){
                return $this->sendResponse([
                    'status' => 2,
                    'mess' =>  "Kỳ $payBack->cycle_name đã được khóa."
                ],'');
            }
        }
        LogCoinDetail::where(['id'=>$logId])->delete();
        PaymentDetail::where(['bdc_log_coin_id' => $logId])->delete();
        BdcCoinRepository::addCoin($payBack->bdc_building_id, $payBack->bdc_apartment_id, $payBack->bdc_apartment_service_price_id, \Carbon\Carbon::now()->format('Ym'), 0, $payBack->paid, 0, 8, $payBack->id);
        QueueRedis::setItemForQueue('add_queue_stat_payment_', [
            "apartmentId" => $payBack->bdc_apartment_id,
            "service_price_id" => $payBack->bdc_apartment_service_price_id,
            "cycle_name" => $payBack->cycle_name,
            "update_before_after" => false,
        ]);
        return $this->sendResponse([
            'status' => 1,
            'mess' =>  "Thành công"
        ],'');
    }

    public function handleFixUser(Request $request)
    {
        dd(123);
        $list = [38367, 38370, 38372, 38405, 38406, 38510, 38511, 38512, 38515, 38516, 38518, 38519, 38521, 38522, 38524, 38525, 38526, 38528, 38529, 38531, 38532, 38533, 38534, 38536, 38538, 38540, 38541, 38543, 38545, 38546, 38547, 38549, 38550, 38552, 38554, 38555, 38557, 38558, 38563, 38564, 38565, 38568, 38569, 38571, 38573, 38574, 38575, 38576, 38577, 38580, 38582, 38583, 38586, 38587, 38590, 38596, 38598, 38600, 38601, 38603, 38604, 38606, 38608, 38609, 38611, 38613, 38614, 38616, 38618, 38620, 38623, 38624, 38627, 38629, 38631, 38633, 38634, 38638, 38639, 38642, 38643, 38652, 38653, 38655, 38656, 38659, 38660, 38662, 38663, 38666, 38667, 38669, 38670, 38672, 38673, 38675, 38676, 38678, 38680, 38684, 38690, 38692, 38696, 38705, 38706, 38710, 38712, 38714, 38715, 38718, 38719, 38721, 38722, 38725, 38727, 38729, 38730, 38732, 38734, 38735, 38737, 38739, 38740, 38742, 38744, 38746, 38747, 38755, 38758, 38759, 38766, 38767, 38771, 38773, 38776, 38777, 38779, 38782, 38783, 38784, 38785, 38786, 38787, 38788, 38789, 38790, 38791, 38792, 38793, 38794, 38795, 38796, 38797, 38798, 38799, 38800, 38801, 38802, 38803, 38804, 38805, 38806, 38807, 38808, 38809, 38810, 38811, 38812, 38813, 38814, 38815, 38816, 38817, 38818, 38819, 38820, 38821, 38822, 38823, 38824, 38825, 38826, 38827, 38828, 38829, 38830, 38831, 38832, 38833, 38834, 38835, 38836, 38837, 38838, 38839, 38840, 38841, 38842, 38843, 38844, 38845, 38846, 38847, 38848, 38849, 38850, 38851, 38852, 38853, 38854, 38855, 38856, 38857, 38858, 38859, 38860, 38861, 38862, 38863, 38864, 38865, 38866, 38867, 38868, 38869, 38870, 38871, 38872, 38873, 38874, 38875, 38876, 38877, 38878, 38879, 38880, 38881, 38882, 38883, 38884, 38885, 38886, 38887, 39059, 39060, 39062, 39063, 39069, 39132, 39135, 39136, 39138, 39142, 39144, 39145, 39147, 39150, 39154, 39155, 39157, 39160, 39161, 39162, 39164, 39165, 39167, 39168, 39170, 39172, 39175, 39176, 39177, 39178, 39181, 39182, 39184, 39185, 39189, 39194, 39196, 39197, 39201, 39203, 39208, 39209, 39213, 39215, 39216, 39217, 39220, 39222, 39224, 39225, 39228, 39229, 39233, 39235, 39236, 39237, 39239, 39240, 39244, 39245, 39249, 39252, 39254, 39256, 39258, 39262, 39263, 39265, 39266, 39267, 39268, 39273, 39275, 39276, 39279, 39280, 39284, 39285, 39291, 39294, 39297, 39298, 39303, 39304, 39308, 39309, 39315, 39316, 39320, 39323, 39324, 39349, 39350, 39351, 39368, 39369, 39373, 39374, 39375, 39377, 39379, 39380, 39384, 39385, 39390, 39394, 39395, 39398, 39399, 39403, 39404, 39406, 39407, 39408, 39410, 39411, 39415, 39418, 39419, 39421, 39422, 39423, 39426, 39428, 39435, 39437, 39441, 39442, 39444, 39445, 39449, 39450, 39456, 39458, 39463, 39464, 39466, 39469, 39470, 39473, 39474, 39476, 39477, 39478, 39485, 39487, 39488, 39489, 39491, 39492, 39493, 39494, 39496, 39509, 39511, 39512, 39514, 39515, 39517, 39518, 39526, 39527, 39528, 39535, 39537, 39538, 39539, 39540, 39541, 39542, 39548, 39557, 39565, 39566, 39569, 39572, 39574, 39579, 39580, 39582, 39583, 39585, 39586, 39587, 39589, 39590, 39594, 39597, 39599, 39604, 39605, 39607, 39608, 39611, 39612, 39614, 39619, 39620, 39622, 39623, 39625, 39626, 39627, 39630, 39631, 39845, 39848, 39849, 39851, 39852, 39854, 39855, 39856, 39858, 39859, 39864, 39885, 39886, 39888, 39890, 39892, 39893, 39894, 39895, 39896, 39898, 39899, 39900, 39901, 39902, 39903, 39905, 39906, 39907, 39908, 39909, 39911, 39912, 39913, 39917, 39920, 39922, 39924, 39926, 39928, 39930, 39932, 39941, 39946, 39947, 39948, 39949, 39950, 39952, 39953, 39954, 39955, 39956, 39958, 39959, 39960, 39961, 39962, 39965, 39969, 39971, 39972, 39975, 39976, 39978, 39981, 39984, 39989, 39990, 39999, 40000, 40009, 40010, 40013, 40015, 40022, 40024, 40025, 40027, 40029, 40033, 40034, 40036, 40037, 40039, 40040, 40041, 40068, 40070, 40076, 40078, 40079, 40080, 40081, 40083, 40086, 40087, 40089, 40092, 40110, 40117, 40118, 40134, 40135, 40138, 40142, 40144, 40146, 40147, 40151, 40152, 40154, 40157, 40159, 40160, 40162, 40164, 40165, 40166, 40167, 40174, 40175, 40178, 40179, 40182, 40184, 40187, 40188, 40189, 40195, 40198, 40200, 40202, 40203, 40204, 40208, 40211, 40212, 40213, 40214, 40216, 40217, 40218, 40225, 40226, 40230, 40234, 40239, 40240, 40241, 40244, 40245, 40246, 40247, 40248, 40250, 40251, 40252, 40253, 40254, 40256, 40262, 40263, 40267, 40268, 40269, 40270, 40271, 40272, 40273, 40274, 40277, 40280, 40281, 40305, 40309, 40310, 40325, 40326, 40330, 40332, 40333, 40334, 40335, 40341, 40342, 40343, 40344, 40345, 40346, 40352, 40353, 40354, 40356, 40357, 40359, 40360, 40361, 40363, 40364, 40376, 40381, 40382, 40384, 40387, 40388, 40398, 40399, 40400, 40401, 40402, 40405, 40407, 40412, 40413, 40414, 40415, 40416, 40437, 40440, 40442, 40444, 40445, 40449, 40453, 40455, 40456, 40457, 40460, 40461, 40462, 40463, 40464, 40465, 40466, 40470, 40471, 40472, 40473, 40477, 40478, 40480, 40481, 40482, 40483, 40484, 40485, 40486, 40487, 40489, 40490, 40493, 40494, 40496, 40497, 40499, 40500, 40501, 40503, 40504, 40505, 40506, 40508, 40510, 40511, 40514, 40515, 40517, 40518, 40520, 40523, 40524, 40526, 40530, 40531, 40534, 40535, 40537, 40538, 40539, 40540, 40541, 40546, 40550, 40551, 40554, 40555, 40557, 40559, 40561, 40562, 40564, 40565, 40567, 40570, 40572, 40573, 40579, 40580, 40581, 40585, 40609, 40610, 40618, 40619, 40620, 40621, 40622, 40623, 40625, 40626, 40628, 40634, 40635, 40636, 40637, 40643, 40645, 40647, 40649, 40654, 40663, 40664, 40665, 40697, 40701, 40707, 40708, 40710, 40713, 40714, 40718, 40720, 40721, 40722, 40723, 40724, 40726, 40730, 40734, 40735, 40737, 40738, 40739, 40741, 40742, 40743, 40744, 40745, 40746, 40747, 40754, 40755, 40757, 40758, 40760, 40761, 40762, 40763, 40766, 40769, 40772, 40773, 40775, 40777, 40778, 40779, 40780, 40782, 40783, 40788, 40792, 40793, 40794, 40795, 40799, 40800, 40801, 40806, 40807, 40808, 40810, 40811, 40814, 40815, 40816, 40817, 40818, 40819, 40820, 40821, 40822, 40826, 40827, 40828, 40832, 40833, 40838, 40839, 40840, 40841, 40843, 40844, 40845, 40847, 40848, 40850, 40851, 40853, 40854, 40855, 40858, 40859, 40860, 40864, 40865, 40867, 40868, 40871, 40872, 40874, 40875, 40877, 40880, 40881, 40885, 40887, 40888, 40889, 40895, 40896, 40897, 40899, 40900, 40901, 40902, 40903, 40907, 40908, 40909, 40916, 40917, 40920, 40921, 40929, 40930, 40943, 40975, 40976, 40978, 40979, 40980, 40982, 40983, 41011, 41012, 41013, 41014, 41021, 41023, 41026, 41027, 41028, 41042, 41043, 41048, 41051, 41052, 41063, 41064, 41065, 41066, 41073, 41075, 41076, 41119, 41120, 41123, 41130, 41136, 41137, 41139, 41140, 41146, 41152, 41153, 41155, 41156, 41157, 41158, 41159, 41161, 41165, 41167, 41168, 41179, 41189, 41190, 41191, 41192, 41193, 41199, 41200, 41201, 41203, 41205, 41207, 41214, 41215, 41216, 41221, 41222, 41224, 41229, 41230, 41234, 41237, 41238, 41239, 41241, 41244, 41250, 41251, 41252, 41254, 41255, 41257, 41258, 41261, 41262, 41264, 41265, 41326, 41327, 41328, 41330, 41331, 41332, 41334, 41337, 41338, 41346, 41347, 41368, 41369, 41370, 41371, 41377, 41378, 41380, 41381, 41385, 41387, 41388, 41391, 41396, 41397, 41402, 41403, 41408, 41412, 41416, 41422, 41423, 41424, 41426, 41459, 41460, 41461, 41462, 41466, 41491, 41492, 41497, 41501, 41502, 41503, 41504, 41507, 41508, 41509, 41511, 41512, 41513, 41516, 41517, 41518, 41519, 41529, 41530, 41531, 41564, 41566, 41568, 41570, 41571, 41585, 41586, 41588, 41595, 41596, 41602, 41603, 41604, 41607, 41610, 41623, 41652, 41653, 41654, 41656, 41659, 41677, 41682, 41683, 41684, 41725, 41726, 41727, 41728, 41731, 41737, 41739, 41752, 41764, 41766, 41767, 41768, 41770, 41771, 41772, 41773, 41774, 41775, 41782, 41784, 41785, 41786, 41790, 41791, 41793, 41795, 41796, 41798, 41799, 41800, 41801, 41812, 41813, 41818, 41820, 41821, 41824, 41825, 41826, 41827, 41828, 41831, 41854, 41867, 41868, 41871, 41874, 41875, 41877, 41878, 41879, 41880, 41884, 41886, 41887, 41888, 41889, 41891, 41892, 41894, 41900, 41903, 41905, 41909, 41911, 41912, 41913, 41914, 41915, 41916, 41918, 41919, 41920, 41924, 41925, 41927, 41938, 41939, 41940, 41941, 41942, 41944, 41945, 41946, 41947, 41949, 41953, 41954, 41955, 41956, 41957, 41958, 41961, 41962, 41964, 41965, 41967, 41969, 41971, 41972, 41973, 41974, 41975, 41979, 41980, 41981, 41982, 41983, 41984, 41987, 42049, 42050, 42051, 42052, 42095, 42098, 42129, 42130, 42150, 42151, 42152, 42179, 42184, 42185, 42186, 42187, 42188, 42189, 42190, 42197, 42198, 42199, 42201, 42203, 42205, 42220, 42235, 42236, 42240, 42241, 42249, 42250, 42251, 42252, 42258, 42259, 42271, 42288, 42307, 42310, 42311, 42315, 42320, 42321, 42324, 42326, 42327, 42329, 42350, 42430, 42435, 42437, 42439, 42444, 42472, 42473, 42504, 42508, 42625, 42633, 42680, 42681, 42683, 42701, 42729, 42731, 42737, 42738, 42757, 42763, 42764, 42765, 42766, 42767, 42768, 42769, 42770, 42771, 42772, 42773, 42774, 42775, 42776, 42777, 42778, 42779, 42780, 42781, 42782, 42783, 42784, 42785, 42786, 42787, 42788, 42789, 42790, 42791, 42792, 42793, 42794, 42795, 42796, 42797, 42798, 42799, 42800, 42801, 42802, 42803, 42804, 42805, 42806, 42807, 42808, 42809, 42810, 42811, 42812, 42813, 42814, 42815, 42816, 42817, 42818, 42821, 42823, 42824, 42849, 42850, 42852, 42857, 42863, 42866, 42867, 42868, 42869, 42870, 42871, 42874, 42896, 42897, 42935, 42938, 42939, 42942, 42943, 42944, 42946, 42947, 42948, 42968, 42976, 42984, 42989, 43026, 43027, 43028, 43029, 43030, 43038, 43053, 43054, 43055, 43056, 43057, 43058, 43060, 43063, 43064, 43071, 43072, 43074, 43079, 43080, 43087, 43091, 43092, 43093, 43095, 43106, 43107, 43108, 43109, 43110, 43111, 43112, 43114, 43115, 43123, 43129, 43130, 43131, 43132, 43133, 43134, 43135, 43136, 43137, 43142, 43148, 43152, 43164, 43165, 43166, 43167, 43170, 43172, 43190, 43191, 43192, 43202, 43205, 43219, 43220, 43221, 43227, 43233, 43234, 43240, 43241, 43242, 43243, 43286, 43288, 43289, 43326, 43327, 43367, 43373, 43374, 43375, 43376, 43391, 43400, 43413, 43434, 43439, 43442, 43443, 43486, 43493, 43494, 43507, 43508, 43528, 43529, 43574, 43579, 43580, 43582, 43585, 43586, 43595, 43601, 43602, 43603, 43604, 43606, 43608, 43611, 43612, 43614, 43615, 43617, 43619, 43620, 43623, 43626, 43635, 43636, 43637, 43638, 43639, 43647, 43653, 43655, 43656, 43657, 43658, 43659, 43670, 43671, 43673, 43674, 43677, 43678, 43683, 43686, 43687, 43688, 43689, 43690, 43691, 43694, 43695, 43696, 44165, 44166, 44167, 44168, 44173, 44174, 44176, 44188, 44189, 44190, 44267, 44268, 44269, 44289, 44290, 44302, 44303, 44311, 44312, 44339, 44351, 44352, 44407, 44408, 44423, 44424, 44425, 44439, 44440, 44441, 44467, 44469, 44475, 44478, 44493, 44494, 44495, 44525, 44532, 44565, 44567, 44572, 44573, 44575, 44576, 44578, 44607, 44608, 44611, 44626, 44627, 44639, 44642, 44643, 44646, 44647, 44651, 44660, 44663, 44664, 44678, 44679, 44688, 44692, 44693, 44696, 44697, 44705, 44706, 44714, 44715, 44716, 44721, 44722, 44731, 44732, 44745, 44746, 44747, 44748, 44751, 44752, 44756, 44757, 44762, 44763, 44764, 44768, 44769, 44772, 44777, 44778, 44781, 44783, 44784, 44787, 44788, 44791, 44792, 44799, 44800, 44815, 44820, 44821, 44834, 44835, 44836, 44850, 44851, 44864, 44881, 44882, 44885, 44886, 44887, 44888, 44895, 44896, 44906, 44909, 44917, 44918, 44919, 44921, 44922, 44925, 44926, 44934, 44935, 44941, 44942, 44943, 44944, 44945, 44954, 44955, 44959, 44960, 44968, 44969, 44983, 44986, 44987, 44988, 44994, 44995, 45005, 45010, 45011, 45018, 45021, 45022, 45026, 45032, 45034, 45052, 45057, 45079, 45091, 45097, 45098, 45099, 45107, 45138, 45139, 45140, 45225, 45226, 45295, 45312, 45313, 45314, 45320, 45321, 45325, 45326, 45330, 45331, 45345, 45346, 45347, 45348, 45349, 45352, 45355, 45356, 45358, 45359, 45360, 45364, 45365, 45369, 45376, 45377, 45378, 45379, 45425, 45426, 45440, 45441, 45442, 45450, 45452, 45462, 45463, 45471, 45472, 45492, 45493, 45495, 45501, 45511, 45512, 45525, 45529, 45530, 45538, 45545, 45546, 45551, 45552, 45559, 45560, 45561, 45564, 45565, 45578, 45587, 45588, 45589, 45590, 45596, 45598, 45617, 45618, 45624, 45668, 45669, 45717, 45737, 45755, 45764, 45765, 45778, 45781, 45782, 45796, 45801, 45802, 45803, 45871, 45872, 45873, 45879, 45882, 45883, 45891, 45892, 45895, 45898, 45901, 45903, 45915, 45917, 45926, 45927, 45928, 45933, 45936, 45937, 45939, 45940, 45941, 45945, 45946, 45947, 45948, 45949, 45950, 45951, 45952, 45953, 45954, 45955, 45956, 45957, 45961, 45962, 45963, 45964, 45965, 45966, 45967, 45968, 45969, 45970, 45971, 45972, 45973, 45974, 45975, 45976, 45977, 45978, 45979, 45980, 45982, 45983, 45984, 45985, 45986, 45987, 45988, 45989, 45990, 45993, 45994, 45999, 46015, 46016, 46027, 46040, 46041, 46052, 46055, 46061, 46062, 46063, 46104, 46105, 46139, 46140, 46141, 46160, 46161, 46166, 46174, 46203, 46204, 46205, 46206, 46241, 46242, 48265, 48266, 48267, 48299, 48300, 48306, 48307, 48308, 48332, 48333, 48339, 48340, 48354, 48361, 48362, 48363, 48364, 48365, 48366, 48368, 48369, 48370, 48371, 48372, 48373, 48374, 48375, 48377, 48378, 48379, 48380, 48381, 48382, 48383, 48384, 48386, 48388, 48389, 48390, 48391, 48392, 48393, 48394, 48395, 48396, 48397, 48398, 48399, 48400, 48401, 48402, 48403, 48404, 48405, 48406, 48407, 48408, 48409, 48410, 48411, 48412, 48413, 48414, 48415, 48416, 48417, 48418, 48419, 48420, 48421, 48422, 48423, 48424, 48425, 48426, 48427, 48428, 48429, 48430, 48431, 48432, 48433, 48434, 48435, 48436, 48437, 48438, 48439, 48440, 48441, 48442, 48443, 48444, 48445, 48446, 48447, 48448, 48449, 48450, 48451, 48452, 48453, 48454, 48455, 48456, 48457, 48458, 48459, 48460, 48461, 48462, 48463, 48464, 48465, 48466, 48467, 48468, 48469, 48470, 48471, 48472, 48473, 48474, 48475, 48476, 48477, 48478, 48479, 48480, 48481, 48482, 48483, 48484, 48485, 48486, 48487, 48488, 48489, 48490, 48491, 48494, 48495, 48496, 48497, 48498, 48499, 48500, 48501, 48502, 48503, 48504, 48505, 48506, 48507, 48511, 48512, 48513, 48514, 48515, 48517, 48518, 48519, 48520, 48522, 48523, 48524, 48525, 48526, 48527, 48528, 48529, 48530, 48531, 48532, 48534, 48535, 48536, 48537, 48538, 48539, 48542, 48543, 48544, 48545, 48546, 48547, 48548, 48549, 48550, 48551, 48552, 48553, 48554, 48555, 48556, 48557, 48558, 48559, 48560, 48561, 48562, 48563, 48564, 48565, 48566, 48567, 48569, 48570, 48571, 48572, 48573, 48574, 48575, 48577, 48578, 48579, 48580, 48581, 48582, 48583, 48584, 48587, 48588, 48589, 48590, 48591, 48592, 48593, 48594, 48595, 48596, 48597, 48598, 48599, 48601, 48602, 48603, 48604, 48606, 48607, 48608, 48609, 48610, 48611, 48612, 48613, 48614, 48615, 48616, 48617, 48618, 48619, 48620, 48621, 48622, 48623, 48624, 48625, 48626, 48627, 48628, 48629, 48630, 48631, 48632, 48633, 48634, 48635, 48636, 48637, 48638, 48639, 48640, 48641, 48642, 48643, 48644, 48645, 48646, 48647, 48648, 48649, 48650, 48651, 48652, 48653, 48654, 48655, 48656, 48657, 48658, 48659, 48660, 48661, 48662, 48663, 48664, 48665, 48666, 48667, 48668, 48669, 48670, 48671, 48672, 48673, 48674, 48675, 48676, 48677, 48678, 48679, 48680, 48681, 48682, 48683, 48684, 48685, 48686, 48690, 48691, 48694, 48695, 48696, 48697, 48698, 48699, 48700, 48701, 48702, 48703, 48704, 48705, 48706, 48707, 48709, 48710, 48711, 48712, 48713, 48714, 48715, 48716, 48717, 48718, 48719, 48720, 48721, 48722, 48723, 48724, 48725, 48726, 48727, 48728, 48729, 48730, 48731, 48732, 48733, 48735, 48736, 48737, 48738, 48739, 48740, 48741, 48742, 48743, 48744, 48745, 48746, 48747, 48748, 48749, 48750, 48751, 48752, 48753, 48754, 48755, 48756, 48757, 48758, 48759, 48760, 48761, 48762, 48763, 48764, 48765, 48766, 48767, 48768, 48769, 48770, 48771, 48772, 48773, 48774, 48775, 48776, 48777, 48778, 48779, 48780, 48782, 48783, 48784, 48785, 48786, 48787, 48788, 48789, 48790, 48791, 48792, 48793, 48794, 48795, 48796, 48798, 48799, 48800, 48801, 48802, 48805, 48806, 48807, 48808, 48809, 48810, 48811, 48812, 48813, 48814, 48816, 48817, 48818, 48819, 48820, 48821, 48822, 48823, 48824, 48825, 48828, 48830, 48831, 48832, 48833, 48834, 48835, 48837, 48838, 48839, 48840, 48841, 48842, 48845, 48846, 48847, 48848, 48850, 48851, 48852, 48853, 48855, 48856, 48857, 48858, 48859, 48860, 48861, 48862, 48863, 48864, 48954, 48955, 48956, 48957, 48958, 48959, 48960, 48961, 48962, 48963, 48964, 48965, 48966, 48967, 48969, 48971, 48972, 48973, 48974, 48975, 48976, 48978, 48979, 48980, 48981, 48982, 48983, 48984, 48985, 48986, 48987, 48988, 48989, 48990, 48991, 48992, 48993, 48994, 48995, 48998, 48999, 49000, 49003, 49006, 49010, 49020, 49021, 49022, 49040, 49041, 50679, 50680, 50952, 50953, 50954, 50973, 50974, 50985, 50995, 51007, 51008, 51009, 51018, 55316, 55325, 55327, 55338, 55339, 55354, 55415, 55467, 55468, 55470, 55471, 55472, 55473, 55476, 55477, 55481, 55484, 55485, 55486, 55487, 55489, 55490, 55491, 55492, 55493, 55495, 55498, 55505, 55519, 55528, 55529, 55530, 55533, 55536, 55537, 55544, 55563, 55564, 55565, 55566, 55568, 55570, 55572, 55575, 55577, 55578, 55581, 55582, 55583, 55595, 55602, 55603, 55609, 55610, 55611, 55621, 55622, 55639, 55640, 55659, 55662, 55663, 55664, 55675, 55676, 55682, 55921, 56013, 56014, 56317, 56372, 56375, 56380, 56383, 56384, 56388, 56390, 56391, 56392, 56394, 56400, 56428, 56429, 56430, 56432, 56437, 56466, 56491, 56501, 56502, 56506, 56509, 56510, 56511, 56517, 56518, 56581, 56596, 56597, 56598, 56609, 56610, 56617, 56618, 56620, 56621, 56623, 56624, 56625, 56626, 56628, 56639, 56642, 56652, 56653, 56660, 56668, 56669, 56673, 56674, 56692, 56722, 56723, 56724, 56736, 56737, 56759, 56760, 56779, 56782, 56787, 56811, 56812, 56813, 56817, 56818, 56836, 56848, 56849, 56850, 56851, 56852, 56853, 56858, 56862, 56864, 56870, 56879, 56890, 56891, 56892, 56893, 56901, 56902, 56921, 56922, 56930, 56931, 56932, 56933, 56934, 56935, 56940, 56941, 56942, 56943, 56980, 56983, 56985, 56996, 56997, 57003, 58971, 58972, 58973, 58974, 58975, 61585, 61586, 61587, 61588, 61589, 61592, 61597, 61598, 61602, 61603, 61612, 61613, 61617, 61619, 61627, 61630, 61634, 61639, 61642, 61643, 61646, 61647, 61648, 61649, 61650, 61659, 61661, 61666, 61674, 61675, 61680, 61681, 61685, 61687, 61688, 61694, 61699, 61700, 61702, 61703, 61707, 61708, 61715, 61722, 61724, 61725, 61729, 61736, 61742, 61743, 61745, 61747, 61762, 61768, 61777, 61781, 61787, 61788, 61801, 61806, 61812, 61813, 61814, 61817, 61819, 61821, 61822, 61823, 61824, 61825, 61826, 61833, 61836, 61843, 61845, 61847, 61850, 61855, 61856, 61861, 61862, 61871, 61877, 61894, 61900, 61915, 61917, 61919, 61921, 61938, 61943, 61948, 61962, 61964, 61968, 61975, 61977, 61978, 61986, 61989, 61992, 61994, 61995, 61996, 61997, 62004, 62019, 62023, 62024, 62027, 62029, 62030, 62032, 62042, 62047, 62054, 62055, 62058, 62060, 62061, 62064, 62070, 62071, 62072, 62079, 62095, 62102, 62115, 62129, 62130, 62131, 62133, 62135, 62144, 62147, 62156, 62157, 62158, 62159, 62164, 62171, 62173, 62181, 62197, 62199, 62200, 62203, 62208, 62213, 62215, 62217, 62218, 62223, 62224, 62225, 62226, 62227, 62228, 62229, 62230, 62231, 62232, 62233, 62235, 62236, 62237, 62238, 62239, 62240, 62255, 62257, 62264, 62265, 62298, 62299, 62331, 62335, 62342, 62358, 62359, 62363, 62364, 62365, 62366, 62370, 62373, 62378, 62379, 62380, 62382, 62389, 62390, 62393, 62394, 62396, 62397, 62403, 62404, 62405, 62408, 62409, 62413, 62414, 62422, 62423, 62427, 62428, 62436, 62437, 62448, 62449, 62454, 62464, 62465, 62467, 62478, 62479, 62483, 62484, 62485, 62489, 62498, 62499, 62506, 62534, 62546, 62547, 62553, 62554, 62555, 62567, 62568, 62569, 62575, 62576, 62577, 62600, 62601, 62606, 62607];
        $listHandle = [];
        $listCheck = [];
        foreach ($list as $item) {
            $sql = "SELECT * FROM bdc_v2_user_info WHERE deleted_at is null AND id = " . $item;
            $userInfo = $this->sqlSelect($sql);
            if (!$userInfo) continue;
            if(in_array($item,$listHandle)) continue;

            echo "handle ".$item;
            echo "</br>";


            $sql = "SELECT * FROM bdc_v2_user_apartment WHERE deleted_at is null AND user_info_id = " . $item;
            $userApartment = $this->sqlSelect($sql);

            if (!$userApartment) continue;

//            $sql = "SELECT * FROM bdc_customers WHERE deleted_at is null AND bdc_apartment_id = " . $userApartment->apartment_id . " AND type = " . $userApartment->type;
//            $data = DB::select(DB::raw($sql));

            $sql = "SELECT * FROM bdc_v2_user_apartment WHERE deleted_at is null AND apartment_id = " . $userApartment->apartment_id . " AND type = " . $userApartment->type;
            $data2 = DB::select(DB::raw($sql));
            if(!$data2) continue;

            $data2 = Arr::pluck($data2, 'user_info_id');

            $listUserAll = $data2;

            $collection = collect($list);
            $listAccept = $collection->intersect($data2);
            $listAccept = array_values($listAccept->toArray());
            Log::dump($listAccept);

            $sql = "SELECT * FROM bdc_customers WHERE deleted_at is null AND bdc_apartment_id = " . $userApartment->apartment_id . " AND type = " . $userApartment->type;
            $data = DB::select(DB::raw($sql));
            $i = 0;
            foreach ($data as $itemUser) {
                $sql = "SELECT * FROM pub_user_profile WHERE deleted_at is null AND id = " . $itemUser->pub_user_profile_id;
                $dataProfile = $this->sqlSelect($sql);
                if(!$dataProfile || !isset($listAccept[$i])) {
                    Log::dump($listAccept);
                    Log::dump($i);
                    Log::dump($dataProfile);
                    Log::dump(isset($listAccept[$i]));
                    if(!$dataProfile || isset($listAccept[$i]) === false) {
                        $getApart = $this->sqlSelect("SELECT * FROM bdc_apartments WHERE deleted_at is null AND id =" . $userApartment->apartment_id);
                        if(!$getApart) continue;
                        $listCheck[] = $getApart->building_id." - ".$userApartment->apartment_id;
                        echo "kiem tra lai: ". $userApartment->apartment_id. " - ". $getApart->building_id;
                        echo "</br>";

                    }
                    echo "continue";
                    echo "</br>";
                    continue;
                }

                $sql = "SELECT * FROM bdc_v2_user_info WHERE deleted_at is null AND id in (" . implode(",",$listUserAll).") AND full_name = '".$dataProfile->display_name."'";
                $checkExist = $this->sqlSelect($sql);
                if($checkExist) continue;
                $dataProfile = (object) $dataProfile;
                $this->updateUserProfile($listAccept[$i], $dataProfile->display_name, $dataProfile->address, $dataProfile->cmt, $dataProfile->cmt_nc, $dataProfile->cmt_address, "", "", $dataProfile->avatar, $dataProfile->birthday, $dataProfile->gender, 0, $dataProfile->phone, $dataProfile->email);
                $i++;
            }

            foreach ($listAccept as $itemUser) {
                $listHandle[] = $itemUser;
            }
//            dd($data);
        }

        log::dump($listCheck);

        dd($listHandle);
    }

    public function updateUserProfile($idUpdate, $full_name, $address, $cmt_number, $cmt_date, $cmt_address, $cmt_province, $cmt_image, $avatar, $birthday, $gender, $cmt_status, $phone_contact, $email_contact)
    {

        $sql = "UPDATE bdc_v2_user_info SET ";
        $sql .= " full_name = '" . $full_name . "' ";
        $sql .= ", address = '" . $address . "' ";
        $sql .= ", cmt_number = '" . $cmt_number . "' ";
        $sql .= ", cmt_date = '" . $cmt_date . "' ";
        $sql .= ", cmt_address = '" . $cmt_address . "' ";
        $sql .= ", cmt_province = '" . $cmt_province . "' ";
        $sql .= ", cmt_image = '" . $cmt_image . "' ";
        $sql .= ", avatar = '" . $avatar . "' ";
        $sql .= ", birthday = '" . $birthday . "' ";
        $sql .= ", gender = '" . $gender . "' ";
        $sql .= ", cmt_status = '" . $cmt_status . "' ";
        $sql .= ", phone_contact = '" . $phone_contact . "' ";
        $sql .= ", email_contact = '" . $email_contact . "' ";
        $sql .= "  WHERE id=" . $idUpdate;

        Log::dump($sql);
//        dd($sql);
//        return;
        $rows = DB::update($sql);
        return $rows;
    }
}

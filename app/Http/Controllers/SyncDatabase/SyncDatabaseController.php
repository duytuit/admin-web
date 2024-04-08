<?php

namespace App\Http\Controllers\SyncDatabase;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LogImport\LogImport;
use App\Models\PublicUser\UserInfo;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Repositories\Service\ServiceRepository;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Webpatser\Uuid\Uuid;

class SyncDatabaseController extends Controller
{
    use ApiResponse;
    
    private $building;
    private $service;
    private $apartment;

    public function __construct(BuildingRepository $buildingRepository, ServiceRepository $serviceRepository, ApartmentsRespository $apartmentsRespository)
    {
        // //$this->middleware('route_permision');
        $this->building = $buildingRepository;
        $this->service = $serviceRepository;
        $this->apartment = $apartmentsRespository;
    }

    // /sync/service
    public function Service()
    {
        ini_set('max_execution_time', 600);
        $services = DB::connection('mysql2')->select("SELECT * FROM Services");
        $message = "";
        foreach($services as $service)
        {
            try {
                $data["id"] = $service->id;
                $data["bdc_building_id"] = 0;
                $data["bdc_period_id"] = 1;
                $data["name"] = $service->name;
                $data["description"] = $service->description;
                $data["unit"] = "VNĐ";
                $data["bill_date"] = 5;
                $data["payment_deadline"] = 10;
                $data["company_id"] = 1;
                $data["service_code"] = Uuid::generate();
                $data["status"] = 1;
                $data["first_time_active"] = "2017-01-01";
                $data["type"] = $service->type;
                $data["service_group"] = 1;
                DB::insert('insert into bdc_services(id, bdc_building_id, bdc_period_id, name, description, unit, bill_date, payment_deadline, company_id, service_code, status, first_time_active, type, service_group) 
                    values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                    [$data["id"], $data["bdc_building_id"],$data["bdc_period_id"], $data["name"], $data["description"], $data["unit"], $data["bill_date"], $data["payment_deadline"], $data["company_id"], $data["service_code"], $data["status"], $data["first_time_active"], $data["type"], $data["service_group"]]);
            }catch(Exception $e){
                $message .= "<br>Service da ton tai.";
            }            
        }
        
        // $services = DB::connection('mysql2')->select("select Services.*, BillingServices.provider_id from Services
        //     INNER JOIN BillingServices ON BillingServices.service_id=Services.id
        //     where BillingServices.amount > 0 and BillingServices.provider_id in (39, 49, 55, 96)
        //     GROUP BY `id`, `name`, `provider_id`");
        // foreach($services as $service){
        //     switch($service->provider_id)
        //     {
        //         case "39": $buildingId = 13; break;
        //         case "49": $buildingId = 17; break;
        //         case "55": $buildingId = 20; break;
        //         case "96": $buildingId = 37; break;
        //         default: $buildingId = 0; break;
        //     }
        //     try {
        //         $data["bdc_building_id"] = $buildingId;
        //         $data["bdc_period_id"] = 1;
        //         $data["name"] = $service->name;
        //         $data["description"] = $service->description;
        //         $data["unit"] = "VNĐ";
        //         $data["bill_date"] = 5;
        //         $data["payment_deadline"] = 10;
        //         $data["company_id"] = 1;
        //         $data["service_code"] = Uuid::generate();
        //         $data["status"] = 1;
        //         $data["first_time_active"] = "2017-01-01";
        //         $data["type"] = $service->type;
        //         $data["service_group"] = 1;
        //         DB::insert('insert into bdc_services(bdc_building_id, bdc_period_id, name, description, unit, bill_date, payment_deadline, company_id, service_code, status, first_time_active, type, service_group) 
        //             values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
        //             [$data["id"], $data["bdc_building_id"],$data["bdc_period_id"], $data["name"], $data["description"], $data["unit"], $data["bill_date"], $data["payment_deadline"], $data["company_id"], $data["service_code"], $data["status"], $data["first_time_active"], $data["type"], $data["service_group"]]);
        //     }catch(Exception $e){
        //         $message .= "<br>Service da ton tai.";
        //     }            
        //     $apartments = DB::select("SELECT * FROM bdc_apartments WHERE building_id = $buildingId");
        //     foreach($apartments as $apartment){
        //         DB::insert('insert into bdc_apartment_service_price(
        //             bdc_service_id, 
        //             bdc_price_type_id, 
        //             bdc_apartment_id, 
        //             name, 
        //             price, 
        //             first_time_active, 
        //             last_time_pay, 
        //             bdc_vehicle_id, 
        //             bdc_building_id,
        //             bdc_progressive_id,
        //             description,
        //             floor_price) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
        //             [$service->id, 1, $apartment->id, $service->name, 0, '2018-03-12', '2019-03-12', 0, $buildingId, 0, $service->description, 0]);
        //     }
        // }
        $message .= "<br>Done.";
        echo $message;
    }

    // /sync/apartment-service-price?building_id=13
    public function ApartmentervicePrice(Request $request)
    {
        $input = $request->all();
        $buildingId = $input["building_id"];
        $message = "";
        switch($buildingId)
        {
            case "13": $_buildingId = 39; break;
            case "17": $_buildingId = 49; break;
            case "20": $_buildingId = 55; break;
            case "37": $_buildingId = 96; break;
            default: $_buildingId = 0; break;
        }
        $services = DB::connection('mysql2')->select("select Services.*, BillingServices.provider_id from Services
        INNER JOIN BillingServices ON BillingServices.service_id=Services.id
        where BillingServices.amount > 0 and BillingServices.provider_id in ($_buildingId)
        GROUP BY `id`, `name`, `provider_id`");
        foreach($services as $service){
            try {
                $data["bdc_building_id"] = $buildingId;
                $data["bdc_period_id"] = 1;
                $data["name"] = $service->name;
                $data["description"] = $service->description;
                $data["unit"] = "VNĐ";
                $data["bill_date"] = 5;
                $data["payment_deadline"] = 10;
                $data["company_id"] = 1;
                $data["service_code"] = Uuid::generate();
                $data["status"] = 1;
                $data["first_time_active"] = "2017-01-01";
                $data["type"] = $service->type;
                $data["service_group"] = 1;
                DB::insert('insert into bdc_services(bdc_building_id, bdc_period_id, name, description, unit, bill_date, payment_deadline, company_id, service_code, status, first_time_active, type, service_group) 
                    values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                    [$data["bdc_building_id"],$data["bdc_period_id"], $data["name"], $data["description"], $data["unit"], $data["bill_date"], $data["payment_deadline"], $data["company_id"], $data["service_code"], $data["status"], $data["first_time_active"], $data["type"], $data["service_group"]]);
            }catch(Exception $e){
                $message .= "<br>Service da ton tai.";
            }        

            $apartments = DB::select("SELECT * FROM bdc_apartments WHERE building_id = $buildingId");
            
            foreach($apartments as $apartment){
                // $ap_id = LogImport::where([
                //     'old_id' => $apartment->id,
                //     'new_table'=> 'bdc_apartments',
                //     'old_table'=> 'Apartments'
                // ])->first();
                
                // $apartmentId = $ap_id != null ? $ap_id->new_id

                DB::insert('insert into bdc_apartment_service_price(
                    bdc_service_id, 
                    bdc_price_type_id, 
                    bdc_apartment_id, 
                    name, 
                    price, 
                    first_time_active, 
                    last_time_pay, 
                    bdc_vehicle_id, 
                    bdc_building_id,
                    bdc_progressive_id,
                    description,
                    floor_price) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                    [$service->id, 1, $apartment->id, $service->name, 0, '2018-03-12', '2019-03-12', 0, $buildingId, 0, $service->description, 0]);
            }
        }
        echo $message . "<br>Done.";
    }

    // /sync/bills?building_id=13
    public function Bills(Request $request)
    {
        ini_set('max_execution_time', 600);
        $buildingIds = [13, 17, 20, 37];
        $input = $request->all();
        $buildingId = $input["building_id"];
        $page = $input != null && $input["page"] != null ? $input["page"] : 0;
        $limit =  2000;
        $offset =  $page * $limit;
        // foreach($buildingIds as $buildingId){
            $billings = DB::connection('mysql2')->select("SELECT * FROM Billings WHERE apartment_id IN (SELECT id FROM Apartments WHERE building_id=$buildingId) LIMIT $limit OFFSET $offset");
            
            foreach($billings as $billing){
                $ap_id = LogImport::where([
                    'old_id' => $billing->apartment_id,
                    'new_table'=> 'bdc_apartments',
                    'old_table'=> 'Apartments'
                ])->first();

                $data["id"] = $billing->id;
                $data["bdc_apartment_id"] = $ap_id->new_id;
                $data["bdc_building_id"] = $buildingId;
                $data["bill_code"] = $billing->billing_code;
                $data["cost"] = $billing->total;
                $data["customer_name"] = $billing->customer_code != null ? $billing->customer_code : "";
                $data["customer_address"] = "";
                $data["deadline"] = $billing->expiredAt != null ? $billing->expiredAt : $billing->createdAt;
                $data["is_vat"] = 0;
                $data["status"] = 2;
                $data["notify"] = 0;
                $data["created_at"] = $billing->createdAt;
                $data["updated_at"] = $billing->updatedAt;
                $data["cycle_name"] = $billing->bill_period != null ? $billing->bill_period : "";
                $data["confirm_date"] = $billing->updatedAt;
                $data["cost_free"] = 0;
                // dd($data);
                DB::insert('insert into bdc_bills(
                    id,
                    bdc_apartment_id, 
                    bdc_building_id, 
                    bill_code, 
                    cost, 
                    customer_name, 
                    customer_address, 
                    deadline, 
                    is_vat, 
                    status,
                    notify,
                    created_at,
                    updated_at,
                    cycle_name,
                    confirm_date,
                    cost_free) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                    [
                        $data["id"], 
                        $data["bdc_apartment_id"], 
                        $data["bdc_building_id"], 
                        $data["bill_code"], 
                        $data["cost"], 
                        $data["customer_name"], 
                        $data["customer_address"], 
                        $data["deadline"], 
                        $data["is_vat"], 
                        $data["status"], 
                        $data["notify"], 
                        $data["created_at"],
                        $data["updated_at"],
                        $data["cycle_name"],
                        $data["confirm_date"],
                        $data["cost_free"]
                    ]);
                // $bill = DB::table('bdc_bills')->insertGetId($data);
            }
        // }
        echo "Done.";
    }

    ///sync/debit-detail?building_id=96&page=2
    public function DebitDetail(Request $request)
    {
        ini_set('max_execution_time', 600);
        $input = $request->all();
        $page = $input != null && isset($input["page"]) ? $input["page"] : 0;
        $limit =  $input != null && isset($input["limit"]) ? $input["limit"] : 0;;
        $offset =  $page * $limit;

        $buildingId = $input["building_id"];
        $bills = collect(DB::select("SELECT * FROM bdc_bills"));
        $debitDetails = DB::connection('mysql2')->select("SELECT * FROM BillingServices LIMIT $limit OFFSET $offset");
        switch($buildingId)
        {
            case "39": $_buildingId = 13; break;
            case "49": $_buildingId = 17; break;
            case "55": $_buildingId = 20; break;
            case "96": $_buildingId = 37; break;
            default: $_buildingId = 0; break;
        }

        $apartmentServicePrices = collect(DB::select("SELECT * FROM bdc_apartment_service_price"));
        
        foreach($debitDetails as $debitDetail)
        {
            $bill = $bills->where("id", "=", $debitDetail->billing_id)->first();
            if($bill == null){
                echo "<br>$debitDetail->billing_id";
                continue;
            }
            
            // WHERE bdc_service_id = $debitDetail->service_id AND bdc_apartment_id = $debitDetail->bdc_apartment_id
            $serviceId = $debitDetail->service_id != null ? $debitDetail->service_id : 0;
            $apartmentId = $bill->bdc_apartment_id != null ? $bill->bdc_apartment_id : 0;
            
            $apartmentServicePrice = $apartmentServicePrices->where("bdc_service_id", "=", $serviceId)->where("bdc_apartment_id", "=", $apartmentId)->first();
            if($apartmentServicePrice == null)
            {
                continue;
            }
            $data["bdc_building_id"] = $apartmentServicePrice->bdc_building_id;
            $data["bdc_bill_id"] = $debitDetail->billing_id;
            $data["bdc_apartment_id"] = $apartmentId;
            $data["bdc_service_id"] = $serviceId;
            $data["bdc_apartment_service_price_id"] = $apartmentServicePrice->id;
            $data["title"] = $apartmentServicePrice->name;
            $data["sumery"] = $debitDetail->amount;
            $data["from_date"] = $debitDetail->createdAt;
            $data["to_date"] = $debitDetail->createdAt;
            $data["detail"] = "[]";
            $data["version"] = 0;
            $data["new_sumery"] = 0;
            $data["previous_owed"] = 0;
            $data["paid"] = $debitDetail->amount;
            $data["created_at"] = $debitDetail->createdAt;
            $data["updated_at"] = $debitDetail->updatedAt;
            $data["is_free"] = 0;
            $data["cycle_name"] = 2;
            $data["quantity"] = 1;
            $data["price"] = $debitDetail->amount;
            $data["bdc_price_type_id"] = 1;
            $id = DB::table('bdc_debit_detail')->insertGetId($data);
        }
        echo "Done.";
    }

    // /sync/receipt?page=0
    public function Receipt(Request $request)
    {
        ini_set('max_execution_time', 600);
        $buildingIds = [13, 17, 20, 37];
        $input = $request->all();
        $page = $input != null && $input["page"] != null ? $input["page"] : 0;
        $limit =  2500;
        $offset =  $page * $limit;
        $accountings = DB::connection('mysql2')->select("SELECT `Accountings`.*, `Apartments`.`name` as `apartment_name`, `Apartments`.`building_id` 
            FROM `Accountings` INNER JOIN `Apartments` ON `Apartments`.`id`=`Accountings`.`apartment_id` 
            WHERE `Apartments`.`building_id` IN (13, 17, 20, 37) 
            LIMIT $limit OFFSET $offset");
        foreach($accountings as $accounting){
            if($accounting->accounting_code == null) continue;

            $ap_id = LogImport::where([
                'old_id' => $accounting->apartment_id,
                'new_table'=> 'bdc_apartments',
                'old_table'=> 'Apartments'
            ])->first();
            
            $receipt = collect(DB::select("SELECT * FROM bdc_receipts WHERE receipt_code='$accounting->accounting_code'"))->first();
            $billIds = array();
            if(empty($receipt)){
                array_push($billIds, $accounting->billing_id);
                $strBillIds = serialize($billIds);
                $data["bdc_building_id"] = $accounting->building_id;
                $data["bdc_apartment_id"] = $ap_id->new_id;
                $data["receipt_code"] = $accounting->accounting_code != null ? $accounting->accounting_code : 0;
                $data["cost"] = $accounting->amount;
                $data["customer_name"] = $accounting->name != null ? $accounting->name : "";
                $data["customer_address"] = $accounting->apartment_name;
                $data["provider_address"] = "";
                $data["bdc_receipt_total"] = "";
                $data["created_at"] = $accounting->createdAt;
                $data["updated_at"] = $accounting->updatedAt;
                $data["bdc_bill_id"] = $strBillIds;
                $data["type_payment"] = "tien_mat";
                $data["description"] = $accounting->note;
                $data["url"] = "";
                $data["user_id"] = $accounting->createdBy != null ? $accounting->createdBy : 0;
                $data["type"] = "phieu_thu";
                $data["status"] = $accounting->status;
                DB::table('bdc_receipts')->insert($data);
            }else{
                $array = unserialize($receipt->bdc_bill_id);
                $key = array_search($accounting->billing_id, $array);
                if($key > 0){
                    array_push($array, $accounting->billing_id);
                    $strBillIds = serialize($array);
                    DB::update("UPDATE bdc_receipts SET bdc_bill_id = ? WHERE receipt_code = ?", [$strBillIds , $accounting->accounting_code]);
                }
            }
            
        }
        echo "Done.";
    }

    // /sync/update-service?service_old=1&service_new=2&building_id=1
    public function UpdateServiceId(Request $request)
    {
        $input = $request->all();
        $serviceOld = $input["service_old"];
        $serviceNew = $input["service_new"];
        $buildingId = $input["building_id"];
        DB::update('update bdc_apartment_service_price set bdc_service_id = ? where bdc_service_id = ? and bdc_building_id = ?', [$serviceNew, $serviceOld, $buildingId]);
        DB::update('update bdc_debit_detail set bdc_service_id = ? where bdc_service_id = ? and bdc_building_id = ?', [$serviceNew, $serviceOld, $buildingId]);
        echo "Done.";
    }

    // /sync/update-order-status
    public function UpdateOrderStatus(Request $request)
    {
        ini_set('max_execution_time', 600);
        $input = $request->all();
        $page = $input != null && $input["page"] != null ? $input["page"] : 0;
        $limit =  2500;
        $offset =  $page * $limit;
        $bills = DB::connection('mysql2')->select("SELECT * FROM Billings WHERE id NOT IN (SELECT billing_id FROM Accountings WHERE billing_id <> 0)
            LIMIT $limit OFFSET $offset");
        foreach($bills as $bill)
        {
            DB::update('UPDATE bdc_bills SET status = 1 WHERE id = ?', [$bill->id]);
            DB::update('UPDATE bdc_debit_detail SET paid = 0 WHERE bdc_bill_id = ?', [$bill->id]);
        }
        echo "Done.";
    }

    public function CreateServicePriceDefault()
    {

    }

    public function Building()
    {
        $buildings = DB::connection('mysql2')->select("select * from Buildings");
        foreach($buildings as $building)
        {
            $data["id"] = $building->id;
            $data["name"] = $building->name;
            $data["description"] = $building->description;
            $data["address"] = $building->address;
            $data["phone"] = $building->mobile;
            $data["email"] = $building->email;
            $this->building->create($data);
        }
        echo "Done.";
    }

    public function buildings()
    {
        $buildings = \App\Models\Building\Building::all();
        return $this->responseSuccess($buildings->toArray());
    }

    public function Apartments()
    {
        $apartments = DB::connection('mysql2')->select("select * from Apartments");
        
        foreach($apartments as $apartment)
        {
            $data["id"] = $apartment->id;
            $data["building_id"] = $apartment->building_id != null ? $apartment->building_id : 0;
            $data["name"] = $apartment->name;
            $data["description"] = $apartment->description;
            $data["floor"] = $apartment->floor_number != null ? $apartment->floor_number : 0;
            $data["status"] = $apartment->status;
            $data["area"] = $apartment->area;
            DB::insert('insert into bdc_apartments (id, building_id, name, description, floor, status, area) values (?, ?, ?, ?, ?, ?, ?)', 
            [$apartment->id, $data["building_id"], $apartment->name, $apartment->description, $data["floor"], $data["status"], $data["area"]]);
        }
        
        echo "Done.";
    }

    public function UpdateCostBill(Request $request)
    {
        $input = $request->all();
        $buildingId = $input["building_id"];
        $serviceId = $input["service_id"];
        $debitDetails = DB::select("SELECT * FROM bdc_debit_detail WHERE bdc_building_id = $buildingId AND bdc_service_id = $serviceId");
        foreach($debitDetails as $_debitDetail)
        {
            $totalPrice = 0;
            $billId = @$_debitDetail->bdc_bill_id;
            $detail = json_decode(@$_debitDetail->detail);
            foreach(@$detail->data as $data)
            {
                $totalPrice += @$data->total_price; 
            }
            $current = Carbon::now();
            DB::update('update bdc_bills set cost = ?, updated_at = ? where id = ?', [$totalPrice, $current, $billId]);
        }
        echo "Done";
    }

    public function UpdateSumeryDebitDetail(Request $request)
    {
        $input = $request->all();
        $buildingId = $input["building_id"];
        $serviceId = $input["service_id"];
        $debitDetails = DB::select("SELECT * FROM bdc_debit_detail WHERE bdc_building_id = $buildingId AND bdc_service_id = $serviceId");
        foreach($debitDetails as $_debitDetail)
        {
            $totalPrice = 0;
            $id = @$_debitDetail->id;
            $detail = json_decode(@$_debitDetail->detail);
            foreach(@$detail->data as $data)
            {
                $totalPrice += @$data->total_price; 
                echo "$totalPrice - $data->total_price - ";
            }
            $current = Carbon::now();
            echo "$id - $totalPrice<br>";
            DB::update('update bdc_debit_detail set sumery = ?, updated_at = ? where id = ?', [$totalPrice, $current, $id]);
        }
        echo "Done";
    }

    public function updateAcc(Request $request)
    {   
        $limit = isset($request->limit) && $request->limit > 0 ? $request->limit : 10;
        $u = UserInfo::where('type', 1)->where(function($query) use ($request) {
            if(isset($request->bid) && $request->bid != null) {
                $query->where('bdc_building_id', $request->bid);
            }
        })->paginate($limit);
        $arr = [];
        foreach($u as $_u) {
            array_push($arr, $_u);
        }
        return $this->responseSuccess($arr);
    }
}

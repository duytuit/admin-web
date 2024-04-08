<?php

namespace App\Console\Commands;

// use App\Repositories\Assets\AssetRepository;
use App\Services\MaintenanceQueueService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\LogImport\LogImport;

class ConvertData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bdc:convertData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert data';
    private $newDataBase;
    private $oldDatabase;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $assetRepository;

    public function __construct()
    {
        parent::__construct();
        $this->newDataBase ='mysql';
        $this->oldDatabase ='mysql2';
    }

    private function mapFieldsUser()
    {
        //table User, Residents, Publishers
        return [
            'email' => 'email',
            'password'=> 'password',
            'mobile'=>'mobile',
            'updatedAt'=>'updated_at',
            'createdAt'=>'created_at',
        ];
    }

    private function mapFieldsStaff()
    {
        //table User, Residents, Publishers
        return [
            'id'=>'pub_user_id',
            'name' => 'name',
            'email'=>'email',
            'mobile'=> 'phone',
            'birthday'=>'birthday',
            'updatedAt'=>'updated_at',
            'createdAt'=>'created_at',
        ];
    }

    private function mapFieldsProfile()
    {
        //table User, Residents, Publishers
        return [
            'name' => 'display_name',
            'mobile'=> 'phone',
            'email'=>'email',
            'birthday'=>'birthday',
            'sex'=>'gender',
            'avatar'=>'avatar',
            'id_passport'=>'cmt',
            'permanent_residence'=>'address',
            'id'=>'pub_user_id',
            'updatedAt'=>'updated_at',
            'createdAt'=>'created_at',
        ];
    }

    private function mapFieldsProfile2()
    {
        //table User, Residents, Publishers
        return [
            'name' => 'display_name',
            'mobile'=> 'phone',
            'email'=>'email',
            'birthday'=>'birthday',
            'sex'=>'gender',
            'avatar'=>'avatar',
            'id_passport'=>'cmt',
            'permanent_residence'=>'address',
            'id'=>'pub_user_id',
            'apartment_id'=>'bdc_building_id',
            'updatedAt'=>'updated_at',
            'createdAt'=>'created_at',
        ];
    }

    private function mapFieldsApartments()
    {
        //table User, Residents, Publishers
        return [
            'name' => 'name',
            'floor_number'=> 'floor',
            'description'=>'description',
            'area'=>'area',
            'status'=>'status',
            'building_id'=>'building_id',

            'updatedAt'=>'updated_at',
            'createdAt'=>'created_at',
        ];
    }

    private function mapFieldsMeans()
    {
        //table Means=>bdc_verhicles
        return [
            'title' => 'name',
            'description'=> 'description',
            'number'=>'number',
            'apartment_id'=>'bdc_apartment_id',
            'type'=>'vehicle_category_id',
            'updatedAt'=>'updated_at',
            'createdAt'=>'created_at',
        ];
    }

    private function mapFieldsNotifications()
    {
        //table Notifications=> posts
        return [
            'name' => 'title',
            'category_id'=> 'category_id',
            'content'=>'content',
            // 'poll'=>'poll_options',
            'notification_time'=>'publish_at',
            'file'=>'attaches',
            'type'=>'category_id',
            'isActive'=>'status',
            'building_id'=>'bdc_building_id',
            'img'=>'image',
            'updatedAt'=>'updated_at',
            'createdAt'=>'created_at',
        ];
    }

    private function mapFieldsComment()
    {
        //table NotificationComments=> posts
        return [
            'notification_id'=>'post_id',
            'comment'=>'content',
            'censor_status'=>'status',
            'reply_to_object'=>'parent_id',
            'object_from_id'=>'user_id',
            'object_from'=>'object',

            'updatedAt'=>'updated_at',
            'createdAt'=>'created_at',
        ];
    }
    private function mapFieldsCommentFeedbacks()
    {
        //table ResidentPetitionFeedbacks=>comment
        return [
            'resident_petition_id'=>'post_id',
            'feedback'=>'content',
            'object_from_id'=>'user_id',
            'object_from'=>'object',

            'updatedAt'=>'updated_at',
            'createdAt'=>'created_at',
        ];
    }

    private function mapFieldsResidentPetitions()
    {
        //table ResidentPetitions=>feedback
        return [
            'resident_id'=>'pub_user_profile_id',
            'building_id'=>'bdc_building_id',
            // 'city_id'=>'building_id',
            'title'=>'title',
            'content'=>'content',
            'status'=>'status',

            'updatedAt'=>'updated_at',
            'createdAt'=>'created_at',
        ];
    }


    private function mapFieldsCustomer()
    {
        //table User, Residents, Publishers
        return [
            'apartment_id' => 'bdc_apartment_id',//apartment_id
            'id'=> 'pub_user_profile_id',
            'isHouseholdHead'=>'type'
        ];
    }

    private function mapFieldsBuldingPlace()
    {
        //table User, Residents, Publishers
        return [
            'name' => 'name',
            'description'=> 'description',
            'address'=>'address',
            'mobile'=>'mobile',
            'email'=>'email',
            'status'=>'status',
            'createdAt'=>'created_at',
            'updatedAt'=>'updated_at',
            'city_id'=>'bdc_building_id'
        ];
    }
    private function mapFieldsBulding()
    {
        //table User, Residents, Publishers
        return [
            'name' => 'name',
            'description'=> 'description',
            'address'=>'address',
            'createdAt'=>'created_at',
            'updatedAt'=>'updated_at',
        ];
    }

    private function mapFieldsDepartments()
    {
        //table User, Residents, Publishers
        return [
            'name' => 'name',
            'code'=> 'code',
            'description'=>'description',
            'mobile'=>'phone',
            'email'=>'email',
            'building_id'=>'bdc_building_id',
            'createdAt'=>'created_at',
            'updatedAt'=>'updated_at',
            'status'=>'status'
        ];
    }
    private function mapFieldsPaymentInfo()
    {
        //table User, Residents, Publishers
        return [
            'bank_name' => 'bank_name',
            'bank_number'=> 'bank_account',
            'bank_branch'=>'branch',
            'createdAt'=>'created_at',
            'updatedAt'=>'updated_at',
            'bank_owner'=>'holder_name'
        ];
    }

    private function buildDataInsert($data, $mapFields)
    {
        $new_data =[];
        foreach ($mapFields as $from => $to) {
            if ( $from == 'password') {
                $new_data[$to] = str_replace('$2a$', '$2y$', $data->$from);
            }else{
                if (isset($data->$from)) {
                    $new_data[$to] = $data->$from;
                    // if ($from == 'isHouseholdHead') {
                    //     $new_data['id'] = $new_data['bdc_apartment_id'];
                    // }
                }

            }
        }



        return $new_data;
    }

    private function constantObject()
    {
        return [
            'resident'=>'Residents',
            'user'=>'Users'
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->convertUsers();
    }

    private function convertUsers()
    {
        /**
        * Convert sang pub_users
        *
        $old_table_name = 'Residents';
        $old_table_name = 'Publishers';
        $old_table_name = 'Users';
        $new_table_name = 'pub_users';
        *=============================
        *
        */
        // //
        // $old_table_name = 'Users';
        // $new_table_name = 'pub_users';
        //TRUNCATE 'pub_users','pub_user_profile','bdc_apartments','bdc_vehicles','posts','comments','feedback','bdc_building','bdc_bulding_place','bdc_department','bdc_payment_info','bdc_customers'
        // $this->convertData("Users", 'pub_users', $this->mapFieldsUser());
        // $this->convertData("Users", 'pub_user_profile', $this->mapFieldsProfile(), ['pub_user_id'=>['old_table'=>'Users','new_table'=>'pub_users']], ['type_profile'=>0,'type'=>2,'app_id'=>'buildingcare']);

        // // $this->convertData("Users", 'bdc_company_staff', $this->mapFieldsStaff(), ['pub_user_id'=>'Users'], ['type'=>1,'active'=>1, 'bdc_company_id'=>1]);
        // // $this->convertData("Cities", 'bdc_building', $this->mapFieldsBulding(), null, ['company_id'=>1]);
        // $this->convertData("Departments", 'bdc_department', $this->mapFieldsDepartments(),['bdc_building_id'=>'Buildings']);
        // $this->convertData("ProviderBanks", 'bdc_payment_info', $this->mapFieldsPaymentInfo());

        // $this->convertData("Apartments", 'bdc_apartments', $this->mapFieldsApartments());


        // $this->convertData("Residents", 'pub_users', $this->mapFieldsUser());
        // $this->convertData("Residents", 'pub_user_profile', $this->mapFieldsProfile2(),['pub_user_id'=>['old_table'=>'Residents','new_table'=>'pub_user_profile']], ['type'=>1,'type_profile'=>0,'app_id'=>'buildingcare'], null, ['table_name'=>'Apartments', 'field'=>'id','from'=>'building_id', 'to'=>'bdc_building_id']);


        // $this->convertData("Means", 'bdc_vehicles', $this->mapFieldsMeans(),['bdc_apartment_id'=>'Apartments']);

        // $this->convertData("Notifications", 'posts', $this->mapFieldsNotifications(),null,['notify'=>'{"send_app": "1","all_selected": "1"}','type'=>'article'], 'attaches');
        $this->convertData("NotificationComments", 'comments', $this->mapFieldsComment(), ['user_id'=>'object', 'post_id'=>'Notifications']);

        $this->convertData("ResidentPetitions", 'feedback', $this->mapFieldsResidentPetitions(),['pub_user_profile_id'=>['old_table'=>'Users','new_table'=>'pub_user_profile']], ['type'=>'fback']);

        $this->convertData("ResidentPetitionFeedbacks", 'comments', $this->mapFieldsCommentFeedbacks(),['user_id'=>'object', 'post_id'=>'ResidentPetitions'], ['type'=>'feedback']);

        // $this->convertData("Residents", 'bdc_customers', $this->mapFieldsCustomer(),['bdc_apartment_id'=>['old_table'=>'Apartments', 'new_table'=>'bdc_apartments'], 'pub_user_profile_id'=>['old_table'=>'Residents','new_table'=>'pub_user_profile']]);

    }

    private function convertData($old_table_name, $new_table_name, $mapFields, $changeData=NULL, $merge = NULL, $attaches=null, $buiding = null)
    {
        $limit = 2000;
        $offset = 0;

        do {
            $data = null;
            $data = $this->getOldData($limit, $offset, $old_table_name);
            if (!count($data)) {
                break;
            }

            foreach ($data as $key => $value) {
                $new_data = $this->buildDataInsert($value, $mapFields);

                if ($changeData) {
                    $new_data = $this->changeData($changeData, $new_data);
                }
                if ($merge) {
                   $new_data = array_merge($new_data, $merge );
                }

                if ($attaches) {
                    $rs = $this->jsonFields($attaches, $new_data);
                    $new_data = array_merge($new_data, $rs );
                }

                if ($buiding) {
                    $new_data = $this->getBuildingId($buiding, $new_data);
                }

                // dd($new_data);
                $new_id = $this->insertNewDB($new_data, $new_table_name);

                $this->logNewIdOfRow($new_id, $value->id, $old_table_name, $new_table_name);
            }

            $offset = $limit + $offset;
            # code...
        } while ( $data != null);
    }

    private function getBuildingId($buiding, $new_data)
    {
        try {
            $old = $new_data;
            if (is_array($buiding)) {
                if (!isset($new_data[$buiding['to']])) {
                    return $new_data;
                }
                $rs =DB::connection($this->oldDatabase)->table($buiding['table_name'])->where($buiding['field'], $new_data[$buiding['to']])
                    ->first();
                    $from =$buiding['from'];
                if ($rs) {
                    $new_data[$buiding['to']] =  $rs->$from;
                }else {
                    $new_data[$buiding['to']] = 0;
                }
            }
        } catch (\Exception $e) {
            print_r($rs);
            print_r($buiding);
            print_r($new_data);
            die($e->getMessage());
        }
        if ($new_data['bdc_building_id'] > 50) {
            echo "========3=======";
            print_r($old);
            print_r($new_data);
            echo "=======3========";
        }



        return $new_data;
    }

    private function jsonFields($field, $data)
    {

        if (isset($data[$field])) {
           return [ $field => '{"0": {"src": "'.$data[$field].'", "sort_order": "1"}}'];
        }
        return [$field => Null];

    }

    private function changeData($changeData, $new_data)
    {
        foreach ($changeData as $field => $old_table_name) {
            if ($old_table_name =='object') {
                $table = $this->constantObject();
                // $old_table_name = $table[$new_data[$old_table_name]];
                $old_table_name = ['old_table'=>$table[$new_data[$old_table_name]],'new_table'=>'user_id'];
                unset($new_data["object"]);

                // dd($new_data);
            }
            if (isset($new_data[$field])) {

                if (is_array($old_table_name)) {
                    $check = $this->getNewIdOfData2($new_data[$field], $old_table_name['old_table'], $old_table_name['new_table']);
                }else{
                    print_r($old_table_name);
                    echo "\n so 2";
                    $check = $this->getNewIdOfData($new_data[$field], $old_table_name);

                }
                // die;
                if ($check) {
                    $new_data[$field] = $check->new_id;
                }
            }
        }


        return $new_data;
    }


    private function getOldData($limit, $offset, $old_table_name)
    {
        return DB::connection($this->oldDatabase)->table($old_table_name)->offset($offset)
                ->where('building_id', [13,17,20,28,30,37])
                ->limit($limit)
                ->get();
    }

    private function insertNewDB($data, $new_table_name)
    {
        $tmp= $this->checkDuplicate($data, $new_table_name);
        // dd($new_table_name);
        // dd(in_array(  $new_table_name, array('pub_users', 'pub_user_profile')));
        if( $tmp && !in_array( $new_table_name =='pub_users')) {
             echo "\n Data này đã có trong table ".$new_table_name." row id: ". $tmp->id;
            return $tmp->id;

        }
        return DB::connection($this->newDataBase)->table($new_table_name)->insertGetId($data);
    }

    private function checkDuplicate($data, $new_table_name)
    {
        return DB::connection($this->newDataBase)->table($new_table_name)->where($data)->first();
    }

    private function logNewIdOfRow($new_id, $old_id, $old_table_name, $new_table_name)
    {
        print_r([
            'new_id' => $new_id,
            'old_id' => $old_id,
            'old_table'=>$old_table_name,
            'new_table'=>$new_table_name
        ]);
        // echo "\nlogNewIdOfRow new_id". $new_id. ' old_id: '. $old_id.' old_table_name: '. $old_table_name .' new_table_name: '. $new_table_name;
        return LogImport::create([
            'new_id' => $new_id,
            'old_id' => $old_id,
            'old_table'=>$old_table_name,
            'new_table'=>$new_table_name
        ]);
    }

    private function getNewIdOfData($old_id, $old_table_name){
        // echo "\ngetNewIdOfData old_id". $old_id. ' old_table_name: '. $old_table_name;
        return LogImport::where([
            'old_id' => $old_id,
            'old_table'=>$old_table_name
        ])->first();
    }
    private function getNewIdOfData2($old_id, $old_table_name, $new_table){
        // echo "\ngetNewIdOfData2 old_id: ". $old_id.' old_table_name: '. $old_table_name .' new_table: '. $new_table;
        return LogImport::where([
            'old_id' => $old_id,
            'new_table'=>$new_table,
            'old_table'=>$old_table_name
        ])->first();
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LogExportService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Models\LogImport\LogImport;
use Illuminate\Support\Facades\DB;

class UpdatePosts extends Command
{
  protected $signature = 'bdc:updateDataPost';

  protected $description = 'UpdatePosts';

  protected $log_export_repository;

    private $newDataBase;
    private $oldDatabase;

  public function __construct()
  {
        parent::__construct();
        $this->newDataBase ='mysql';
        $this->oldDatabase ='old_mysql';
  }

  public function handle()
  {
    $this->convertData();

  }

  private function convertData()
    {
        $limit = 1000;
        $offset = 0;
        /**
        * Convert sang pub_users
        *
        $old_table_name = 'Apartments';
        $old_table_name = 'Publishers';
        $old_table_name = 'Users';
        $new_table_name = 'pub_users';
        *=============================
        *
        */
        //

        do {
            $data = null;
            $data = $this->getListData($limit, $offset, 'bdc_apartments');
            if (!count($data)) {
                break;
            }

            foreach ($data as $value) {

                // print_r($value);
                // echo "\n========";

                $rs = $this->getOldIdOfData($value->id, 'Apartments', 'bdc_apartments');
                if (!$rs ) {
                    continue;
                }
                $oldData = $this->getOldData($rs->old_id, 'Apartments');
                if ($oldData ) {
                    if ( $oldData->status == 1 ) {
                        DB::connection($this->newDataBase)->table('bdc_apartments')->where('id',$value->id)->update(['status'=> 3]);
                    }elseif ($oldData->status == 2) {
                        DB::connection($this->newDataBase)->table('bdc_apartments')->where('id',$value->id)->update(['status'=> 0]);
                    }elseif ($oldData->status == 3) {
                        DB::connection($this->newDataBase)->table('bdc_apartments')->where('id',$value->id)->update(['status'=> 1]);
                    }elseif ($oldData->status == 4) {
                        DB::connection($this->newDataBase)->table('bdc_apartments')->where('id',$value->id)->update(['status'=> 2]);
                    }else {
                        DB::connection($this->newDataBase)->table('bdc_apartments')->where('id',$value->id)->update(['status'=> 0]);
                    }

                    echo "\nid ". $oldData->status;

                }

            }
            $offset = $limit + $offset;
            # code...
        } while ( $data != null);
    }

    private function getListData($limit, $offset, $table)
    {
        return DB::connection($this->newDataBase)->table($table)->offset($offset)
                ->limit($limit)
                ->get();
    }

    private function getOldData($id, $old_table_name)
    {
        return DB::connection($this->oldDatabase)->table($old_table_name)->find($id);
    }

    private function getOldIdOfData($new_id, $old_table_name, $new_table){
        return LogImport::where([
            'new_id' => $new_id,
            'new_table'=>$new_table,
            'old_table'=>$old_table_name
        ])->first();
    }

    private function getNewIdOfData($old_id, $old_table_name, $new_table){
        $rs = [];
        foreach ($old_id as $value) {
           $r =  LogImport::where([
            'old_id' => (int)$value,
            'new_table'=>$new_table,
            'old_table'=>$old_table_name
        ])->first();

           if ($r) {
            if ($new_table =='pub_users') {
                $rs[]= $this->getProfileId($r->new_id);
            }
            $rs[]= '"'.$r->new_id. '"';
           }
        }

        return $rs ;
    }

    private function getProfileId($user_id)
    {
        $rs = DB::connection($this->newDataBase)->table('pub_user_profile')->where('pub_user_id', $user_id)->first();
        if ($rs) {
           return '"'.$rs->id. '"';
        }
        return 0;
    }
}
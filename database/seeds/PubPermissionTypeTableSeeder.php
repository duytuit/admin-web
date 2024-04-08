<?php

use App\Models\Permissions\PubPermissionType;
use Illuminate\Database\Seeder;

class PubPermissionTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        PubPermissionType::truncate();
        PubPermissionType::create([
            'name'=>'Danh sách hiển thị bản ghi',
            'description'=>'Bao Gồm các trang danh sách hiển thị của module',
            'parent'=>0,
            'status'=>1,
        ]);
        PubPermissionType::create([
            'name'=>'Thêm mới bản ghi',
            'description'=>'Bao Gồm các trang thêm mới của module',
            'parent'=>0,
            'status'=>1,
        ]);
        PubPermissionType::create([
            'name'=>'Sửa chữa bản ghi',
            'description'=>'Bao Gồm các trang sửa chữa của module',
            'parent'=>0,
            'status'=>1,
        ]);
        PubPermissionType::create([
            'name'=>'Xóa bản ghi',
            'description'=>'Bao Gồm các trang xóa bản ghi thị của module',
            'parent'=>0,
            'status'=>1,
        ]);
        PubPermissionType::create([
            'name'=>'Tác vụ khác',
            'description'=>'Bao Gồm các trang tác vụ khác của module',
            'parent'=>0,
            'status'=>1,
        ]);
    }
}

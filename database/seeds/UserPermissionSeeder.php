<?php

use App\Models\PublicUser\Users;
use Illuminate\Database\Seeder;
use App\Models\PublicUser\UserPermission;
use App\Models\Permissions\Permisison;
use App\Models\PublicUser\UserInfo;

class UserPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserInfo::truncate();
        UserPermission::truncate();
        $permissions = Permisison::all();
        $ids = $permissions->pluck('id')->toArray();
        UserPermission::where('pub_user_id', 1)->delete();
        UserPermission::create([
            'pub_user_id' => 1,
            'permissions' => serialize($ids)
        ]);

        UserInfo::create([
           'pub_user_id' => 1,
            'display_name' => 'Admin Tòa 1',
            'email' => 'admin@dxmb',
            'bdc_building_id' => 1,
            'app_id' => 'buildingcare',
            'status' => 1,
            'type'=>Users::USER_WEB
        ]);
    }
}

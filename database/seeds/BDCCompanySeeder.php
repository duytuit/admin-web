<?php

use Illuminate\Database\Seeder;
use App\Models\Building\Company;
use App\Models\Building\CompanyStaff;
use App\Models\Building\Building;

class BDCCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Company::truncate();
        CompanyStaff::truncate();

        Company::create([
            'name' => 'Đất Xanh Miền Bắc',
            'code' => 'DXMB',
            'type' => 1,
            'admin_id' => 1
        ]);

        CompanyStaff::create([
            'bdc_company_id' => '1',
            'pub_user_id' => '1',
            'type' => 1,
            'name' => 'Lê Văn Tạo',
            'email' => 'admin@dxmb.vn',
            'phone' => '0123456789',
            'code' => 'DXMB001',
            'active' => true
        ]);

        $buildings = Building::all();
        foreach ($buildings as $building)
        {
            $building->update([
                'company_id' => 1,
                'manager_id' => 1
            ]);
        }
    }
}

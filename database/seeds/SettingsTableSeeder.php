<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate
        DB::table('settings')->truncate();
        $setting = [
            "ggs"    => "GGS",
            "gdn"    => "GDN",
            "seo"    => "SEO",
            "axd"    => "ADX",
            "mv"     => "MB",
            "ads"    => "Ads",
            "zalo"   => "Zalo",
            "coccoc" => "Coccoc",
            "other"  => "Other",
        ];

        Setting::create([
            'config_key'   => 'customer-source',
            'config_value' => $setting,
        ]);
    }
}

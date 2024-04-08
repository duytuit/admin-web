<?php

use Illuminate\Database\Seeder;
use App\Models\System\TemplateMail;

class TemplateDefaultMail extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TemplateMail::create([
           'bdc_building_id' => 1,
            'type' => 3,
            'name' => 'Mail mặc định',
            'subject' => 'Đây là mail mặc định',
            'content' => '<p>Hello abc @tenkhachhang</p>'
        ]);
    }
}

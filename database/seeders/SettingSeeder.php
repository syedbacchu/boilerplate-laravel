<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminSetting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdminSetting::firstOrCreate(['slug'=>'app_title'],['value'=>'Laravel Custom Authentication']);
        AdminSetting::firstOrCreate(['slug'=>'tag_title'],['value'=>'Tag title']);
        AdminSetting::firstOrCreate(['slug'=>'company_email'],['value'=>'tech@gmail.com']);
        AdminSetting::firstOrCreate(['slug'=>'company_address'],['value'=>'Company Address']);
        AdminSetting::firstOrCreate(['slug'=>'helpline'],['value'=>'04764 67 12 86']);

        AdminSetting::firstOrCreate(['slug' => 'logo'],['value' => '']);
        AdminSetting::firstOrCreate(['slug' => 'favicon'], ['value' => '']);
        AdminSetting::firstOrCreate(['slug' => 'copyright_text'], ['value' => 'Copyright@2024']);
        AdminSetting::firstOrCreate(['slug' => 'pagination_count'], ['value' => '10']);

        //General Settings
        AdminSetting::firstOrCreate(['slug' => 'currency'],[ 'value' => 'USD']);
        AdminSetting::firstOrCreate(['slug' => 'lang'], ['value' => 'en']);

        AdminSetting::firstOrCreate(['slug' => 'mail_driver'], ['value' => 'SMTP']);
        AdminSetting::firstOrCreate(['slug' => 'mail_host'], ['value' => 'sandbox.smtp.mailtrap.io']);
        AdminSetting::firstOrCreate(['slug' => 'mail_port'], ['value' => 2525]);
        AdminSetting::firstOrCreate(['slug' => 'mail_username'], ['value' => '6cfc6cf1fab4a0']);
        AdminSetting::firstOrCreate(['slug' => 'mail_password'], ['value' => '0d5f9161ef1ae0']);
        AdminSetting::firstOrCreate(['slug' => 'mail_encryption'], ['value' => 'null']);
        AdminSetting::firstOrCreate(['slug' => 'mail_from_address'], ['value' => 'noreply@info.com']);
    }
}

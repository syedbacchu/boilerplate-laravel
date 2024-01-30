<?php
namespace App\Http\Services;

use App\Models\AdminSetting;
use Illuminate\Support\Facades\DB;

class SettingService
{
    
    public function saveAdminSetting($request)
    {
        $response = responseData(false);
        DB::beginTransaction();
        try {
            foreach ($request->except('_token') as $key => $value) {

                if ($request->hasFile($key)) {
                    $image = uploadFileStorage($request->$key, IMAGE_SETTING_PATH, isset(allsetting()[$key]) ? allsetting()[$key] : '');
                    AdminSetting::updateOrCreate(['slug' => $key],['slug' => $key, 'value' => $image]);
                } else {
                    AdminSetting::updateOrCreate(['slug' => $key],['slug' => $key, 'value' => $value]);
                }
            }

            $response = responseData(true,__('Setting updated successfully'));
            DB::commit();
        } catch (\Exception $e) {
            storeException('saveAdminSetting ex --> ', $e->getMessage());
            DB::rollBack();
        }
        
        return $response;
    }
}

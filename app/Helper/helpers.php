<?php

use App\Models\AdminSetting;
use App\Http\Services\Logger;
use App\Models\Coin;
use App\Models\CurrencyList;
use App\Models\WithdrawHistory;
use App\Models\DepositeTransaction;
use App\Models\Wallet;
use App\Models\WalletAddressHistory;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;


function makeUniqueId()
{
    return uniqid().date('').time();
}


/**
 * @param int $a
 * @return string
 */
// random number
function randomNumber($a = 10)
{
    $x = '123456789';
    $c = strlen($x) - 1;
    $z = '';
    for ($i = 0; $i < $a; $i++) {
        $y = rand(0, $c);
        $z .= substr($x, $y, 1);
    }
    return $z;
}



/**
 * @param null $array
 * @return array|bool
 */
function allsetting($array = null)
{
    if (!isset($array[0])) {
        $allsettings = AdminSetting::get();
        if ($allsettings) {
            $output = [];
            foreach ($allsettings as $setting) {
                $output[$setting->slug] = $setting->value;
            }
            return $output;
        }
        return false;
    } elseif (is_array($array)) {
        $allsettings = AdminSetting::whereIn('slug', $array)->get();
        if ($allsettings) {
            $output = [];
            foreach ($allsettings as $setting) {
                $output[$setting->slug] = $setting->value;
            }
            return $output;
        }
        return false;
    } else {
        $allsettings = AdminSetting::where(['slug' => $array])->first();
        if ($allsettings) {
            $output = $allsettings->value;
            return $output;
        }
        return false;
    }
}

if (!function_exists('settings')) {

    function settings($keys = null)
    {
        if ($keys && is_array($keys)) {
            return AdminSetting::whereIn('slug', $keys)->pluck('value', 'slug')->toArray();
        } elseif ($keys && is_string($keys)) {
            $setting = AdminSetting::where('slug', $keys)->first();
            return empty($setting) ? false : $setting->value;
        }
        return AdminSetting::pluck('value', 'slug')->toArray();
    }
}


function storeException($type,$message)
{
    $logger = new Logger();
    $logger->log($type,$message);
}

function responseData($status,$message='',$data=[])
{
    $message = !empty($message) ? $message : __('Something went wrong');
    return ['success' => $status,'message' => $message, 'data' => $data];
}

function responseJsonData($status,$message='',$data=[])
{
    $message = !empty($message) ? $message : __('Something went wrong');
    return response()->json(['success' => $status,'message' => $message, 'data' => $data]);
}

function uploadImage($new_file, $path, $old_file_name = null, $width = null, $height = null)
{
    try{
        if (!file_exists(public_path($path))) {
            mkdir(public_path($path), 0777, true);
        }
        if (isset($old_file_name) && $old_file_name != "" && file_exists($path . $old_file_name)) {
            unlink($path . $old_file_name);

        }
        if ($new_file == '') return false;
        $input['imagename'] = uniqid() . time() . '.' . $new_file->getClientOriginalExtension();
        $imgPath = public_path($path . $input['imagename']);

        $makeImg = Image::make($new_file);
        if ($width != null && $height != null && is_int($width) && is_int($height)) {
            $makeImg->resize($width, $height);
            $makeImg->fit($width, $height);
        }
        // if(ResizeImage::make($new_file)
        //     ->resize($width, $height)
        //     ->save($path . $input['imagename'])) {
        //         return $input['imagename'];
        //     }



        if ($makeImg->save($imgPath)) {
            return $input['imagename'];
        }
        return false;
    }catch(\Exception $e){
        storeException('uploadFilep2p helper', $e->getMessage());
        return '';
    }

}


function imageSrcUser($path,$image="")
{
    $return = asset('assets/images/avatar.jpg');
    if (isset($image) && $image !== "" && file_exists(public_path($path . '/' . $image))) {
        $return = asset($path . '/' . $image);
    }
    return $return;
}

function uploadFileStorage($new_file, $path, $old_file_name = null, $width = null, $height = null)
{
    try{
        if ($new_file == '') return false;
        if (isset($old_file_name) && $old_file_name != "" ) {
            $fileExists = Storage::disk('local')->exists('/public/'.$path.$old_file_name);
            if($fileExists) {
                Storage::delete('/public/'.$path.$old_file_name);
            }
        }

        // Get filename with the extension
        $filenameWithExt = $new_file->getClientOriginalName();
        //Get just filename
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
        // Get just ext
        $extension = $new_file->getClientOriginalExtension();
        // Filename to store
        $fileNameToStore = uniqid().'_'.time().'.'.$extension;
        $new_file->storeAs($path,$fileNameToStore,'public');

        return $fileNameToStore;
    } catch(\Exception $e) {
        storeException('uploadFileStorage helper', $e->getMessage());
        return false;
    }

}

function showUserImage($path,$image)
{
    $return = asset('assets/images/avatar.jpg');
    if (isset($image) && $image !== "" ) {
        // $fileExists = Storage::disk('public')->exists($path.$image);
        // if($fileExists) {
            $return = asset($path . $image);
        // }
    }
    return $return;
}

function showImage($path,$image)
{
    $return = '';
    if (isset($image) && $image !== "" ) {
        // $fileExists = Storage::disk('local')->exists($path.$image);
        // if($fileExists) {
            $return = asset($path . $image);
    //     }
    }
    return $return;
}

function showDefaultImage($path,$image)
{
    $return = asset('assets/images/placeholder-image.png');
    if (isset($image) && $image !== "" ) {
        // $fileExists = Storage::disk('public')->exists($path.$image);
        // if($fileExists) {
            $return = asset($path . $image);
        // }
    }
    return $return;
}

function decryptId($encryptedId)
{
    try {
        $id = decrypt($encryptedId);
    } catch (Exception $e) {
        storeException('decryptId',$e->getMessage());
        return ['success' => false];
    }
    return $id;
}

function make_unique_slug($title, $table_name = NULL, $column_name = 'slug')
{
    $table = array(
        'Š' => 'S', 'š' => 's', 'Đ' => 'Dj', 'đ' => 'dj', 'Ž' => 'Z', 'ž' => 'z', 'Č' => 'C', 'č' => 'c', 'Ć' => 'C', 'ć' => 'c',
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
        'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
        'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss',
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e',
        'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o',
        'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b',
        'ÿ' => 'y', 'Ŕ' => 'R', 'ŕ' => 'r', '/' => '-', ' ' => '-'
    );

    // -- Remove duplicated spaces
    $stripped = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $title);

    // -- Returns the slug
    $slug = strtolower(strtr($title, $table));
    $slug = str_replace("?", "", $slug);
    $slug = str_replace("&", "-", $slug);
    $slug = str_replace("%", "-", $slug);
    $slug = str_replace("#", "-", $slug);
    $slug = str_replace("@", "-", $slug);
    $slug = str_replace('--', '-', $slug);
    if (isset($table_name)) {
        $item = DB::table($table_name)->where($column_name, $slug)->first();
        if (isset($item)) {
            $slug = setSlugAttribute($slug, $table_name, $column_name);
        }
    }

    return $slug;
}

function setSlugAttribute($value, $table, $column_name = 'slug')
{
    if (DB::table($table)->where($column_name, $value)->exists()) {
        return incrementSlug($value, $table, $column_name);
    }
    return $value;
}

function incrementSlug($slug, $table, $column_name = 'slug')
{
    $original = $slug;
    $count = 2;

    while (DB::table($table)->where($column_name, $slug)->exists()) {
        $slug = "{$original}-" . $count++;
    }

    return $slug;
}
    
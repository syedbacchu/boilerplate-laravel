<?php
namespace App\Http\Repository;

use App\Models\AdminSetting;
use Illuminate\Support\Facades\DB;


class SettingRepository
{
    public function saveBitgoSetting($request)
    {
        $response = ['success' => false, 'message' => __('Invalid request')];
        DB::beginTransaction();
        try {
            AdminSetting::updateOrCreate(['slug' => 'bitgo_api'], ['value' => $request->bitgo_api]);
            AdminSetting::updateOrCreate(['slug' => 'bitgoExpess'], ['value' => $request->bitgoExpess]);
            AdminSetting::updateOrCreate(['slug' => 'BITGO_ENV'], ['value' => $request->BITGO_ENV]);
            AdminSetting::updateOrCreate(['slug' => 'bitgo_token'], ['value' => $request->bitgo_token]);

            $response = [
                'success' => true,
                'message' => __('Bitgo setting updated successfully')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'success' => false,
                'message' => __('Something went wrong')
            ];
            return $response;
        }
        DB::commit();
        return $response;
    }

    public function saveApiServiceSetting($request)
    {
        $response = ['success' => false, 'message' => __('Invalid request')];
        DB::beginTransaction();
        try {
            AdminSetting::updateOrCreate(['slug' => 'CRYPTO_COMPARE_API_KEY'], ['value' => $request->CRYPTO_COMPARE_API_KEY]);
            AdminSetting::updateOrCreate(['slug' => 'CURRENCY_EXCHANGE_RATE_API_KEY'], ['value' => $request->CURRENCY_EXCHANGE_RATE_API_KEY]);

            $response = [
                'success' => true,
                'message' => __('Api Service updated successfully')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'success' => false,
                'message' => __('Something went wrong')
            ];
            return $response;
        }
        DB::commit();
        return $response;
    }

    public function savePaymentSetting($request)
    {
        $response = ['success' => false, 'message' => __('Invalid request')];
        DB::beginTransaction();
        try {

            if (isset($request->COMPARE_WEBSITE)) {
                AdminSetting::updateOrCreate(['slug' => 'COMPARE_WEBSITE'], ['value' => $request->COMPARE_WEBSITE]);
            }
            if (isset($request->COIN_PAYMENT_PUBLIC_KEY)) {
                AdminSetting::updateOrCreate(['slug' => 'COIN_PAYMENT_PUBLIC_KEY'], ['value' => $request->COIN_PAYMENT_PUBLIC_KEY]);
            }
            if (isset($request->COIN_PAYMENT_PRIVATE_KEY)) {
                AdminSetting::updateOrCreate(['slug' => 'COIN_PAYMENT_PRIVATE_KEY'], ['value' => $request->COIN_PAYMENT_PRIVATE_KEY]);
            }
            if (isset($request->COINPAYMENT_CURRENCY)) {
                AdminSetting::updateOrCreate(['slug' => 'COINPAYMENT_CURRENCY'], ['value' => $request->COINPAYMENT_CURRENCY]);
            }
            if (isset($request->base_coin_type)) {
                AdminSetting::updateOrCreate(['slug' => 'base_coin_type'], ['value' => $request->base_coin_type]);
            }
            if (isset($request->base_coin_type)) {
                AdminSetting::updateOrCreate(['slug' => 'base_coin_type'], ['value' => $request->base_coin_type]);
            }
            if (isset($request->ipn_merchant_id)) {
                AdminSetting::updateOrCreate(['slug' => 'ipn_merchant_id'], ['value' => $request->ipn_merchant_id]);
            }
            if (isset($request->ipn_secret)) {
                AdminSetting::updateOrCreate(['slug' => 'ipn_secret'], ['value' => $request->ipn_secret]);
            }
            AdminSetting::updateOrCreate(['slug' => 'coin_payment_withdrawal_email'], ['value' => $request->coin_payment_withdrawal_email]);

            $response = [
                'success' => true,
                'message' => __('Payment setting updated successfully')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'success' => false,
                'message' => __('Something went wrong')
            ];
            return $response;
        }
        DB::commit();
        return $response;
    }
}
<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\DbError;
use App\Models\Payment;
use App\Models\PaymentResponse;
use App\Models\UnverifiedPayments;
use App\Models\UserOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MpesaController extends Controller
{
    private function callBackBaseUrl()
    {
        return 'https://app.asapenergies.co.ke';
    }

    public function generateAccessToken()
    {
        $consumer_key = env("SAF_CONSUMER_KEY");
        $consumer_secret = env("SAF_CONSUMER_SECRET");
        $credentials = base64_encode($consumer_key . ":" . $consumer_secret);
        $url = "https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Basic " . $credentials));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);
        try {
            $access_token = json_decode($curl_response);
            if (isset($access_token->access_token)) {
                return $access_token->access_token;
            } else {
                return "Failed";
            }
        } catch (\Exception $exception) {
            echo "errorrr";
            return "Error";
        }
    }

    public function lipaNaMpesaPassword()
    {
        $lipa_time = Carbon::rawParse('now')->format('YmdHms');
        $passkey = "e8e1d85e29ab84fef2f88d1ccf76c2b2df844b6b7bc5a5d5bda86d97ec1ff37c";
        $BusinessShortCode = '7855029';
        $timestamp = $lipa_time;
        $lipa_na_mpesa_password = base64_encode($BusinessShortCode . $passkey . $timestamp);
//        echo $lipa_na_mpesa_password;
        return $lipa_na_mpesa_password;
    }

    public function customerMpesaSTKPush($identifier, $senderPhoneNumber, $amountToCharge)
    {

        $senderPhoneNumber = '254' . substr($senderPhoneNumber, 1);
        $url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $this->generateAccessToken()));
        $curl_post_data = [
            //Fill in the request parameters with valid values
            'BusinessShortCode' => '7855029',//5096743
            'Password' => $this->lipaNaMpesaPassword(),
            'Timestamp' => Carbon::rawParse('now')->format('YmdHms'),
            'TransactionType' => 'CustomerBuyGoodsOnline',
            'Amount' => $amountToCharge,
            'PartyA' => $senderPhoneNumber, // replace this with your phone number
            'PartyB' => '5096743',
            'PhoneNumber' => $senderPhoneNumber, // replace this with your phone number
            'CallBackURL' => $this->callBackBaseUrl() . "/api/confirmation/$identifier",
            'AccountReference' => "ASAP",
            'TransactionDesc' => "ASAP Payments"
        ];
        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        return curl_exec($curl);
    }

    public function mpesaConfirmation(Request $request, $identifier)
    {

        $data = $request->getContent();

        $paymentResponse = new PaymentResponse();
        $paymentResponse->identifier = $identifier;
        $paymentResponse->message = $data;
        try {
            $paymentResponse->save();
        } catch (\Exception $exception) {
        }

        if ($paymentResponse->callback_response_code != null) {
            echo "Transaction has already been processed";
            return;
        }

        $content = json_decode($data);
        $payment = Payment::where('identifier', $identifier)->first();
        if ($payment == null) {
            $unverifiedPayments = new UnverifiedPayments();
            $unverifiedPayments->identifier = $identifier;
            $unverifiedPayments->message = Str::substr($data, 0, 1000);
            try {
                $unverifiedPayments->save();
            } catch (\Exception $exception) {
            }
            echo "Transaction marked as suspicious";
            return;
        } else {
            $paymentResponse = new PaymentResponse();
            $paymentResponse->identifier = $identifier;
            $paymentResponse->message = Str::substr($data, 0, 600);
            try {
                $paymentResponse->save();
            } catch (\Exception $exception) {
            }
        }

        $responseCode = $content->Body->stkCallback->ResultCode;
        $merchant_id = $content->Body->stkCallback->MerchantRequestID;
        $checkout_id = $content->Body->stkCallback->CheckoutRequestID;
        $resultDesc = $content->Body->stkCallback->ResultDesc;

        //Save To Database
        $payment->callback_response_code = $responseCode;
        $payment->callback_merchant_request_id = $merchant_id;
        $payment->callback_checkout_request_id = $checkout_id;
        $payment->callback_result_desc = $resultDesc;
        $stk_status = '2';
        if ($responseCode == 0 || $responseCode == "0") {
            $stk_status = '1';
            $amount = $content->Body->stkCallback->CallbackMetadata->Item[0]->Value;
            $mpesa_receipt = $content->Body->stkCallback->CallbackMetadata->Item[1]->Value;
            $transaction_date = $content->Body->stkCallback->CallbackMetadata->Item[3]->Value;
            try {
                $callbackItems = $content->Body->stkCallback->CallbackMetadata->Item;
                if (sizeof($callbackItems) == 4){
                    $transaction_date = $content->Body->stkCallback->CallbackMetadata->Item[2]->Value;
                    $phone_number = $content->Body->stkCallback->CallbackMetadata->Item[3]->Value;
                }else{
                    $phone_number = $content->Body->stkCallback->CallbackMetadata->Item[4]->Value;
                }
            } catch (\Exception $exception) {
                $phone_number = '';
            }
            //Save To Database
            $payment->callback_phone = $phone_number;
            $payment->callback_amount = $amount;
            $payment->mpesa_receipt_number = $mpesa_receipt;
            $payment->mpesa_transaction_date = $transaction_date;
        }
        $payment->stk_status = $stk_status;
        try {
            $payment->save();
            $ordersGiven = json_decode($payment->order_id);
            foreach ($ordersGiven as $orderGiven){
                $userOrder = UserOrder::find($orderGiven);
                $userOrder->status = '4';
                $userOrder->save();
            }
        } catch (\Exception $exception) {
            $dbError = new DbError();
            $dbError->stage = "STK CallBAck Decoding";
            $dbError->exception = Str::substr($exception->getMessage(), 0, 99);
            $dbError->message = Str::substr($data, 0, 499);
            try {
                $dbError->save();
            } catch (\Exception $exception) {
            }
        }
    }

    public function test(){
//      $result =   $this->customerMpesaSTKPush('sssss', '0705537065', '1');
//      dd($result);
    }
}

<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\DbError;
use App\Models\Payment;
use App\Models\PaymentResponse;
use App\Models\UnverifiedPayments;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MpesaController extends Controller
{
    private function callBackBaseUrl()
    {
        return 'https://33346ea06901.ngrok.io';
    }

    public function generateAccessToken()
    {
        $consumer_key = env("SAF_CONSUMER_KEY");
        $consumer_secret = env("SAF_CONSUMER_SECRET");
        $credentials = base64_encode($consumer_key . ":" . $consumer_secret);
        $url = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
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
            return "Error";
        }
    }

    public function lipaNaMpesaPassword()
    {
        $lipa_time = Carbon::rawParse('now')->format('YmdHms');
        $passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
        $BusinessShortCode = 174379;
        $timestamp = $lipa_time;
        $lipa_na_mpesa_password = base64_encode($BusinessShortCode . $passkey . $timestamp);
        return $lipa_na_mpesa_password;
    }

    public function customerMpesaSTKPush($identifier, $senderPhoneNumber, $amountToCharge)
    {

        $senderPhoneNumber = '254' . substr($senderPhoneNumber, 1);

        $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $this->generateAccessToken()));
        $curl_post_data = [
            //Fill in the request parameters with valid values
            'BusinessShortCode' => 174379,
            'Password' => $this->lipaNaMpesaPassword(),
            'Timestamp' => Carbon::rawParse('now')->format('YmdHms'),
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => 1,
            'PartyA' => $senderPhoneNumber, // replace this with your phone number
            'PartyB' => 174379,
            'PhoneNumber' => $senderPhoneNumber, // replace this with your phone number
            'CallBackURL' => $this->callBackBaseUrl() . "/api/confirmation/$identifier",
            'AccountReference' => "H-lab tutorial",
            'TransactionDesc' => "Testing stk push on sandbox"
        ];//https://20a6ce4a4019.ngrok.io/api/confirmation/Gpu7kk20Ym6mZWQSDM09_2_4
//    dd($curl_post_data);
        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        return curl_exec($curl);
    }

    public function mpesaConfirmation(Request $request, $identifier)
    {
        $paymentResponse = new PaymentResponse();
        $paymentResponse->identifier = $identifier;
        $paymentResponse->message = 'Test jjjjjj bbbbbb';
        try {
            $paymentResponse->save();
        } catch (\Exception $exception) {
        }

        echo 'hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh';

        return;

        $data = $request->getContent();
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
            $phone_number = $content->Body->stkCallback->CallbackMetadata->Item[4]->Value;

            //Save To Database
            $payment->callback_phone = $phone_number;
            $payment->callback_amount = $amount;
            $payment->mpesa_receipt_number = $mpesa_receipt;
            $payment->mpesa_transaction_date = $transaction_date;
        }
        $payment->stk_status = $stk_status;
        try {
            $payment->save();
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
}

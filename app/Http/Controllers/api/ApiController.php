<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Gas;
use App\Models\GasCompany;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApiController extends Controller
{

    public $successStatus = 200;


    public function gas($refilling = 1): JsonResponse
    {
        if ($refilling == 1) {
            $gasses = Gas::where('availability', 'available')->where('classification', 'Refilling')->get();
        } else {
            $gasses = Gas::where('availability', 'available')->where('classification', '!=', 'Refilling')->get();
        }

        foreach ($gasses as $gas) {
            $gas->company_name = GasCompany::find($gas->company_id)->name;
        }

        return response()->json(
            [
                'success' => true,
                'gasses' => $gasses
            ], $this->successStatus);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['bail', 'required', 'email',],
            'password' => ['bail', 'required']
        ],
        );
        if ($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            return response()->json(
                [
                    'success' => true,
                    'message' => "Login successfull",
                    'user' => Auth::user()
                ], $this->successStatus);
        } else {
            return response()->json(
                [
                    'success' => false,
                    'message' => "Invalid Email address or Password",
                ], $this->successStatus);
        }
    }


    public function addAddress(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'address' => ['bail', 'required',],
            'house_number' => ['bail', 'required'],
            'apartment_estate' => ['bail', 'required'],
            'landmark' => ['bail', 'required'],
            'user_id' => ['bail', 'required'],
        ],
        );
        if ($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }
        $user = User::find($request->user_id);
        if ($user == null) {
            return response()->json(
                [
                    'success' => false,
                    'message' => "Your request was not verified",
                ], $this->successStatus);
        } else {

            $address = new UserAddress();
            $address->user_id = $request->user_id;
            $address->address = $request->address;
            $address->house_number = $request->house_number;
            $address->apartment_estate = $request->apartment_estate;
            $address->landmark = $request->landmark;
            $address->save();

            return response()->json(
                [
                    'success' => true,
                    'message' => "Address was added",
                ], $this->successStatus);
        }
    }


    public function postOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'total_price' => ['bail', 'required',],
            'count' => ['bail', 'required', 'numeric', 'max:10'],
            'address_id' => ['bail', 'required'],
            'gas_id' => ['bail', 'required'],
            'user_id' => ['bail', 'required'],
            'order_instructions' => ['bail', 'nullable', 'max:200'],
        ],
        );
        if ($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }
        $user = User::find($request->user_id);
        if ($user == null) {
            return response()->json(
                [
                    'success' => false,
                    'message' => "Your request was not verified",
                ], $this->successStatus);
        } else {
            $order = new UserOrder();
            $order->user_id = $request->user_id;
            $order->address_id = $request->address_id;
            $order->gas_id = $request->gas_id;
            $order->count = $request->count;
            $order->total_price = $request->total_price;
            $order->order_instructions = $request->order_instructions;
            try {
                $order->save();
                return response()->json(
                    [
                        'success' => true,
                        'message' => "Order has been placed successfully",
                    ], $this->successStatus);
            } catch (\Exception $exception) {

                return response()->json(
                    [
                        'success' => false,
                        'message' => "Something went wrong. Order was not placed.",
                    ], $this->successStatus);
            }
        }
    }


    public function fetchMyAddresses($userId)
    {
        $user = User::find($userId);
        if ($user == null) {
            return response()->json(
                [
                    'success' => false,
                    'message' => "Your request was not verified",
                ], $this->successStatus);
        } else {
            return response()->json(
                [
                    'success' => true,
                    'addresses' => UserAddress::where('user_id', $userId)->get(),
                ], $this->successStatus);
        }
    }

    public function fetchAllOrders($userId)
    {
        $user = User::find($userId);
        if ($user == null) {
            return response()->json(
                [
                    'success' => false,
                    'message' => "Your request was not verified",
                ], $this->successStatus);
        } else {
            $myOrders = UserOrder::where('user_id', $userId)->get();

            foreach ($myOrders as $myOrder) {
                $address = UserAddress::find($myOrder->address_id);
                $gas = Gas::find($myOrder->gas_id);
                $myOrder->address = $address->address;
                $myOrder->house_number = $address->house_number;
                $myOrder->apartment_estate = $address->apartment_estate;
                $myOrder->landmark = $address->landmark;
                $myOrder->classification = $gas->classification;
                $myOrder->weight = $gas->weight;
                $myOrder->initialPrice = $gas->initialPrice;
                $myOrder->price = $gas->price;
                $myOrder->company_name = GasCompany::find($gas->company_id)->name;
                $myOrder->created_at_parsed = $myOrder->created_at->timezone('Africa/Nairobi')->format('dS M Y \\a\\t g:i a');

                switch ($myOrder->status) {
                    case '0':
                        $status = 'Ongoing';
                        break;
                    case '1':
                        $status = 'Completed';
                        break;
                    case '2':
                        $status = 'Cancelled';
                        break;
                    case '3':
                        $status = 'Rejected';
                        break;
                    default:
                        $status = "Undefined";
                }
                $myOrder->status = $status;
            }
            return response()->json(
                [
                    'success' => true,
                    'orders' => $myOrders,
                ], $this->successStatus);
        }
    }

    public function fetchMyOngoingOrders($userId)
    {
        $user = User::find($userId);
        if ($user == null) {
            return response()->json(
                [
                    'success' => false,
                    'message' => "Your request was not verified",
                ], $this->successStatus);
        } else {
            $myOrders = UserOrder::where('user_id', $userId)->where('status', '0')->get();

            foreach ($myOrders as $myOrder) {
                $address = UserAddress::find($myOrder->address_id);
                $gas = Gas::find($myOrder->gas_id);
                $myOrder->address = $address->address;
                $myOrder->house_number = $address->house_number;
                $myOrder->apartment_estate = $address->apartment_estate;
                $myOrder->landmark = $address->landmark;
                $myOrder->classification = $gas->classification;
                $myOrder->weight = $gas->weight;
                $myOrder->initialPrice = $gas->initialPrice;
                $myOrder->price = $gas->price;
                $myOrder->company_name = GasCompany::find($gas->company_id)->name;
                $myOrder->created_at_parsed = $myOrder->created_at->timezone('Africa/Nairobi')->format('dS M Y \\a\\t g:i a');

                $myOrder->status = 'Ongoing';
            }
            return response()->json(
                [
                    'success' => true,
                    'orders' => $myOrders,
                ], $this->successStatus);
        }
    }

    public function updateDetails(Request $request, $user_id)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['bail', 'required', 'min:3'],
            'email' => ['bail', 'required', 'email', 'unique:users,email,' . $user_id],
            'phone' => ['bail', 'required', 'numeric', 'digits:10', 'unique:users,phone,' . $user_id],
        ],
            [
                'phone.unique' => 'Another user is already registered with the same phone. Please use a different phone number',
                'email.unique' => 'Another user is already registered with the same email. Please use a different email',
            ]
        );
        if ($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }
        $user = User::find($user_id);
        if ($user == null) {
            return response()->json(
                [
                    'success' => false,
                    'message' => "Your request was not verified",
                ], $this->successStatus);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();

        return response()->json(
            [
                'success' => true,
                'user' => $user,
                'message' => 'Profile updated successfully',
            ], $this->successStatus);
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'firstname' => ['bail', 'required', 'min:3'],
            'lastname' => ['bail', 'required', 'min:3'],
            'email' => ['bail', 'required', 'email', 'unique:users'],
            'phone' => ['bail', 'required', 'numeric', 'digits:10', 'unique:users'],
            'password' => ['bail', 'required', 'min:4']
        ],
            [
                'phone.unique' => 'Another user is already registered with the same phone. Please use a different phone number',
                'email.unique' => 'Another user is already registered with the same email. Please use a different email',
            ]
        );
        if ($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }

        $user = User::create([
            'name' => $request->firstname . ' ' . $request->lastname,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(
            [
                'success' => true,
                'user' => $user,
            ], $this->successStatus);
    }

    public function order(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_id' => ['bail', 'required'],
            'gas_id' => ['bail', 'required'],
            'order_notes' => ['bail', 'required', 'max:200'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }
        $order = new Order();
        $order->user_id = $request->user_id;
        $order->gas_id = $request->gas_id;
        $order->order_notes = $request->order_notes;

        try {
            $order->save();
            return response()->json(
                [
                    'success' => true,
                    'message' => "Order has been recorded successfully",
                ], $this->successStatus);
        } catch (\Exception $exception) {

            return response()->json(
                [
                    'success' => false,
                    'message' => "Something went wrong. Please try again later",
                ], $this->successStatus);
        }
    }

    public function payForOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['bail', 'required'],
            'gas_id' => ['bail', 'required'],
            'phone' => ['bail', 'required', 'numeric', 'digits:10'],
            'count' => ['bail', 'required', 'numeric', 'max:10'],
            'total_price' => ['bail', 'required',],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }
        $random = Str::random(20) . '_' . $request->user_id . '_' . $request->gas_id;
        $payment = new Payment();
        $payment->identifier = $random;
        $payment->user_id = $request->user_id;
        $payment->user_phone = $request->phone;
        $payment->gas_id = $request->gas_id;
        $payment->count = $request->count;
        $payment->amount = $request->total_price;
        $payment->save();

        $mpesaController = new MpesaController();
        $response = $mpesaController->customerMpesaSTKPush($random, $request->phone, $request->total_price);

        $decodedResponse = json_decode($response);
        $successFull = false;
        $errorMessage = "Some unknown error occurred while processing you transaction. Please try again";

        if (isset($decodedResponse->ResponseCode)) {
            $response_code = $decodedResponse->ResponseCode;
            if ($response_code == 0) {
                $successFull = true;
                $merchant_request_id = $decodedResponse->MerchantRequestID;
                $check_out_request_id = $decodedResponse->CheckoutRequestID;
                $payment->stk_response_code = $response_code;
                $payment->stk_merchant_request_id = $merchant_request_id;
                $payment->stk_checkout_request_id = $check_out_request_id;
            } else {
                $payment->stk_response_code = $response_code;
                $payment->stk_error_message = "$response";
            }
        } elseif (isset($decodedResponse->errorCode)) {
            try {
                $errorCode = $decodedResponse->errorCode;
                $errorMessage = $decodedResponse->errorMessage;
                $payment->stk_error_code = $errorCode;
                $payment->stk_error_message = $errorMessage;
            } catch (\Exception $exception) {
                $payment->stk_server_error = Str::substr($exception->getMessage(), 0, 49);
            }
        } else {
            $payment->stk_error_message = "$response";
        }
        $payment->save();

        if ($successFull) {
            return response()->json([
                'success' => true,
                'message' => "An STK push has been sent to your phone ($request->phone). Enter your PIN to complete transaction."
            ], $this->successStatus);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Something went wrong. Try again later. Error message: \"$errorMessage\""
            ], $this->successStatus);
        }


    }
}

<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CumulativeOrder;
use App\Models\Gas;
use App\Models\GasAccessory;
use App\Models\GasCompany;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserOrder;
use App\Models\UserOrderAccessory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
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
            $gasCompany = GasCompany::find($gas->company_id);
            $gas->company_name = $gasCompany->name;
            if ($gasCompany->image == null) {
                $gas->url = null;
            } else {
                $gas->url = asset("storage/".$gasCompany->image);
            }
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
            'phone' => ['bail', 'required', 'numeric', 'digits:10',],
            'password' => ['bail', 'required']
        ],
        );
        if ($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }
        $credentials = $request->only('phone', 'password');
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
                    'message' => "Invalid Phone Number or Password",
                ], $this->successStatus);
        }
    }


    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['bail', 'required', 'email',],
        ],
        );
        if ($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }
        $user = User::where('email', $request->email)->first();

        if ($user == null) {
            return response()->json(
                [
                    'success' => false,
                    'message' => "No user is registered with the the provided email",
                ], $this->successStatus);
        } else {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                $message = "An email with password reset instruction has been sent to $request->email";
            } else {
                $message = "Something went wrong. Reset email not sent";
            }
            return response()->json(
                [
                    'success' => true,
                    'message' => $message,
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
            'gasItems' => ['bail', 'required',],
            'accessoryItems' => ['bail', 'required',],
            'address_id' => ['bail', 'required'],
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

            $gasItemsString = preg_replace('/\\\\\"/', "\"", $request->gasItems);
            $accessoryItemsString = preg_replace('/\\\\\"/', "\"", $request->accessoryItems);

            $accessoryItems = json_decode($accessoryItemsString);
            $gasItems = json_decode($gasItemsString);

            $newOrderIdsGasItems = [];
            $newOrderIdsAccessoryItems = [];


            foreach ($accessoryItems as $accessoryItem) {
                $orderAccessory = new UserOrderAccessory();
                $orderAccessory->user_id = $request->user_id;
                $orderAccessory->accessory_id = $accessoryItem->id;
                $orderAccessory->count = $accessoryItem->count;
                $orderAccessory->total_price = (int)$accessoryItem->count * $accessoryItem->price;

                $orderAccessory->save();
                $orderAccessory->refresh();
                $newOrderIdsAccessoryItems[] = $orderAccessory->id;
            }

            foreach ($gasItems as $gasItem) {
                $order = new UserOrder();
                $order->user_id = $request->user_id;
                $order->gas_id = $gasItem->id;
                $order->count = $gasItem->count;
                $order->total_price = (int)$gasItem->count * $gasItem->price;

                $order->save();
                $order->refresh();
                $newOrderIdsGasItems[] = $order->id;
            }


            $cumulativeOrder = new CumulativeOrder();
            $cumulativeOrder->user_orders_gases = json_encode($newOrderIdsGasItems);
            $cumulativeOrder->user_orders_accessory = json_encode($newOrderIdsAccessoryItems);
            $cumulativeOrder->user_id = $request->user_id;
            $cumulativeOrder->address_id = $request->address_id;
            $cumulativeOrder->order_instructions = $request->order_instructions;
            $cumulativeOrder->save();
            $cumulativeOrder->refresh();


            try {
                return response()->json(
                    [
                        'success' => true,
                        'cumulative_id' => $cumulativeOrder->id,
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

            $myCumulativeOrders = CumulativeOrder::where('user_id', $userId)->orderBy('id', 'desc')->get();

            foreach ($myCumulativeOrders as $cumulativeOrder) {

                $address = UserAddress::find($cumulativeOrder->address_id);

                $cumulativeOrder->address = $address->address;
                $cumulativeOrder->house_number = $address->house_number;
                $cumulativeOrder->apartment_estate = $address->apartment_estate;
                $cumulativeOrder->landmark = $address->landmark;

                $cumulativeOrder->created_at_parsed = $cumulativeOrder->created_at->timezone('Africa/Nairobi')->format('dS M Y \\a\\t g:i a');

                switch ($cumulativeOrder->status) {
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
                    case '4':
                        $status = 'Paid';
                        break;
                    default:
                        $status = "Undefined";
                }
                $cumulativeOrder->status = $status;

                $gasItems = json_decode($cumulativeOrder->user_orders_gases);
                $gasItemsOrders = UserOrder::whereIn('id', $gasItems)->get();
                foreach ($gasItemsOrders as $myOrder) {
                    $gas = Gas::find($myOrder->gas_id);
                    $myOrder->classification = $gas->classification;
                    $myOrder->weight = $gas->weight;
                    $myOrder->initialPrice = $gas->initialPrice;
                    $myOrder->price = $gas->price;
                    $myOrder->company_name = GasCompany::find($gas->company_id)->name;
                }

                $accessoryItems = json_decode($cumulativeOrder->user_orders_accessory);
                $accessoryItemsOrders = UserOrderAccessory::whereIn('id', $accessoryItems)->get();
                foreach ($accessoryItemsOrders as $accessoryOrder) {
                    $accessory = GasAccessory::find($accessoryOrder->accessory_id);
                    $accessoryOrder->accessory = $accessory;
                }

                $cumulativeOrder->gasItemsOrders = $gasItemsOrders;
                $cumulativeOrder->accessoryItemsOrders = $accessoryItemsOrders;
            }
            return response()->json(
                [
                    'success' => true,
                    'orders' => $myCumulativeOrders,
                ], $this->successStatus);
        }
    }

    public function fetchAllPayments($userId)
    {
        $user = User::find($userId);
        if ($user == null) {
            return response()->json(
                [
                    'success' => false,
                    'message' => "Your request was not verified",
                ], $this->successStatus);
        } else {
            $payments = Payment::where('callback_response_code', '0')->orderBy('created_at', 'desc')->limit(30)->get();

            foreach ($payments as $payment) {
                $payment->created_at_parsed = $payment->created_at->timezone('Africa/Nairobi')->format('dS M Y \\a\\t g:i a');
            }
            return response()->json(
                [
                    'success' => true,
                    'payments' => $payments,
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

            $myCumulativeOrders = CumulativeOrder::where('user_id', $userId)->where(function ($query) {
                $query->where('status', '0')->orWhere('status', '4');
            })->orderBy('id', 'desc')->get();


            foreach ($myCumulativeOrders as $cumulativeOrder) {

                $address = UserAddress::find($cumulativeOrder->address_id);

                $cumulativeOrder->address = $address->address;
                $cumulativeOrder->house_number = $address->house_number;
                $cumulativeOrder->apartment_estate = $address->apartment_estate;
                $cumulativeOrder->landmark = $address->landmark;

                $cumulativeOrder->created_at_parsed = $cumulativeOrder->created_at->timezone('Africa/Nairobi')->format('dS M Y \\a\\t g:i a');

                switch ($cumulativeOrder->status) {
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
                    case '4':
                        $status = 'Paid';
                        break;
                    default:
                        $status = "Undefined";
                }
                $cumulativeOrder->status = $status;

                $gasItems = json_decode($cumulativeOrder->user_orders_gases);
                $gasItemsOrders = UserOrder::whereIn('id', $gasItems)->get();
                foreach ($gasItemsOrders as $myOrder) {
                    $gas = Gas::find($myOrder->gas_id);
                    $myOrder->classification = $gas->classification;
                    $myOrder->weight = $gas->weight;
                    $myOrder->initialPrice = $gas->initialPrice;
                    $myOrder->price = $gas->price;
                    $myOrder->company_name = GasCompany::find($gas->company_id)->name;
                }

                $accessoryItems = json_decode($cumulativeOrder->user_orders_accessory);
                $accessoryItemsOrders = UserOrderAccessory::whereIn('id', $accessoryItems)->get();
                foreach ($accessoryItemsOrders as $accessoryOrder) {
                    $accessory = GasAccessory::find($accessoryOrder->accessory_id);
                    $accessoryOrder->accessory = $accessory;
                }

                $cumulativeOrder->gasItemsOrders = $gasItemsOrders;
                $cumulativeOrder->accessoryItemsOrders = $accessoryItemsOrders;
            }
            return response()->json(
                [
                    'success' => true,
                    'orders' => $myCumulativeOrders,
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
            $order->refresh();
            return response()->json(
                [
                    'success' => true,
                    'order_id' => $order->id,
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
            'cumulative_id' => ['bail', 'required'],
            'phone' => ['bail', 'required', 'numeric', 'digits:10'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }

        $cumulativeOrder = CumulativeOrder::find($request->cumulative_id);

        if ($cumulativeOrder == null) {
            return response()->json([
                'success' => false,
                'message' => "Request cannot be verified"
            ], $this->successStatus);
        }


        $cumulativeTotalPrice = 0;
        $ordersGiven = json_decode($cumulativeOrder->user_orders_gases);
        foreach ($ordersGiven as $orderGiven) {
            $userOrder = UserOrder::find($orderGiven);
            $price = $userOrder->total_price;
            $cumulativeTotalPrice += $price;
        }

        $ordersAccessoryGiven = json_decode($cumulativeOrder->user_orders_accessory);
        foreach ($ordersAccessoryGiven as $orderAccessoryGiven) {
            $userOrderAccessory = UserOrderAccessory::find($orderAccessoryGiven);
            $price = $userOrderAccessory->total_price;
            $cumulativeTotalPrice += $price;
        }


        $random = Str::random(10) . time();
        $payment = new Payment();
        $payment->identifier = $random;
        $payment->user_id = $request->user_id;
        $payment->user_phone = $request->phone;
        $payment->cumulative_id = $request->cumulative_id;
        $payment->amount = $cumulativeTotalPrice;
        $payment->save();

        $mpesaController = new MpesaController();
        $response = $mpesaController->customerMpesaSTKPush($random, $request->phone, $cumulativeTotalPrice);

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

    public function getAccessories()
    {
        $accessories = GasAccessory::get();
        foreach ($accessories as $accessory) {
            if ($accessory->image == null) {
                $accessory->url = 'https://bitsofco.de/content/images/2018/12/broken-1.png';
            } else {
                $accessory->url = asset("storage/" . $accessory->image);
            }
        }
        return response()->json([
            'success' => true,
            'accessories' => $accessories
        ], $this->successStatus);
    }
}

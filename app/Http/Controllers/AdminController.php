<?php

namespace App\Http\Controllers;

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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{


    /**
     * AdminController constructor.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    private function gasClassifications(): array
    {
        return array(
            'Complete Set',
            'Refilling',
            'Gas Cylinder + Gas only',
            'Gas Cylinder only',
        );
    }

    private function gasAvailability(): array
    {
        return array(
            'Available',
            'Unavailable',
        );
    }

    public function fetchOrders($limit, $status = null)
    {
        if ($limit) {
            $latestOrders = CumulativeOrder::orderBy('created_at', 'desc')->paginate(20);
        } else {
            if ($status == null) {
                $latestOrders = CumulativeOrder::orderBy('created_at', 'desc')->paginate(10);
            } else {
                $latestOrders = CumulativeOrder::orderBy('created_at', 'desc')->where('status', $status)->paginate(10);
            }
        }
        foreach ($latestOrders as $latestOrder) {
            $this->loopCumulativeOrder($latestOrder);
        }

        return $latestOrders;
    }

    public function index()
    {
        $ongoingOrders = CumulativeOrder::where('status', '0')->count();
        $completeOrders = CumulativeOrder::where('status', '1')->count();
        $cancelledOrders = CumulativeOrder::where('status', '2')->count();
        $usersCount = User::where('level', '0')->count();
        $latestUsers = User::orderBy('created_at', 'desc')->limit(10)->get();
        $latestOrders = $this->fetchOrders(true);

        foreach ($latestOrders as $latestOrder){
            $latestOrder->user = User::find($latestOrder->user_id);
        }

        return view('dashboard', compact('ongoingOrders', 'completeOrders', 'cancelledOrders', 'usersCount', 'latestUsers', 'latestOrders'));
    }

    public function loopCumulativeOrder($cumulativeOrder){
        $address = UserAddress::find($cumulativeOrder->address_id);

        $cumulativeOrder->address = $address->address;
        $cumulativeOrder->house_number = $address->house_number;
        $cumulativeOrder->apartment_estate = $address->apartment_estate;
        $cumulativeOrder->landmark = $address->landmark;

        $cumulativeOrder->created_at_parsed = $cumulativeOrder->created_at->timezone('Africa/Nairobi')->format('dS M Y \\a\\t g:i a');

        switch ($cumulativeOrder->status) {
            case '0':
                $status = 'Unpaid';
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
            $gas = Gas::withTrashed()->find($myOrder->gas_id);
            $myOrder->classification = $gas->classification;
            $myOrder->weight = $gas->weight;
            $myOrder->initialPrice = $gas->initialPrice;
            $myOrder->price = $gas->price;
            $myOrder->company_name = GasCompany::find($gas->company_id)->name;
        }

        $accessoryItems = json_decode($cumulativeOrder->user_orders_accessory);
        $accessoryItemsOrders = UserOrderAccessory::whereIn('id', $accessoryItems)->get();
        foreach ($accessoryItemsOrders as $accessoryOrder) {
            $accessory = GasAccessory::withTrashed()->find($accessoryOrder->accessory_id);
            $accessoryOrder->accessory = $accessory;
        }

        $cumulativeOrder->gasItemsOrders = $gasItemsOrders;
        $cumulativeOrder->accessoryItemsOrders = $accessoryItemsOrders;
    }
    public function viewCompanies()
    {
        $companies = GasCompany::orderBy('name', 'asc')->get();

        foreach ($companies as $company){
            if ($company->image == null){
                $company->url = "https://cdn.iconscout.com/icon/free/png-512/data-not-found-1965034-1662569.png";
            }else{
                $company->url = asset("storage/".$company->image);
            }
        }
        return view('companies', compact('companies'));
    }

    public function viewOrders($tag = null)
    {

        if ($tag == null) {
            $latestOrders = $this->fetchOrders(true);
        } else {
            switch ($tag) {
                case 'ongoing':
                    $status = '0';
                    break;
                case 'completed':
                    $status = '1';
                    break;
                case 'cancelled':
                    $status = '2';
                    break;
                default:
                    $status = '3';
            }
            $latestOrders = $this->fetchOrders(false, $status);
        }


        foreach ($latestOrders as $latestOrder){
            $latestOrder->user = User::find($latestOrder->user_id);
        }

        return view('orders', compact('latestOrders'));
    }

    public function viewUsers()
    {
        $users = User::where('level', '0')->limit('100')->get();
        foreach ($users as $user) {
            $user->orders_count = UserOrder::where('user_id', $user->id)->count();
        }
        return view('users', compact('users'));
    }

    public function viewGas()
    {
        $classifications = $this->gasClassifications();
        $availability = $this->gasAvailability();
        $companies = GasCompany::orderBy('name', 'asc')->get();
        $gasses = Gas::paginate(10);
        foreach ($gasses as $gas) {
            $gas->companyName = GasCompany::find($gas->company_id)->name;
        }
        return view('gas', compact('companies', 'classifications', 'gasses', 'availability'));
    }

    public function viewAccessories()
    {
        $availability = $this->gasAvailability();
        $accessories = GasAccessory::paginate(10);
        foreach ($accessories as $accessory){
            if ($accessory->image == null){
                $accessory->url = "https://cdn.iconscout.com/icon/free/png-512/data-not-found-1965034-1662569.png";
            }else{
                $accessory->url = asset("storage/".$accessory->image);
            }
        }

        return view('accessories', compact('accessories', 'availability'));
    }

    public function addCompany(Request $request)
    {
        Validator::make($request->all(), [
            'name' => ['required', 'min:3', 'max:20', 'unique:gas_companies'],
        ], [
            'name.unique' => 'Another company with a similar name already exist.'
        ])->validateWithBag('gas');
        $company = new GasCompany();
        if ($request->file('image') != null) {
            $path = $request->file('image')->store('company_images', ['disk' => 'public']);
            $company->image = $path;
        }
        $company->name = $request->name;
        $company->save();
        return Redirect::back()->with('success', 'Company has been added successfully');
    }

    public function addAccessory(Request $request)
    {

        $gasAccessory = new GasAccessory();
        if ($request->file('image') != null) {
            $path = $request->file('image')->store('accessory_images', ['disk' => 'public']);
            $gasAccessory->image = $path;
        }
        $gasAccessory->title = $request->title;
        $gasAccessory->description = $request->description;
        $gasAccessory->initialPrice = $request->initialPrice;
        $gasAccessory->price = $request->price;
        $gasAccessory->save();
        return Redirect::back()->with('success', 'Gas Accessory has been added successfully');
    }

    public function completeOrder(Request $request)
    {
        $order = UserOrder::find($request->order_id);
        if ($order == null){
            return Redirect::back()->with('error', 'Something went wrong. Try again');
        }else{
            $order->status = '1';
            $order->save();
            return Redirect::back()->with('success', 'Order has been marked as complete');
        }
    }

    public function cancelOrder(Request $request)
    {
        $order = UserOrder::find($request->order_id);
        if ($order == null){
            return Redirect::back()->with('error', 'Something went wrong. Try again');
        }else{
            $order->status = '2';
            $order->save();
            return Redirect::back()->with('success', 'Order has been marked as cancelled');
        }
    }

    public function addGas(Request $request)
    {
        Validator::make($request->all(), [
            'company_id' => ['required'],
            'classification' => ['required'],
            'weight' => ['required', 'numeric'],
            'initialPrice' => ['nullable', 'numeric'],
            'price' => ['required', 'numeric'],
            'availability' => ['required'],
        ])->validateWithBag('add_gas');

        $gas = new Gas();
        $gas->company_id = $request->company_id;
        $gas->classification = $request->classification;
        $gas->weight = $request->weight;
        $gas->initialPrice = $request->initialPrice;
        $gas->price = $request->price;
        $gas->availability = $request->availability;
        $gas->save();
        return Redirect::back()->with('success', 'Gas has been added successfully');
    }

    public function editCompany(Request $request)
    {

        $company = GasCompany::find($request->id);
        if ($company == null) {
            return Redirect::back()->with('error', 'Something went wrong. Try again');
        }
        Validator::make($request->all(), [
            'name' => ['required', 'min:3', 'max:20', 'unique:gas_companies,name,' . $company->id],
        ], [
            'name.unique' => 'Another company with a similar name already exist.'
        ])->validateWithBag('gas_edit');

        if ($request->file('image') != null) {
            $path = $request->file('image')->store('company_images', ['disk' => 'public']);
            $company->image = $path;
        }

        $company->name = $request->name;
        $company->save();
        return Redirect::back()->with('success', 'Company has been updated successfully');
    }

    public function editAccessory(Request $request)
    {

        $gasAccessory = GasAccessory::find($request->id);
        if ($gasAccessory == null) {
            return Redirect::back()->with('error', 'Something went wrong. Try again');
        }
        if ($request->file('image') != null) {
            $path = $request->file('image')->store('company_images', ['disk' => 'public']);
            $gasAccessory->image = $path;
        }

        $gasAccessory->title = $request->title;
        $gasAccessory->description = $request->description;
        $gasAccessory->initialPrice = $request->initialPrice;
        $gasAccessory->price = $request->price;
        $gasAccessory->save();

        return Redirect::back()->with('success', 'Accessory has been updated successfully');
    }

    public function deleteAccessory(Request $request)
    {
        $gasAccessory = GasAccessory::find($request->id);
        $gasAccessory->delete();
        return Redirect::back()->with('success', 'Accessory has been deleted successfully');
    }

    public function deleteGas(Request $request)
    {
        $gas = Gas::find($request->id);
        $gas->delete();
        return Redirect::back()->with('success', 'Gas has been deleted successfully');
    }

    public function editGas(Request $request)
    {
        $gas = Gas::find($request->gas_id);
        if ($gas == null) {
            return Redirect::back()->with('error', 'Something went wrong. Try again');
        }
        Validator::make($request->all(), [
            'company_id' => ['required'],
            'classification' => ['required'],
            'weight' => ['required', 'numeric'],
            'initialPrice' => ['nullable', 'numeric'],
            'price' => ['required', 'numeric'],
            'availability' => ['required'],
        ])->validateWithBag('edit_gas');

        $gas->company_id = $request->company_id;
        $gas->classification = $request->classification;
        $gas->weight = $request->weight;
        $gas->initialPrice = $request->initialPrice;
        $gas->price = $request->price;
        $gas->availability = $request->availability;
        $gas->save();

        return Redirect::back()->with('success', 'Gas has been updated successfully');
    }

    public function changePassword(Request $request){

      $validator =  Validator::make($request->all(), [
            'old' =>  [
                'required', function ($attribute, $value, $fail) {
                    if (!Hash::check($value, Auth::user()->password)) {
                        $fail('Old Password didn\'t match');
                    }
                },
            ],
            'new' => ['required', 'confirmed'],
        ]);


        if ($validator->fails()) {
            return Redirect::back()->with('error', $validator->errors());
        }

        $user = User::find(Auth::id());
        $user->password = Hash::make($request->new);
        $user->save();
        return Redirect::back()->with('success', 'Password has been updated');
    }

    public function payments(){
        $payments = Payment::where('callback_response_code','0')->orderBy('created_at','desc')->paginate(10);
        return view('payments', compact('payments'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Gas;
use App\Models\GasCompany;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{


    /**
     * AdminController constructor.
     */
    public function __construct()
    {
        $this->middleware(['auth','verified']);
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


    public function fetchOrders($limit, $status= null){
        if ($limit){
            $latestOrders = UserOrder::orderBy('created_at','desc')->limit(5)->get();
        }else{
            if ($status == null){
                $latestOrders = UserOrder::orderBy('created_at','desc')->limit(50)->get();
            }else{
                $latestOrders = UserOrder::orderBy('created_at','desc')->where('status', $status)->limit(50)->get();
            }
        }
        foreach ($latestOrders as $latestOrder) {
            $address = UserAddress::find($latestOrder->address_id);
            $gas = Gas::find($latestOrder->gas_id);
            $latestOrder->user = User::find($latestOrder->user_id);
            $latestOrder->address = $address->address;
            $latestOrder->house_number = $address->house_number;
            $latestOrder->apartment_estate = $address->apartment_estate;
            $latestOrder->landmark = $address->landmark;
            $latestOrder->classification = $gas->classification;
            $latestOrder->weight = $gas->weight;
            $latestOrder->initialPrice = $gas->initialPrice;
            $latestOrder->price = $gas->price;
            $latestOrder->date = $gas->created_at->timezone('Africa/Nairobi')->format('d/m/Y g:i a');
            $latestOrder->company_name = GasCompany::find($gas->company_id)->name;
            switch($latestOrder->status){
                case '0':
                    $stage = 'New';
                    break;

                case '1':
                    $stage = 'Complete';
                    break;

                case '2':
                    $stage = 'Cancelled';
                    break;
                default:
                    $stage = 'Undefined';
            }
            $latestOrder->stage = $stage;
        }

        return $latestOrders;
    }

    public function index(){
        $ongoingOrders = UserOrder::where('status','0')->count();
        $completeOrders = UserOrder::where('status','1')->count();
        $cancelledOrders = UserOrder::where('status','2')->count();
        $usersCount = User::where('level','0')->count();
        $latestUsers = User::orderBy('created_at','desc')->limit(5)->get();
        $latestOrders = $this->fetchOrders(true);


        return view('dashboard', compact('ongoingOrders', 'completeOrders', 'cancelledOrders', 'usersCount','latestUsers', 'latestOrders'));
    }
    public function viewCompanies(){
        $companies = GasCompany::orderBy('name','asc')->get();
        return view('companies', compact('companies'));
    }
    public function viewOrders($tag = null){

        if ($tag == null){
            $latestOrders = $this->fetchOrders(true);
        }else{
            switch ($tag){
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
        return view('orders', compact('latestOrders'));
    }
    public function viewUsers(){
        $users = User::where('level','0')->limit('100')->get();
        foreach ($users as $user){
            $user->orders_count = UserOrder::where('user_id', $user->id)->count();
        }
        return view('users', compact('users'));
    }
    public function viewGas(){
        $classifications = $this->gasClassifications();
        $availability = $this->gasAvailability();
        $companies = GasCompany::orderBy('name','asc')->get();
        $gasses = Gas::paginate(10);
        foreach ($gasses as $gas){
            $gas->companyName = GasCompany::find($gas->company_id)->name;
        }
        return view('gas', compact('companies', 'classifications', 'gasses', 'availability'));
    }


    public function addCompany(Request $request){

        Validator::make($request->all(),[
            'name' => ['required', 'min:3','max:20','unique:gas_companies'],
        ],[
            'name.unique' => 'Another company with a similar name already exist.'
        ])->validateWithBag('gas');

        $company = new GasCompany();
        $company->name = $request->name;
        $company->save();
        return Redirect::back()->with('success', 'Company has been added successfully');
    }
    public function addGas(Request $request){
        Validator::make($request->all(),[
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

    public function editCompany(Request $request){

        $company =  GasCompany::find($request->id);
        if ($company == null){
            return Redirect::back()->with('error', 'Something went wrong. Try again');
        }
        Validator::make($request->all(),[
            'name' => ['required', 'min:3','max:20','unique:gas_companies,name,'.$company->id],
        ],[
            'name.unique' => 'Another company with a similar name already exist.'
        ])->validateWithBag('gas_edit');

        $company->name = $request->name;
        $company->save();
        return Redirect::back()->with('success', 'Company has been updated successfully');
    }

    public function editGas(Request $request){
        $gas =  Gas::find($request->gas_id);
        if ($gas == null){
            return Redirect::back()->with('error', 'Something went wrong. Try again');
        }
        Validator::make($request->all(),[
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
}

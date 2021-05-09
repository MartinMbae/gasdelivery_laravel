<?php

namespace App\Http\Controllers;

use App\Models\Gas;
use App\Models\GasCompany;
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

    public function index(){
        return view('dashboard');
    }
    public function viewCompanies(){
        $companies = GasCompany::orderBy('name','asc')->get();
        return view('companies', compact('companies'));
    }
    public function viewOrders(){
        return view('orders');
    }
    public function viewUsers(){
        return view('users');
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

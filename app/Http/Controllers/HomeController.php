<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function test(){
        echo "Martin";
        $response = '{cart: {0: {"id":2,"company_id":3,"classification":"Complete Set","weight":"6","initialPrice":null,"price":"789","availability":"Available","company_name":"Sea Gas","url":"https://app.asapenergies.co.ke/public/storage_images/company_images/4o7Ru4DBwxGFxEmKD8jwJWVXjW1gNoZ0K9ZbpApY.jpg"}, 1: {"id":1,"company_id":2,"classification":"Gas Cylinder + Gas only","weight":"4","initialPrice":"1200","price":"1000","availability":"Available","company_name":"K-Gas","url":"https://app.asapenergies.co.ke/public/storage_images/company_images/Lx82eRSu0KPBM4gkuSs8IdU1DPOPJVT3VXobAWDj.jpg"}}}';

        dd($response);

        $cartItems = json_decode($response);

        echo sizeof($cartItems);

    }
}

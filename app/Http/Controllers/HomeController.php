<?php

namespace App\Http\Controllers;

use App\Price;
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
        $prices = Price::latest()->paginate(7);
        return view('home',compact('prices'))
            ->with('i', (request()->input('page', 1) - 1) * 7);
    }
}

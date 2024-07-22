<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:PPIC']);
       
    }
    public function index(){
        return view('dashboard.index');
    }
}

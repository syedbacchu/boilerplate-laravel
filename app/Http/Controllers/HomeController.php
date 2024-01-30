<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    //main page
    public function index(){
        $data = [];
        // $data['title'] = settings('app_title');
        // $data['settings'] = settings();
        
        return view('index', $data);
    }

   

   
}

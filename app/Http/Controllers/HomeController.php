<?php

namespace App\Http\Controllers;

use App\Models\Patron;

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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customer_id = auth()->user()->id;
        //我的资讯
        $my_patrons = Patron::where('customer_id', $customer_id)->get()->map(function ($model) {
            $need_arr = [0 => 'APP', 1 => '小程序', 2 => '网站', 3 => '系统软件', 4 => '其它'];
            $model['need'] = $need_arr[$model['need']];
            return $model;
        });
        //公共资讯
        $our_patrons = Patron::where('customer_id', 0)->get()->map(function ($model) {
            $need_arr = [0 => 'APP', 1 => '小程序', 2 => '网站', 3 => '系统软件', 4 => '其它'];
            $model['need'] = $need_arr[$model['need']];
            return $model;
        });

        return view('home', compact('my_patrons', 'our_patrons'));
    }

}

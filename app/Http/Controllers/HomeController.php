<?php

namespace App\Http\Controllers;

use App\Models\Patron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    public function password()
    {
        return view('password');
    }

    public function retrieve(Request $request)
    {
        $customer=auth()->user();
        $messages = [
            'password.min' => '密码最少为6位!',
        ];
        $rules = [
            'password' => 'confirmed|max:255|min:6',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return back()->with('notice', $error);
        }

        if ($request->has('password') && $request->password != '') {
            if (!\Hash::check($request->old_password, $customer->password)) {
                return back()->with('notice', "原始密码错误");
            }
            $customer->password = bcrypt($request->password);
        }
        $customer->update([
            'password' => bcrypt($request->password),
        ]);

        return back()->with('success', "修改密码成功~");
    }

}

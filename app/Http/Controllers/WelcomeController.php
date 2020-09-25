<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Notice;
use Illuminate\Http\Request;
use App\Handlers\WechatConfigHandler;
use Illuminate\Support\Facades\Auth;
class WelcomeController extends Controller
{
    private $wechat;

    public function __construct(WechatConfigHandler $wechat)
    {
        if (is_wei_xin()) {
            $this->middleware('wechat.oauth');
        }
        $this->wechat = $wechat;
    }


    public function index(Request $request)
    {
        $notices = Notice::wherein('department_id', [0, 12])->orderby('sort_order')->get();

        $app = $this->wechat->app();
        $wechat = session('wechat.oauth_user.default'); //拿到授权用户资料
        if ($wechat){
            $customer = Customer::where('openid', $wechat['openid'])->first();
            if ($customer) {
                Auth::loginUsingId($customer->id);
            }
        }
        return view('welcome', compact('notices'));
//    return redirect('/home');
    }


}

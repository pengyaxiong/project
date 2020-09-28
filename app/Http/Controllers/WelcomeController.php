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

        if (is_wei_xin()) {
            $app = $this->wechat->app();
            $user = session('wechat.oauth_user.default'); //拿到授权用户资料
            $original = $user->original;
            $openid = $original['openid'];
            if ($openid != null) {
                $customer = Customer::where('openid', $openid)->first();
                if ($customer) {
                    Auth::loginUsingId($customer->id);
                }
            }
        }
        return view('welcome', compact('notices'));
//    return redirect('/home');
    }


}

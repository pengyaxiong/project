<?php

namespace App\Http\Controllers;

use App\Handlers\WechatConfigHandler;
use App\Models\Customer;
use App\Models\Patron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    private $wechat;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(WechatConfigHandler $wechat)
    {
        $this->wechat = $wechat;
        $this->middleware('auth');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function index()
    {
        $customer_id = auth()->user()->id;

        $user = session('wechat.oauth_user.default'); //拿到授权用户资料
        $original = $user->original;
        $openid = $original['openid'];
        if ($openid!=null){
            Customer::where('id',$customer_id)->update([
                'openid' => $original['openid'],
                'headimgurl' => $original['headimgurl'],
                'nickname' => $original['nickname'],
//                'tel' => $wechat['tel'],
                'sex' => $original['sex'],
            ]);
        }

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

        //我的组员
        $my_children = Customer::with('patrons')->wherein('id', auth()->user()->children->pluck('id'))->get()->map(function ($model) {

            foreach ($model->patrons as &$patron) {
                $need_arr = [0 => 'APP', 1 => '小程序', 2 => '网站', 3 => '系统软件', 4 => '其它'];
                $patron['need'] = $need_arr[$patron['need']];
            }
            return $model;
        });

        return view('home', compact('my_patrons', 'our_patrons', 'my_children'));
    }

    public function password()
    {
        return view('password');
    }

    public function retrieve(Request $request)
    {
        $customer = auth()->user();
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

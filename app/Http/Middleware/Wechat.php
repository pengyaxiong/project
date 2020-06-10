<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use Closure;

class Wechat
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //echo json_encode(session('wechat.customer'));exit();
        if (!session('wechat.customer')) {
            $openid=$request->openid;

            $customer = Customer::where('openid', $openid)->first();
            if ($customer) {
                $customer->update([
                    'openid'=>$openid,
                ]);
            } else {
                $customer = Customer::create([
                    'openid'=>$openid,
                ]);
            }
            session(['wechat.customer' => $customer]);
        }

        return $next($request);
    }
}
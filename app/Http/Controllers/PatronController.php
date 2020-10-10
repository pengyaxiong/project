<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Patron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Models\Activity;
use App\Models\Staff;
use App\Notifications\TopicReplied;

class PatronController extends Controller
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

    public function create()
    {
        $customer_id = auth()->user()->id;
        $parent_id = auth()->user()->parent_id;
        $customers = Customer::where('parent_id', $customer_id)->orwhere('id', $customer_id)->where('status', 1)->get();

        return view('add_patron', compact('parent_id', 'customers'));
    }

    public function store(Request $request)
    {
        $customer_id = auth()->user()->id;

//        $start_time = str_replace('T', ' ', $request->start_time);
//        $request->merge(['start_time' => $start_time]);

//        if ($request->customer_id>0){
//            $request->offsetSet('customer_id', $customer_id);
//        }
        try {
            $messages = [
                'company_name.required' => '公司名称不能为空',
                'name.required' => '联系人不能为空',
                'phone.required' => '联系人电话不能为空',
                'phone.unique' => '联系人已经存在',
                'job.required' => '联系人职位不能为空',
                'need.required' => '需求不能为空',
            ];
            $rules = [
                'company_name' => 'required',
                'name' => 'required',
//                'phone' => 'unique:wechat_patron',
                'phone' => 'required|unique:wechat_patron',
                'job' => 'required',
                'need' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $error = $validator->errors()->first();

                return back()->with('notice', $error);
            }

            Patron::create($request->all());

        } catch (\Exception $exception) {

            Log::error($exception->getMessage());
        }

        return redirect('/home');
    }

    public function edit($id)
    {
        $patron = Patron::find($id);
//        $patron->start_time = str_replace(' ', 'T', $patron->start_time);
        $patron->start_time = date('Y-m-d', strtotime($patron->start_time));

        $customer_id = auth()->user()->id;
        $parent_id = auth()->user()->parent_id;
        $customers = Customer::where('parent_id', $customer_id)->orwhere('id', $customer_id)->where('status', 1)->get();

        return view('edit_patron', compact('patron', 'parent_id', 'customers'));
    }

    public function update(Request $request, $id)
    {
        $patron = Patron::find($id);

        $messages = [
            'company_name.required' => '公司名称不能为空',
            'name.required' => '联系人不能为空',
            'phone.unique' => '联系人已经存在',
            'phone.required' => '联系人电话不能为空',
            'job.required' => '联系人职位不能为空',
            'need.required' => '需求不能为空',
        ];
        $rules = [
            'company_name' => 'required',
            'name' => 'required',
            'phone' => 'required|unique:wechat_patron,phone,' . $patron->id,
            'job' => 'required',
            'need' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return back()->with('notice', $error);
        }

        $customer_id = auth()->user()->id;
        $parent_id = auth()->user()->parent_id;
        if ($patron->customer_id != $customer_id && $parent_id > 0) {
            return back()->with('notice', '您没用权限修改,请联系主管');
        }
//        $start_time = str_replace('T', ' ', $request->start_time);
//        $request->merge(['start_time' => $start_time]);


        $patron->update($request->only('customer_id', 'company_name', 'name', 'phone', 'job', 'need', 'money', 'start_time', 'relation', 'remark'));

        return back()->with('success', '成功');
    }

    /**
     * Ajax修改属性
     * @param Request $request
     * @return array
     */
    public function is_something(Request $request)
    {
        $attr = $request->attr;
        $patron = Patron::with('customer')->find($request->id);
        $value = $patron->$attr ? false : true;
        $patron->$attr = $value;
        $patron->start_time = date('Y-m-d');

        $patron->save();

        activity()->inLog(2)
            ->performedOn($patron)
            ->causedBy(auth()->user())
            ->withProperties([])
            ->log(auth()->user()->name . '与客户' . $patron->name . '已签约');

        $lastLoggedActivity = Activity::all()->last();
        $staffs = Staff::where('is_notice', 1)->get();
        //执行消息分发
        dispatch(new \App\Jobs\SendNotice($staffs, new TopicReplied($lastLoggedActivity), 5));
    }

    public function follow(Request $request)
    {
        $patron = Patron::find($request->id);

        $arr = [[
            'time' => date('Y-m-d H:i:s'),
            'content' => $request->input('content')
        ]];
        $patron->follow = array_merge($patron->follow, $arr);

        $patron->save();
    }

}

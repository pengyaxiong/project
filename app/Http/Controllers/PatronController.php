<?php

namespace App\Http\Controllers;

use App\Models\Patron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
        return view('add_patron');
    }

    public function store(Request $request)
    {
        $customer_id = auth()->user()->id;

        $start_time = str_replace('T', ' ', $request->start_time);
        $request->merge(['start_time' => $start_time]);

        $request->offsetSet('customer_id', $customer_id);

        try {
            $messages = [
                'name.required' => $request->name,
                'phone.required' => $request->phone,
            ];
            $rules = [
                'name' => 'required',
                'phone' => 'required',
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
        $patron->start_time = str_replace(' ', 'T', $patron->start_time);

        return view('edit_patron', compact('patron'));
    }

    public function update(Request $request, $id)
    {
        $patron = Patron::find($id);

        $customer_id = auth()->user()->id;
        if ($patron->customer_id != $customer_id) {
            return back()->with('notice', '您没用权限修改,请联系主管');
        }
        $start_time = str_replace('T', ' ', $request->start_time);
        $request->merge(['start_time' => $start_time]);
        $patron->update($request->all());

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
        $patron = Patron::find($request->id);
        $value = $patron->$attr ? false : true;
        $patron->$attr = $value;
        $patron->save();
    }

    public function follow(Request $request)
    {
        $patron = Patron::find($request->id);

        $arr=[[
            'time'=>date('Y-m-d H:i:s'),
            'content'=>$request->content
        ]];
        $patron->follow=array_merge($patron->follow,$arr);

        $patron->save();
    }
}

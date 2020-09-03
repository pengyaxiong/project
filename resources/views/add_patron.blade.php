@extends('layouts.app')
<style>

</style>
@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">新增客户资讯</div>
        <div class="panel-body">
            @if (session('notice'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">&times;</span>
                    </button>
                    <strong>错误!</strong>{{ session('notice') }}
                </div>
            @endif

            <form method="POST" action="{{ route('patron.store') }}">
                @csrf
                <div class="form-group">
                    <label for="exampleInputEmail1">来源</label>
                    <select name="from" class="form-control">
                        <option value="0">线上</option>
                        <option value="1">线下</option>
                        <option value="2">其它</option>
                    </select>
                </div>

                @if($parent_id==0)
                    @if(!empty($customers))
                        <div class="form-group">
                            <label for="exampleInputEmail1">所属商务</label>
                            <select name="customer_id" class="form-control">
                                <option value="0">共有池</option>
                                @foreach($customers as $customer)
                                    <option value="{{$customer->id}}">{{$customer->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                @else
                    <div class="form-group">
                        <label for="exampleInputEmail1">所属商务</label>
                        <select name="customer_id" class="form-control">
                            <option value="0">共有池</option>
                            <option value="{{auth()->user()->id}}">{{auth()->user()->name}}</option>
                        </select>
                    </div>
                @endif
                <div class="form-group">
                    <label for="exampleInputEmail1">公司名称 </label>
                    <input type="text" class="form-control" name="company_name" id="" placeholder="">
                </div>
                <div class="form-group">
                    <label for="">联系人</label>
                    <input type="text" class="form-control" name="name" id="" placeholder="">
                </div>
                <div class="form-group">
                    <label for="">手机号</label>
                    <input type="text" class="form-control" name="phone" id="" placeholder="">
                </div>
                <div class="form-group">
                    <label for="">职位</label>
                    <input type="text" class="form-control" name="job" id="" placeholder="">
                </div>
                <div class="form-group">
                    <label for="">需求</label>
                    <select name="need" class="form-control">
                        <option value="0">APP</option>
                        <option value="1">小程序</option>
                        <option value="2">网站</option>
                        <option value="3">系统软件</option>
                        <option value="4">其它</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="">预算</label>
                    <input type="number" name="money" class="form-control" id="" placeholder="">
                </div>

                {{--<div class="form-group">--}}
                    {{--<label for="">签约时间</label>--}}
                    {{--<input type="date" class="form-control" name="start_time" id="" placeholder="">--}}
                {{--</div>--}}

                <div class="form-group">
                    <label for="">客户关系</label>
                    <textarea class="form-control" rows="3" name="relation"></textarea>
                </div>

                {{--<div class="form-group">--}}
                {{--<label for="">跟进记录</label>--}}
                {{--<input type="text" class="form-control" id="" placeholder="">--}}
                {{--</div>--}}

                <div class="form-group">
                    <label for="">备注</label>
                    <textarea class="form-control" rows="3" name="remark"></textarea>
                </div>

                {{--<div class="form-group">--}}
                {{--<label for="exampleInputFile">附件</label>--}}
                {{--<input type="file" id="exampleInputFile">--}}
                {{--<p class="help-block">选择上传.</p>--}}
                {{--</div>--}}

                <button type="submit" class="btn btn-default">新增</button>
            </form>
        </div>
    </div>
@endsection

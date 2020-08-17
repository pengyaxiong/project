@extends('layouts.app')
<style>

</style>
@section('content')
    @if(!empty($patron->follow) && $patron->customer_id==auth()->user()->id)
        <div class="panel panel-default">
            <div class="panel-heading">跟进记录</div>
            <div class="panel-body">
                <div class="list-group">
                    @foreach($patron->follow as $follow)
                        <a href="#" class="list-group-item">
                            <h4 class="list-group-item-heading">{{$follow['time']}}</h4>
                            <p class="list-group-item-text">{{$follow['content']}}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    <div class="panel panel-default">
        <div class="panel-heading">修改客户资讯</div>
        <div class="panel-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">&times;</span>
                    </button>
                    <strong>成功!</strong>{{ session('success') }}
                </div>
            @endif
            @if (session('notice'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">&times;</span>
                    </button>
                    <strong>失败!</strong>{{ session('notice') }}
                </div>
            @endif
            <form method="POST" action="{{ route('patron.update',$patron->id) }}">
                @csrf
                {{ method_field('PUT') }}
                <div class="form-group">
                    <label for="exampleInputEmail1">来源</label>
                    <select name="from" class="form-control">
                        <option value="0" @if($patron->from==0) selected @endif>线上</option>
                        <option value="1" @if($patron->from==1) selected @endif>线下</option>
                        <option value="2" @if($patron->from==2) selected @endif>其它</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">公司名称 </label>
                    <input type="text" class="form-control" name="company_name" value="{{$patron->company_name}}" id=""
                           placeholder="">
                </div>
                <div class="form-group">
                    <label for="">联系人</label>
                    <input type="text" class="form-control" name="name" value="{{$patron->name}}" id="" placeholder="">
                </div>
                <div class="form-group">
                    <label for="">手机号</label>
                    <input type="text" class="form-control" name="phone" value="{{$patron->phone}}" id=""
                           placeholder="">
                </div>
                <div class="form-group">
                    <label for="">职位</label>
                    <input type="text" class="form-control" name="job" value="{{$patron->job}}" id="" placeholder="">
                </div>
                <div class="form-group">
                    <label for="">需求</label>
                    <select name="need" class="form-control">
                        <option value="0" @if($patron->need==0) selected @endif>APP</option>
                        <option value="1" @if($patron->need==1) selected @endif>小程序</option>
                        <option value="2" @if($patron->need==2) selected @endif>网站</option>
                        <option value="3" @if($patron->need==3) selected @endif>系统软件</option>
                        <option value="4" @if($patron->need==4) selected @endif>其它</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="">预算</label>
                    <input type="number" name="money" class="form-control" id="" value="{{$patron->money}}"
                           placeholder="">
                </div>

                <div class="form-group">
                    <label for="">开始时间</label>
                    <input type="datetime-local" class="form-control" name="start_time" id=""
                           value="{{$patron->start_time}}" placeholder="">
                </div>

                <div class="form-group">
                    <label for="">客户关系</label>
                    <input type="text" class="form-control" name="relation" id="" value="{{$patron->relation}}"
                           placeholder="">
                </div>

                {{--<div class="form-group">--}}
                {{--<label for="">跟进记录</label>--}}
                {{--<input type="text" class="form-control" id="" placeholder="">--}}
                {{--</div>--}}

                <div class="form-group">
                    <label for="">备注</label>
                    <textarea class="form-control" rows="3" name="remark">{{$patron->remark}}</textarea>
                </div>

                {{--<div class="form-group">--}}
                {{--<label for="exampleInputFile">附件</label>--}}
                {{--<input type="file" id="exampleInputFile">--}}
                {{--<p class="help-block">选择上传.</p>--}}
                {{--</div>--}}

                <button type="submit" class="btn btn-default">修改</button>
            </form>
        </div>
    </div>
@endsection

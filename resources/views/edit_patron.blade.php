@extends('layouts.app')
@section('css')
    <link href="/vendor/bootstrap-fileinput/css/fileinput.min.css" rel="stylesheet">
@endsection
@section('content')
    @if(!empty($patron->follow))
        @if($patron->customer_id==auth()->user()->id ||auth()->user()->parent_id==0)
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

                @if($parent_id==0)
                    @if(!empty($customers))
                        <div class="form-group">
                            <label for="exampleInputEmail1">所属商务</label>
                            <select name="customer_id" class="form-control">
                                <option value="0" @if($patron->customer_id==0) selected @endif>共有池</option>
                                @foreach($customers as $customer)
                                    <option value="{{$customer->id}}"
                                            @if($patron->customer_id==$customer->id) selected @endif>{{$customer->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                @else
                    <div class="form-group">
                        <label for="exampleInputEmail1">所属商务</label>
                        <select name="customer_id" class="form-control">
                            <option value="0" @if($patron->customer_id==0) selected @endif>共有池</option>
                            <option value="{{auth()->user()->id}}" @if($patron->customer_id==auth()->user()->id) selected @endif>{{auth()->user()->name}}</option>
                        </select>
                    </div>
                @endif

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
                    <label for="">签约时间</label>
                    <input type="date" class="form-control" name="start_time" id=""
                           value="{{$patron->start_time}}" placeholder="">
                </div>

                <div class="form-group">
                    <label for="">客户关系</label>
                    <textarea class="form-control" rows="3" name="relation">{{$patron->relation}}</textarea>
                </div>

                {{--<div class="form-group">--}}
                {{--<label for="">跟进记录</label>--}}
                {{--<input type="text" class="form-control" id="" placeholder="">--}}
                {{--</div>--}}

                <div class="form-group">
                    <label for="">备注</label>
                    <textarea class="form-control" rows="3" name="remark">{{$patron->remark}}</textarea>
                </div>

                <div class="form-group">
                    <label for="images">附件</label>
                    <input type="file" id="images" name="file" data-overwrite-initial="false">
                </div>

                <button type="submit" class="btn btn-default">修改</button>

                <button id="delete_patron" data-id="{{$patron->id}}" data-customer_id="{{auth()->user()->id}}" type="button" class="btn btn-danger">删除</button>

            </form>

        </div>
    </div>
@endsection
@section('js')
    <script src="/vendor/bootstrap-fileinput/js/fileinput.min.js"></script>
    <script src="/vendor/bootstrap-fileinput/js/fileinput_locale_zh.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        var images = '{{ implode(',',$patron->images) }}';
        var data = images.split(',');
        var html = [];

        data.forEach(function (element, index) {
            //初始化fileinput控件（第一次初始化）
            html[index] = "<img src=/storage/" + element + " class='file-preview-image'><i class='glyphicon glyphicon-trash text-danger delete_image' data-id='{{$patron->id}}' data-index='" + index + "'></i>"
        });
        console.log(data);
        if (data[0]) {
            initFileInput("images", "/api/upload_image?id={{$patron->id}}", html);
        } else {
            initFileInputnull("images", "/api/upload_image?id={{$patron->id}}");
        }

        function initFileInputnull(ctrlName, uploadUrl) {
            var control = $('#' + ctrlName);
            control.fileinput({
                language: 'zh', //设置语言
                uploadUrl: uploadUrl, //上传的地址
                allowedFileExtensions: ['jpg', 'jpeg', 'png', 'gif'],//接收的文件后缀
                overwriteInitial: false,
                showCaption: false,
                maxFileSize: 3000,
                browseClass: "btn btn-default", //按钮样式
            });
        }


        //初始化fileinput控件（第一次初始化）
        function initFileInput(ctrlName, uploadUrl, html) {
            var control = $('#' + ctrlName);
            control.fileinput({
                initialPreview: html,
                language: 'zh', //设置语言
                uploadUrl: uploadUrl, //上传的地址
                allowedFileExtensions: ['jpg', 'jpeg', 'png', 'gif'],//接收的文件后缀
                overwriteInitial: false,
                showCaption: false,
                maxFileSize: 3000,
                browseClass: "btn btn-default", //按钮样式
            });
        }

        $(".delete_image").on('click', function () {
            var index = $(this).data('index');
            var id = $(this).data('id');
            $.post('/api/delete_image', {id: id, index: index}, function (data) {
                if (data.code == 200) {
                    location.href = location.href;
                }
            })
        })

        $("#delete_patron").on('click', function () {
            var customer_id = $(this).data('customer_id');
            var id = $(this).data('id');
            swal({
                title: "确定删除?",
                text: " ",
                icon: "warning",
                buttons: {
                    cancel: "取消",
                    catch: {
                        text: "确定!",
                        value: "willDelete",
                        className: "swal-button--danger",
                    }
                },
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.post('/api/delete_patron', {id: id, customer_id: customer_id}, function (data) {
                        if (data.code == 200) {
                            swal("恭喜!", "删除成功", "success").then((value) => {
                                location.href = '/home';
                            });

                        }else{
                            swal("失败!", "没有权限", "error")
                        }
                    })
                 }

            });

        })
    </script>
@endsection
@extends('layouts.app')
<style>
    .badge_ {
        display: inline-block;
        min-width: 10px;
        padding: 3px 7px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        border-radius: 10px;
        background-color: #f0ad4e;
    !important;
        float: right;
    }

    .follow {
        display: inline-block;
        min-width: 10px;
        padding: 3px 13px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        float: right;
        border-radius: 10px;
        background-color: #5bc0de;
        color: #fff;
        text-align: center;
    }
</style>
@section('content')
    <div class="panel panel-success">
        <div class="panel-heading">我的客户资讯</div>
        <div class="panel-body">
            <ul class="list-group">
                @if(!empty( $my_patrons))
                    @foreach($my_patrons as $patron)

                        <li class="list-group-item">
                            <span class="label label-primary">{{$patron['need']}}</span>
                            <span class="label label-success">{{$patron['money']}}</span>
                            <span class="{{$patron['status']?'badge':'badge_ is_something'}}"
                                  data-id="{{$patron['id']}}"
                                  data-attr="status">{{$patron['status']?$patron['status']==1?'已签约':'已审核':'待签约'}}</span>
                            <a href="{{route('patron.edit',$patron['id'])}}">
                                {{$patron['company_name'].'-'.$patron['name'].'-'.$patron['phone']}}
                            </a>
                            <span class="follow" data-id="{{$patron['id']}}">跟进</span>
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>

    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">公共客户资讯</h3>
        </div>
        <div class="panel-body">
            <ul class="list-group">
                @if(!empty( $our_patrons))
                    @foreach($our_patrons as $patron)
                        <li class="list-group-item">
                            <span class="label label-primary">{{$patron['need']}}</span>
                            <span class="label label-success">{{$patron['money']}}</span>
                            <span class="{{$patron['status']?'badge':'badge_'}}">{{$patron['status']?'已签约':'待签约'}}</span>
                            <a href="{{route('patron.edit',$patron['id'])}}">
                                {{$patron['company_name'].'-'.$patron['name'].'-'.$patron['phone']}}
                            </a>
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>

    @if(!empty($my_children))
        @foreach($my_children as $children)
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">{{$children['name']}}</h3>
                </div>
                <div class="panel-body">
                    <ul class="list-group">
                        @if(!empty($children['patrons']))
                            @foreach($children['patrons'] as $patron)
                                <li class="list-group-item">
                                    <span class="label label-primary">{{$patron['need']}}</span>
                                    <span class="label label-success">{{$patron['money']}}</span>
                                    <span class="{{$patron['status']?'badge':'badge_'}}">{{$patron['status']?'已签约':'待签约'}}</span>
                                    <a href="{{route('patron.edit',$patron['id'])}}">
                                        {{$patron['company_name'].'-'.$patron['name'].'-'.$patron['phone']}}
                                    </a>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        @endforeach
    @endif
    <a class="btn btn-info" href="{{url('/patron/create')}}" role="button"><span class="glyphicon glyphicon-plus-sign"
                                                                                 aria-hidden="true"></span> 新增客户</a>
@endsection
@section('js')
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        $(function () {
            $(".is_something").on('click', function () {
                var _this = $(this);
                var data = {
                    id: _this.data('id'),
                    attr: _this.data('attr')
                }
                swal({
                    title: "确定已签约?",
                    text: " ",
                    icon: "warning",
                    buttons: {
                        cancel: "取消",
                        catch: {
                            text: "确定!",
                            value: "catch",
                        }
                    },
                    dangerMode: true,
                }).then((value) = > {
                    switch(value) {
                    case
                        "catch"
                    :
                        $.ajax({
                            type: "PATCH",
                            url: "/patron/is_something",
                            data: data,
                            success: function (data) {
                                swal("恭喜!", "签约成功", "success").then((value) = > {
                                    location.href = location.href;
                            })
                                ;
                            }
                        });
                        break;
                    }
                }
            )
                ;
            })

            $(".follow").on('click', function () {
                var id = $(this).data('id');
                swal("跟进记录", {
                    content: "input",
                }).then((value) = > {
                    if(
                !value
            )
                {
                    swal("失败!", "跟进记录不能为空", "error");
                    return false;
                }
                $.ajax({
                    type: "POST",
                    url: "/patron/follow",
                    data: {
                        id: id,
                        content: value,
                    },
                    success: function (data) {
                        swal("恭喜!", "跟进成功", "success");
                    }
                });
            })
                ;
            })
        })
    </script>
@endsection

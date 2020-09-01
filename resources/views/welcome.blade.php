@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.0.0/animate.min.css"/>
    <style>
        .modal-dialog {
            top: 31%;
        }
    </style>
@endsection
@section('content')
    <div class="page-header">
        <h1 class="animate__animated  animate__backInDown">公告
            <small>列表</small>
        </h1>
    </div>
    @foreach($notices as $notice)
        <div class="animate__animated animate__fadeInLeft panel panel-default" data-toggle="modal" data-target="#myModal" data-des="{{$notice->description}}" data-title="{{$notice->title}}">
            <div class="panel-body">
                {{$notice->title}}
            </div>
        </div>
    @endforeach

    <br>
    <div class="bs-example" data-example-id="simple-pager">
        <nav aria-label="...">
            <ul class="pager">
                <li class="previous"><a href="{{url('/problem')}}"><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span> 使用帮助</a></li>
                <li class="next"><a href="{{url('/home')}}">个人中心 <span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span></a></li>
            </ul>
        </nav>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Modal title</h4>
                </div>
                <div class="modal-body">

                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(function () {
            $(".panel").on('click', function () {
                var des = $(this).data('des'),
                    title = $(this).data('title');
                $("#myModalLabel").html(title);
                $(".modal-body").html(des);
            })
        })
    </script>
@endsection
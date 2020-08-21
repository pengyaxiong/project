<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- jQuery (Bootstrap 的所有 JavaScript 插件都依赖 jQuery，所以必须放在前边) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@1.12.4/dist/jquery.min.js"></script>
    <!-- 加载 Bootstrap 的所有 JavaScript 插件。你也可以根据需要只加载单个插件。 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container bs-docs-container">
    <div class="row">
        <div class="col-md-12">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>任务名</th>
                    <th>类型</th>
                    <th>时间周期</th>
                    <th>是否完成</th>
                    <th>开始时间</th>
                    <th>已耗时</th>
                </tr>
                </thead>
                <tbody>
                @foreach($tasks as $task)
                    <tr>
                        <th scope="row">{{$loop->index+1}}</th>
                        <td><a href="/admin/tasks">{{$task['name']}}</a></td>
                        <td>{!! $task['grade'] !!}</td>
                        <td>{{$task['days']}}</td>
                        <td>
                            @if($task['is_finish'])
                                <span class="label label-success">已完成</span>
                            @else
                                <span class="label label-info">进行中</span>
                            @endif
                        </td>
                        <td>{{$task['start_time']}}</td>
                        <td>
                            @if($task['is_empty'])
                                <div class="progress">
                                    <div class="progress-bar progress-bar-danger" role="progressbar"
                                         aria-valuenow="{{$task['now_date']}}"
                                         aria-valuemin="{{$task['start_date']}}" aria-valuemax="{{$task['end_date']}}"
                                         style="width: {{$task['rate']}}%;">
                                        {{$task['rate']}}%
                                    </div>
                                </div>
                            @else
                                @if(!$task['is_finish'])
                                    <span class="label label-danger">已超时!!</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>

</html>

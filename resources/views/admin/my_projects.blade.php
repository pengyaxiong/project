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
                    <th>项目名</th>
                    <th>优先级</th>
                    <th>状态</th>
                    <th>预计交付时间</th>
                    <th>进度</th>
                </tr>
                </thead>
                <tbody>
                @foreach($projects as $project)
                    <tr>
                        <th scope="row">{{$loop->index+1}}</th>
                        <td>{{$project['name']}}</td>
                        <td>{{$project['grade']}}</td>
                        <td>{{$project['status']}}</td>
                        <td>{{$project['y_check_time']}}</td>
                        <td>
                            @if($project['is_empty'])
                                倒计时：<span id="wait">{{$project['end_date']-$project['now_date']}}</span><span>秒</span>
                            @else
                                <span class="label label-danger">已超时!!</span>
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
<script type="text/javascript">
    (function () {
        var wait = document.getElementById('wait');
        var interval = setInterval(function () {
            var time = --wait.innerHTML;
            if (time <= 0) {
                clearInterval(interval);
            };
        }, 1000);
    })();
</script>
</html>
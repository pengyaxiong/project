<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('admin.name', 'Laravel') }}</title>

    <!-- Scripts -->
{{--<script src="{{ asset('js/app.js') }}" defer></script>--}}
<!-- jQuery (Bootstrap 的所有 JavaScript 插件都依赖 jQuery，所以必须放在前边) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@1.12.4/dist/jquery.min.js"></script>
    <!-- 加载 Bootstrap 的所有 JavaScript 插件。你也可以根据需要只加载单个插件。 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
{{--<link href="{{ asset('css/app.css') }}" rel="stylesheet">--}}
<!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<table class="table">
    <thead>
    <tr>
        <th>项目名称</th>
        <th>项目状态</th>
        <th>合同金额</th>
        <th>回款金额</th>
        <th>回款进度</th>
    </tr>
    </thead>
    <tbody>
    @if(!empty($projects))
        @foreach($projects as $project)
            <tr>
                <td><a href="/admin/finances?project_id={{$project->id}}">{{$project->name}}</a></td>
                <td>{!! $project->status !!}</td>
                <td>{{$project->money}}</td>
                <td>{{$project->returned_money}}</td>
                <td data-toggle="tooltip" data-placement="top" title="{{$project->check_status}}">
                    <div class="progress">
                        <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar"
                             aria-valuenow="{{$project->rate}}" aria-valuemin="0" aria-valuemax="100" style="width: {{$project->rate}}%;">
                            {{$project->rate}}%
                        </div>
                    </div>
                </td>
            </tr>
        @endforeach
    @endif
    </tbody>
</table>

</body>
</html>

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

<div class="row">
    <div class="col-md-2">
        <div class="row">
            <div class="col-md-4">
                <a type="button" href="/admin/finances" class="btn btn-info btn-sm" data-toggle="tooltip"
                   data-placement="right" title="回款列表">
                    <span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span>&nbsp;返回
                </a>
            </div>
            <div class="col-md-8">
                <button class="btn btn-danger btn-sm">
                    欠款总额：{{$debtors}}
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-10">
        <div class="btn-group btn-group-justified" role="group" aria-label="...">
            <div class="btn-group" role="group">
                <a type="button" href="/admin/finance_statistics" class="btn btn-default">
                    总览
                </a>
            </div>
            <div class="btn-group" role="group">
                <a type="button" href="/admin/finance_month" class="btn btn-default">
                    月度报表
                </a>
            </div>
            <div class="btn-group" role="group">
                <a type="button" href="/admin/finance_quarter" class="btn btn-default" >
                    季度报表
                </a>
            </div>
            <div class="btn-group" role="group">
                <a type="button" href="/admin/finance_year" class="btn btn-primary ">
                   年度报表&nbsp;{{$year}}
                </a>
            </div>

        </div>
    </div>
</div>


</body>
</html>

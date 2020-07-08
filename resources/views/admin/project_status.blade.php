<style>
    .panel-body {
        padding: 15px;
    }

    .panel {
        border-radius: 2px;
        box-shadow: none;
        margin-bottom: 20px;
        background-color: #fff;
        border: 1px solid transparent;
    }

    .circle-icon {
        float: left;
        margin-right: 15px;
        width: 50px;
        height: 50px;
        border-radius: 50px;
        color: #fff;
        text-align: center;
        font-size: 20px;
        line-height: 50px;
    }
</style>
<div class="row">
    <div class="col-md-2 col-sm-6 col-xs-12">
        <section class="panel">
            <div class="panel-body">
                <a href="/admin/projects?status=1">
                    <div class="circle-icon btn-primary">
                        <i class="fa fa-plus-circle"></i>
                    </div>
                </a>
                <div>
                    <h3 class="no-margin" id="s1"></h3> 已立项
                </div>
            </div>
        </section>
    </div>
    <div class="col-md-2 col-sm-6 col-xs-12">
        <section class="panel">
            <div class="panel-body">
                <a href="/admin/projects?status=2">
                    <div class="circle-icon btn-warning">
                        <i class="fa fa-paper-plane-o"></i>
                    </div>
                </a>
                <div>
                    <h3 class="no-margin" id="s2"></h3> 进行中
                </div>
            </div>
        </section>
    </div>
    <div class="col-md-2 col-sm-6 col-xs-12">
        <section class="panel">
            <div class="panel-body">
                <a href="/admin/projects?status=3">
                    <div class="circle-icon btn-danger">
                        <i class="fa fa-pause-circle"></i>
                    </div>
                </a>
                <div>
                    <h3 class="no-margin" id="s3"></h3> 已暂停
                </div>
            </div>
        </section>
    </div>
    <div class="col-md-2 col-sm-6 col-xs-12">
        <section class="panel">
            <div class="panel-body">
                <a href="/admin/projects?status=4">
                    <div class="circle-icon" style="background-color: #d2d6de">
                        <i class="fa fa-power-off"></i>
                    </div>
                </a>
                <div>
                    <h3 class="no-margin" id="s4"></h3> 已结项
                </div>
            </div>
        </section>
    </div>
    <div class="col-md-2 col-sm-6 col-xs-12">
        <section class="panel">
            <div class="panel-body">
                <a href="/admin/projects?is_check=1">
                    <div class="circle-icon btn-success">
                        <i class="glyphicon glyphicon-ok-circle"></i>
                    </div>
                </a>
                <div>
                    <h3 class="no-margin" id="s5"></h3> 已交付
                </div>
            </div>
        </section>
    </div>
    <div class="col-md-2 col-sm-6 col-xs-12">

    </div>
</div>
{{--<div id="project_status" style="height: 400px;width: 100%"></div>--}}
<script src="https://cdn.jsdelivr.net/npm/echarts@4.8.0/dist/echarts.min.js"></script>
<script src="/vendor/echarts/macarons.js"></script>
<script src="/vendor/echarts/china.js"></script>
<script>
    $(function () {
        $.get('/api/project_status').done(function (data) {
            console.log(data);
            $("#s1").html(data[0]);
            $("#s2").html(data[1]);
            $("#s3").html(data[2]);
            $("#s4").html(data[3]);
            $("#s5").html(data[4]);
            var myChart = echarts.init(document.getElementById('project_status'), 'macarons');
            myChart.setOption({
                color: ['#3398DB'],
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                        type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                    }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis: [
                    {
                        type: 'category',
                        data: ['已立项', '进行中', '已暂停', '已结项', '已交付'],
                        axisTick: {
                            alignWithLabel: true
                        }
                    }
                ],
                yAxis: [
                    {
                        type: 'value'
                    }
                ],
                series: [
                    {
                        name: '完成',
                        type: 'bar',
                        barWidth: '60%',
                        data: data
                    }
                ]
            });
        });
    });
</script>
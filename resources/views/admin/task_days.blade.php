<div id="task_days" style="height: 200px;width: 100%"></div>
<script src="https://cdn.jsdelivr.net/npm/echarts@4.8.0/dist/echarts.min.js"></script>
<script src="/vendor/echarts/macarons.js"></script>
<script src="/vendor/echarts/china.js"></script>
<script>
    $(function () {
         var   id="{{\Request::input('id')}}",
              name="{{\Request::input('name')}}",
             is_contract="{{\Request::input('is_contract')}}",
             principal_id="{{Request::input('principal_id')}}",
             access_id="{{\Request::input('access_id')}}";
        $.get('/api/task_days?id='+id+'&name='+name+'&is_contract='+is_contract+'&principal_id='+principal_id+'&access_id='+access_id).done(function (data) {
              console.log(data);
            var myChart = echarts.init(document.getElementById('task_days'), 'macarons');

            // 指定图表的配置项和数据
            myChart.setOption({
                title: {
                    text: '总时长'+data.all+'天',
                    x: 'center'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: data.company
                },
                series: [
                    {
                        name: '时间周期(天)',
                        type: 'pie',
                        radius: '55%',
                        center: ['50%', '60%'],
                        data: data.days,
                        itemStyle: {
                            emphasis: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    }
                ]
            });
        });
    });
</script>
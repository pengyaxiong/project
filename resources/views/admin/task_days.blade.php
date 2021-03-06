<div id="task_days" style="height: 200px;width: 100%"></div>
<script src="https://cdn.jsdelivr.net/npm/echarts@4.8.0/dist/echarts.min.js"></script>
<script src="/vendor/echarts/macarons.js"></script>
<script src="/vendor/echarts/china.js"></script>
<script>
    $(function () {
         var   id="{{\Request::input('id')}}",
              name="{{\Request::input('name')}}",
             start="{{\Request::input('start_time.start')}}",
             end="{{\Request::input('start_time.end')}}",
             is_contract="{{\Request::input('is_contract')}}",
             staff_id="{{Request::input('staff_id')}}",
             customer_id="{{\Request::input('customer_id')}}";
             console.log(start+'---'+end);
        $.get('/api/task_days?id='+id+'&name='+name+'&start='+start+'&end='+end+'&is_contract='+is_contract+'&staff_id='+staff_id+'&customer_id='+customer_id).done(function (data) {
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
                    data: data.node
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
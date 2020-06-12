<div id="task_days" style="height: 200px;width: 100%"></div>
<script src="/vendor/echarts/echarts.min.js"></script>
<script src="/vendor/echarts/macarons.js"></script>
<script src="/vendor/echarts/china.js"></script>
<script>
    $(function () {
        $.get('/api/task_days').done(function (data) {
              console.log(data);
            var myChart = echarts.init(document.getElementById('task_days'), 'macarons');

            // 指定图表的配置项和数据
            myChart.setOption({
                title: {
                    //    text: '会员性别统计',
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
<div id="task_count" style="height: 400px;width: 100%"></div>
<script src="https://cdn.jsdelivr.net/npm/echarts@4.8.0/dist/echarts.min.js"></script>
<script src="/vendor/echarts/macarons.js"></script>
<script src="/vendor/echarts/china.js"></script>
<script>
    $(function () {
        $.get('/api/task_count').done(function (data) {
            console.log(data);
            var type = [];      //类型
            var sell = [];      //数据

            $.each(data.tasks, function (k, v) {
                type.push(v.staff.name);
                sell.push({value: v.sum_num, name: v.staff.name})
            })

            var myChart = echarts.init(document.getElementById('task_count'), 'macarons');

            // 指定图表的配置项和数据
            myChart.setOption({
                title: {
                    //    text: '会员性别统计',
                    subtext: data.month_start + ' ~ ' + data.month_end,
                    x: 'center'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: type
                },
                series: [
                    {
                        name: '任务量（天）',
                        type: 'pie',
                        radius: '55%',
                        center: ['50%', '60%'],
                        data: sell,
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
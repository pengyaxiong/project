<div id="task_rate" style="height: 400px;width: 100%"></div>
<script src="https://cdn.jsdelivr.net/npm/echarts@4.8.0/dist/echarts.min.js"></script>
<script src="/vendor/echarts/macarons.js"></script>
<script src="/vendor/echarts/china.js"></script>
<script>
    $(function () {
        $.get('/api/task_rate').done(function (data) {
            console.log(data);
            var myChart = echarts.init(document.getElementById('task_rate'), 'macarons');

            // 指定图表的配置项和数据
            myChart.setOption({
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross',
                        crossStyle: {
                            color: '#999'
                        }
                    }
                },
                toolbox: {
                    feature: {
                        dataView: {show: true, readOnly: false},
                        magicType: {show: true, type: ['line', 'bar']},
                        restore: {show: true},
                        saveAsImage: {show: true}
                    }
                },
                legend: {
                    data: ['任务数', '签约数', '签约率']
                },
                xAxis: [
                    {
                        type: 'category',
                        data: data.name,
                        axisPointer: {
                            type: 'shadow'
                        }
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        name: '任务数&签约数',
                        min: 0,
                        max: 50,
                        interval: 5,
                        axisLabel: {
                            formatter: '{value}'
                        }
                    },
                    {
                        type: 'value',
                        name: '签约率',
                        min: 0,
                        max: 1,
                        interval: 0.1,
                        axisLabel: {
                            formatter: '{value}'
                        }
                    }
                ],
                series: [
                    {
                        name: '任务数',
                        type: 'bar',
                        data: data.task
                    },
                    {
                        name: '签约数',
                        type: 'bar',
                        data: data.contract
                    },
                    {
                        name: '签约率',
                        type: 'line',
                        symbolSize: 15,
                        yAxisIndex: 1,
                        data: data.rate
                    }
                ]
            });
        });
    });
</script>
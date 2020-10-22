<div id="project_p" style="height: 400px;width: 100%"></div>
<script src="https://cdn.jsdelivr.net/npm/echarts@4.8.0/dist/echarts.min.js"></script>
<script src="/vendor/echarts/macarons.js"></script>
<script src="/vendor/echarts/china.js"></script>
<script>
    $(function () {
        $.get('/api/project_p').done(function (data) {
            console.log(data);
            var myChart = echarts.init(document.getElementById('project_p'), 'macarons');
            myChart.setOption({
                title: {
                    text: '产品项目分布情况',
                    // subtext:  data.week_start + ' ~ ' + data.week_end
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ['已立项', '进行中', '已暂停', '已结项']
                },
                toolbox: {
                    show: true,
                    feature: {
                        dataZoom: {},
                        dataView: {readOnly: false},
                        magicType: {type: ['line', 'bar']},
                        restore: {},
                        saveAsImage: {}
                    }
                },

                xAxis: {
                    type: 'category',
                    data: data.name
                },
                yAxis: {
                    type: 'value',
                    axisLabel: {
                        formatter: '{value}'
                    }
                },
                series: [
                    {
                        name: '已立项',
                        type: 'bar',
                        data: data.count.create,
                    },
                    {
                        name: '进行中',
                        type: 'bar',
                        data: data.count.doing,
                    },
                    {
                        name: '已暂停',
                        type: 'bar',
                        data: data.count.stop,
                    },
                    {
                        name: '已结项',
                        type: 'bar',
                        data: data.count.finish,
                    }
                ]
            });
        })
    });
</script>
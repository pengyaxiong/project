<div id="staff_project" style="height: 600px;width: 100%"></div>
<script src="https://cdn.jsdelivr.net/npm/echarts@4.8.0/dist/echarts.min.js"></script>
<script src="/vendor/echarts/macarons.js"></script>
<script src="/vendor/echarts/china.js"></script>
<script>
    $(function () {
        $.get('/api/staff_project').done(function (data) {
            console.log(data);
            var myChart = echarts.init(document.getElementById('staff_project'), 'macarons');
            // 指定图表的配置项和数据
            myChart.setOption({
                baseOption: {
                    timeline: {
                        axisType: 'category',
                        // realtime: false,
                        // loop: false,
                        autoPlay: true,
                        // currentIndex: 2,
                        playInterval: 1500,
                        // controlStyle: {
                        //     position: 'left'
                        // },
                        data: data.projects,
                    },
                    title: {
                        subtext: '数据来自武汉麦若软创'
                    },
                    tooltip: {
                    },
                    legend: {
                        left: 'right',
                        data: data.nodes,
                    },
                    calculable : true,
                    grid: {
                        top: 80,
                        bottom: 100,
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'shadow',
                                label: {
                                    show: true,
                                    formatter: function (params) {
                                        return params.value.replace('\n', '');
                                    }
                                }
                            }
                        }
                    },
                    xAxis: [
                        {
                            'type':'category',
                            'axisLabel':{'interval':0},
                            'data':data.staffs,
                            splitLine: {show: false}
                        }
                    ],
                    yAxis: [
                        {
                            type: 'value',
                            name: '周期（天）'
                        }
                    ],
                    series: data.node_arr
                },
                options: data.staff_project_arr
            });
        });
    });
</script>
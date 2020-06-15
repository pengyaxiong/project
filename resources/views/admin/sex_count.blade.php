<div id="sex_count" style="height: 400px;width: 100%"></div>
<script src="/vendor/echarts/echarts.min.js"></script>
<script src="/vendor/echarts/macarons.js"></script>
<script src="/vendor/echarts/china.js"></script>
<script>
    $(function () {
        $.get('/api/sex_count').done(function (data) {
          //  console.log(data);
            var myChart = echarts.init(document.getElementById('sex_count'), 'macarons');

            // 指定图表的配置项和数据
            myChart.setOption({
                title: {
                    //    text: '会员性别统计',
                    //
                    // subtext:'1-2',
                    x: 'center'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: ['男', '女', '其它']
                },
                series: [
                    {
                        name: '性别',
                        type: 'pie',
                        radius: '55%',
                        center: ['50%', '60%'],
                        data: [
                            {value: data.male, name: '男'},
                            {value: data.female, name: '女'},
                            {value: data.other, name: '其它'}
                        ],
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
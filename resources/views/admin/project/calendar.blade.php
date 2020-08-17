<link rel='stylesheet' href='/vendor/fullcalendar/bootstrap.min.css'/>
<link rel='stylesheet' href='/vendor/fullcalendar/fullcalendar.css'/>
<link href='/vendor/fullcalendar/fullcalendar.print.min.css' rel='stylesheet' media='print'/>
<link rel="stylesheet" href="/vendor/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css">
<link rel="stylesheet" href="/vendor/bootstrap-datepicker/css/bootstrap-datepicker.min.css">
<style>
    #top {
        padding: 0 10px;
        font-size: 12px;
    }

    #edit_from {
        display: none;
    }
</style>

<div id='top'>
    语言:
    <select id='locale-selector'></select>
    <div id="success"></div>
    <div id="err"></div>
</div>
<div class="panel-body">
    <div id='calendar'>
    </div>
</div>

<div class="modal in" tabindex="-1" id="edit_from" role="dialog" aria-hidden="true">
    <div class="modal-dialog " role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close close_edit" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                <h4 class="modal-title">编辑日程</h4>
            </div>
            <form>
                <div class="modal-body">
                    <div class="form-group">
                        <label>日程名称</label>
                        <input type="text" name="title" id="title" value="" class="form-control express_name action"
                               placeholder="输入 日程">
                    </div>
                    <div class="form-group">
                        <label>背景色</label>
                        <div id="cp2" class="input-group colorpicker-component">
                            <input type="text" class="form-control" name="color" id="color"
                                   placeholder="">
                            <span class="input-group-addon"><i></i></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>时间</label>
                        <div class="input-group input-daterange">
                            <input type="text" class="form-control" name="start" id="start"
                                   placeholder="" data-date-format="yyyy.mm.dd.HH.mm.ss">
                            <div class="input-group-addon">至</div>

                            <input type="text" class="form-control" name="end" id="end"
                                   placeholder="" data-date-format="yyyy.mm.dd.HH.mm.ss">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>物流单号</label>
                        <input type="text" id="express_code" name="express_code" value=""
                               class="form-control express_code action" placeholder="输入 物流单号">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default close_edit">关闭</button>
                    <button type="submit" class="btn btn-primary" id="edit">提交</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>

</div>

<script src='/vendor/fullcalendar/lib/jquery.min.js'></script>
<script src='/vendor/fullcalendar/lib/moment.min.js'></script>
<script src='/vendor/fullcalendar/fullcalendar.min.js'></script>
<script src='/vendor/fullcalendar/locale-all.js'></script>
<script src='/vendor/fullcalendar/bootstrap.min.js'></script>
<script src='/vendor/fullcalendar/destroy.js'></script>

<script>
    $(document).ready(function () {
        $('#cp2').colorpicker({});//颜色js

        $(".close_edit").click(function () {
            $("#edit_from").hide();
        })
        $("#start,#end").datepicker({//时间js
            format: 'yyyy-mm-dd hh:ii:ss',
            minuteStep:1,
            weekStart: 1,
            minView: 0,
            autoclose: true,
            language: 'zh-CN',
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        //国际化默认值为'en'，代表使用英文
        var Lang = 'zh-cn';
        //页面加载完初始化日历
        $('#calendar').fullCalendar({
            //设置选项和回调
            header: {
                right: 'prev,next,today',
                center: 'title',
                left: 'month,agendaWeek,agendaDay,listMonth'
            },
            locale: Lang,
            timeFormat: 'H:mm',
            buttonIcons: false, // show the prev/next text
            weekNumbers: true,
            navLinks: true, // can click day/week names to navigate views
            selectable: true,
            selectHelper: true,
            dayClick: function (date) {
                //do something here...
                {{--$.post('{{route('calendar.store')}}',{id:1,title:2},function () {--}}
                {{--console.log('success');--}}

            },
            eventClick: function (event) {
                //do something here...
                $("#edit_from").show();
                $('#title').val(event.title);
                $('#color').val(event.color);
                $('#start').val($.fullCalendar.formatDate(event.start, "YYYY-MM-DD HH:mm:ss"));
                $('#end').val($.fullCalendar.formatDate(event.end, "YYYY-MM-DD HH:mm:ss"));
                // ...
                //模态框编辑
                $(document).on("click", "#edit", function () {
                    var eventData;
                    eventData = {
                        id: event.id,
                        title: event.title,
                        color: $('#color').val(),
                        start: $('#start').val(),
                        end: $('#end').val(),
                    };
                    //console.log(eventData);
                    $.ajax({
                        type: "PUT",
                        url: '/admin/calendar/' + event.id + '',
                        data: eventData,
                        success: function () {
                            location.replace(document.referrer);
                        }
                    });
                })
            },
            select: function (start, end) {
                var title = prompt('添加日程');
                var eventData;
                if (title) {
                    eventData = {
                        title: title,
                        start: $.fullCalendar.formatDate(start, "YYYY-MM-DD HH:mm:ss"),
                        end: $.fullCalendar.formatDate(end, "YYYY-MM-DD HH:mm:ss")
                    };
                    $.ajax({
                        url: "{{route('admin.calendar.store')}}",
                        type: "post",
                        data: eventData,
                        success: function (res) {
                            $("#success").html("<strong>添加成功!</strong>");
                            $("#success").show();
                            setTimeout(function () {
                                $("#success").hide();
                            }, 3000);
                            $('#calendar').fullCalendar('refetchEvents');
                        },
                        error: function (result) {
                            $("#err").html("<strong>操作有误!</strong>");
                            $("#err").show();
                            setTimeout(function () {
                                $("#err").hide();
                            }, 3000);
                        }
                    });
                }
                $('#calendar').fullCalendar('unselect');
            },
            //Event是否可被拖动或者拖拽
            editable: true,
            eventLimit: true, // allow "more" link when too many events
            eventDrop: function (event, dayDelta, jsEvent, revertFunc) {
                var eventData;
                eventData = {
                    id: event.id,
                    title: event.title,
                    color: event.color,
                    start: $.fullCalendar.formatDate(event.start, "YYYY-MM-DD HH:mm:ss"),
                    end: $.fullCalendar.formatDate(event.end, "YYYY-MM-DD HH:mm:ss"),
                    day: dayDelta._days + 'days',
                };
                $.ajax({
                    type: "PUT",
                    url: '/admin/drop/' + event.id + '',
                    data: eventData,
                    success: function () {
                        console.log(eventData);
                    },
                    error: function () {
                        revertFunc();
                    }
                });
            },
            eventResize: function (event, dayDelta, revertFunc) {
                var eventData;
                eventData = {
                    id: event.id,
                    title: event.title,
                    color: event.color,
                    start: $.fullCalendar.formatDate(event.start, "YYYY-MM-DD HH:mm:ss"),
                    end: $.fullCalendar.formatDate(event.end, "YYYY-MM-DD HH:mm:ss")
                };
                console.log(eventData);
                $.ajax({
                    type: "PUT",
                    url: '/admin/calendar/' + event.id + '',
                    data: eventData,
                    success: function (data, status) {
                        //alert("修改“" + data + "”成功！(status:" + status + ".)");
                    },
                    error: function (data, status) {
                        consol.log(data);
                        //revertFunc();
                    }
                });
            },
            events: {
                url: '/admin/event',
                error: function () {
                    $('#err').show();
                }
            },
        })

        // build the locale selector's options
        $.each($.fullCalendar.locales, function (localeCode) {
            $('#locale-selector').append(
                $('<option/>')
                    .attr('value', localeCode)
                    .prop('selected', localeCode == Lang)
                    .text(localeCode)
            );
        });
        // when the selected option changes, dynamically change the calendar option
        $('#locale-selector').on('change', function () {
            if (this.value) {
                $('#calendar').fullCalendar('option', 'locale', this.value);
            }
        });
    });
</script>
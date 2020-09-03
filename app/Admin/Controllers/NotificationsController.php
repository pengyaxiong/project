<?php

namespace App\Admin\Controllers;

use App\Models\Staff;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Table;
use Illuminate\Http\Request;
use Encore\Admin\Widgets\Box;

class NotificationsController extends AdminController
{
    public function index(Content $content)
    {

        // 获取登录用户的所有通知
        return $content
            ->title('消息通知')
            ->description('列表')
            ->row(function (Row $row) {
                $row->column(12, function (Column $column) {

                    $staff = Staff::where('admin_id', auth('admin')->user()->id)->first();
                    $staff->markAsRead();
                    $notifications = $staff->notifications->map(function ($model) {
                        $nodes = [
                            'id' => $model->id,
                            'name' => $model->data['name'],
                            'log_name' => $model->data['title'],
                            'description' => $model->data['description'],

//                            'content' => "<a class='btn btn-xs action-btn btn-danger grid-row-refuse' data-id='{$model->id}'><i class='fa fa-eye' title='详情'>详情</i></a>"
                        ];
                        return $nodes;
                    });

                    $table = new Table(['ID', '操作者', '类型', '详情'], $notifications->toArray());

                    $column->append(new Box('', $table->render()));


                    /**
                     * 创建模态框
                     */
                    $this->script = <<<EOT
                    $('.grid-row-refuse').unbind('click').click(function() {
                        var id = $(this).data('id');
                        $.ajax({
                            method: 'get',
                            url: '/admin/projects/info/' + id,
                            success: function (data) {
                                console.log(data);
                                var content = "无记录";
                                if (data.length>0) {
                                    var html1="<table class='table'>"
                                        + "<thead><tr>"
                                        + "     <th> 详情</th> <th>备注</th> <th>时间</th>"
                                        + "</tr></thead><tbody>";
                                       
                                     var html2="</tbody></table>"
                                     var html='';
                                     for (var i=0;i<data.length;i++)
                                        { 
                                           html+='<tr><td>'+data[i]['content']+'</td><td>'+data[i]['remark']+'</td><td>'+data[i]['updated_at']+'</td></tr>';
                                        }
                                     content  = html1+html+html2;
                                }

                                swal.fire({
                                    title: '<strong>记录</strong>',
                                 //   type: 'info',
                                    html: content, // HTML
                                    focusConfirm: true, //聚焦到确定按钮
                                    showCloseButton: true,//右上角关闭
                                    customClass: "Alerttable",
                                })
                            }
                        });
                    });
EOT;
                    $this->style = <<<EOT
               .Alerttable{width: 90%; font-size: 14px;}
               .Alerttable th{text-align: center;}
EOT;
                    Admin::script($this->script);
                    Admin::style($this->style);
                });
            });
    }
}

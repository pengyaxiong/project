<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\Notice;
use App\Models\Project;
use App\Models\ProjectNode;
use App\Models\ProjectStaff;
use App\Models\Staff;
use App\Models\Task;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Widgets\Form;
use Encore\Admin\Widgets\Collapse;

class HomeController extends Controller
{
    public function index(Content $content)
    {

        $auth = auth('admin')->user();

        return $content
            ->title('图表统计')
            ->description('list...')
            //  ->row(Dashboard::title())
            ->row(function (Row $row) use ($auth) {
//                $row->column(12, function (Column $column) {
//                    $column->append(new Box('项目状态统计', view('admin.project_status')));
//                });
                $row->column(12, function (Column $column) {
                    $column->row(function (Row $row) {
                        $row->column(2, function (Column $column) {
                            $column->append($this->info_1());
                        });
                        $row->column(2, function (Column $column) {
                            $column->append($this->info_2());
                        });
                        $row->column(2, function (Column $column) {
                            $column->append($this->info_3());
                        });
                        $row->column(2, function (Column $column) {
                            $column->append($this->info_4());
                        });
                        $row->column(2, function (Column $column) {
                            $column->append($this->info_5());
                        });
                    });
                });

                if ($auth->id == 1) {
                    $row->column(12, function (Column $column) {
                        $column->row(function (Row $row) {
//                        $row->column(6, function (Column $column) {
//                            $column->append(new Box('任务签约情况统计', view('admin.chartjs')));
//                        });
                            $row->column(6, function (Column $column) {
                                $column->append(new Box('本月任务量统计', view('admin.task_count')));
                            });
                            $row->column(6, function (Column $column) {
                                $column->append(new Box('员工性别统计', view('admin.sex_count')));
                            });
                        });
                    });

                    $row->column(12, function (Column $column) {
                        $column->append(new Box('任务签约情况统计', view('admin.task_rate')));
                    });

                    $row->column(12, function (Column $column) {
                        $column->append(new Box('方案签约率统计', view('admin.case_count')));
                    });

                    $row->column(12, function (Column $column) {
                        $column->append(new Box('项目节点时间统计', view('admin.project_count')));
                    });

                    $row->column(12, function (Column $column) {
                        $column->append(new Box('员工项目情况分析', view('admin.staff_project')));
                    });

                    $row->column(12, function (Column $column) {
                        $column->append(Dashboard::environment());
                    });
                } else {
                    $row->column(12, function (Column $column) {
                        $column->append($this->my_notices());
                        /**
                         * 创建模态框
                         */
                        $this->script = <<<EOT
                        $('.grid-row-refuse').unbind('click').click(function() {
                            var des=$(this).data('des');
                            var title=$(this).data('title');
                            swal.fire({
                                        title: '<strong>'+title+'</strong>',
                                        html: des, // HTML
                                        focusConfirm: true, //聚焦到确定按钮
                                        showCloseButton: true,//右上角关闭
                             })
                          });
EOT;
                        Admin::script($this->script);
                    });

                    $row->column(12, function (Column $column) {
                        $column->append($this->my_tasks());
                    });

                    $row->column(12, function (Column $column) {
                        $column->append($this->my_projects());
                    });

                }
            });
    }

    public function my_notices()
    {
        $auth = auth('admin')->user();
        $staff = Staff::where('admin_id', $auth->id)->first();
        $notices = Notice::where('department_id', $staff->department_id)->orwhere('department_id', 0)->orderBy('sort_order')->get()->map(function ($model) {
            $result = [
                'content' => "<a class='btn  grid-row-refuse'  data-title='{$model->title}' data-des='{$model->description}'>{$model->title}</a>"
            ];
            return $result;
        });

        if (!empty($notices)) {
            $table = implode(' ',array_pluck($notices->toarray(),'content'));
        } else {
            $table ='暂无~';
        }
        $Box = new Box('公告', $table);
        $Box->collapsable();
        $Box->style('info');
        $Box->solid();
        $Box->scrollable();


        return $Box->render();
    }

    public function info_1()
    {
        $status = Project::where('status', 1)->count();
        // 参数1为标题 参数2为图标 参数3为颜色 参数4为跳转链接 参数5为数据
        $infoBox = new InfoBox('已立项', 'flag-o', 'yellow', '/admin/projects?status=1', $status);
        return $infoBox->render();
    }

    public function info_2()
    {
        $status = Project::where('status', 2)->count();
        // 参数1为标题 参数2为图标 参数3为颜色 参数4为跳转链接 参数5为数据
        $infoBox = new InfoBox('进行中', 'paper-plane-o', 'aqua', '/admin/projects?status=2', $status);
        return $infoBox->render();
    }

    public function info_3()
    {
        $status = Project::where('status', 3)->count();
        // 参数1为标题 参数2为图标 参数3为颜色 参数4为跳转链接 参数5为数据
        $infoBox = new InfoBox('已暂停', 'pause', 'red', '/admin/projects?status=3', $status);
        return $infoBox->render();
    }

    public function info_4()
    {
        $status = Project::where('status', 4)->count();
        // 参数1为标题 参数2为图标 参数3为颜色 参数4为跳转链接 参数5为数据
        $infoBox = new InfoBox('已结项', 'angellist', 'blue', '/admin/projects?status=4', $status);
        return $infoBox->render();
    }

    public function info_5()
    {
        $status = Project::where('is_check', 1)->count();
        // 参数1为标题 参数2为图标 参数3为颜色 参数4为跳转链接 参数5为数据
        $infoBox = new InfoBox('已交付', 'smile-o', 'green', '/admin/projects?status=5', $status);
        return $infoBox->render();
    }

    public function my_tasks()
    {
        $auth = auth('admin')->user();
        $staff_id = Staff::where('admin_id', $auth->id)->first()->id;
        $tasks = Task::where('principal_id', $staff_id)->get()->map(function ($model) {
            $result = [
                'id' => $model->id,
                'name' => $model->name,
                'grade' => $model->node->name,
                'days' => $model->days . '(天)',
                'is_finish' => $model->is_finish,
                'start_time' => $model->start_time,

                'start_date' => strtotime($model->start_time),
                'now_date' => time(),
                'end_date' => strtotime('+' . $model->days . ' days', strtotime($model->start_time)),
                'is_empty' => strtotime('+' . $model->days . ' days', strtotime($model->start_time)) - time() > 0 ? 1 : 0,
                'rate' => round((time() - strtotime($model->start_time)) / ($model->days * 24 * 36), 2),
            ];
            return $result;
        });


//                        $headers = ['任务名', '类型', '时间周期', '开始时间'];
//
//                        $table = new Table($headers, $tasks->toarray());
//
//                        $column->append(new Box('我的任务', $table->render()));
        $Box = new Box('我的任务', view('admin.my_tasks', compact('tasks')));

        $Box->collapsable();
        $Box->style('info');
        $Box->solid();
        $Box->scrollable();

        return $Box->render();
    }

    public function my_projects()
    {
        $auth = auth('admin')->user();
        $staff_id = Staff::where('admin_id', $auth->id)->first()->id;
        $project_ids = ProjectNode::where('staff_id', $staff_id)->pluck('project_id');
        $projects = Project::whereIn('id', $project_ids)->orderby('grade')->get()->map(function ($model) {

            $grade = [
                1 => 'A',
                2 => 'B',
                3 => 'C',
                4 => 'D',
                5 => 'E'
            ];
            $status = [
                1 => '<span class="label label-info">已立项</span>',
                2 => '<span class="label label-primary">进行中</span>',
                3 => '<span class="label label-warning">已暂停</span>',
                4 => '<span class="label label-default">已结项</span>'
            ];
            $result = [
                'id' => $model->id,
                'name' => $model->name,
                'grade' => $grade[$model->grade],
                'status' => $status[$model->status],
                'y_check_time' => $model->y_check_time,

                'end_date' => strtotime($model->y_check_time),
                'now_date' => time(),
                'is_empty' => strtotime($model->y_check_time) - time() > 0 ? 1 : 0,
            ];
            return $result;
        });

//                        $headers = ['项目名', '优先级', '状态', '预计交付时间'];
//
//                        $table = new Table($headers, $projects->toarray());
//
//                        $column->append(new Box('我的项目', $table->render()));

        $Box = new Box('我的项目', view('admin.my_projects', compact('projects')));

        $Box->collapsable();
        $Box->style('info');
        $Box->solid();
        $Box->scrollable();

        return $Box->render();
    }
}

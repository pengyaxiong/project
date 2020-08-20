<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\Project;
use App\Models\ProjectStaff;
use App\Models\Staff;
use App\Models\Task;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Table;

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
                $row->column(12, function (Column $column) {
                    $column->append(new Box('项目状态统计', view('admin.project_status')));
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
                    $row->column(12, function (Column $column) use ($auth) {

                        $staff_id = Staff::where('admin_id', $auth->id)->first()->id;
                        $tasks = Task::where('principal_id', $staff_id)->get()->map(function ($model) {

                                $result = [
                                    'name' => $model->name,
                                    'grade' => $model->node->name,
                                    'days' => $model->days.'(天)',
                                    'start_time' => $model->start_time,
                                ];
                                return $result;
                            });

                        $headers = ['任务名', '类型', '时间周期', '开始时间'];

                        $table = new Table($headers, $tasks->toarray());

                        $column->append(new Box('我的任务', $table->render()));

                    });

                    $row->column(12, function (Column $column) use ($auth) {

                        $staff_id = Staff::where('admin_id', $auth->id)->first()->id;
                        $project_ids = ProjectStaff::where('staff_id', $staff_id)->pluck('project_id');
                        $projects = Project::whereIn('id', $project_ids)->orderby('grade')->get()->map(function ($model) {

                            $grade = [1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E'];
                            $status = [1 => '已立项', 2 => '进行中', 3 => '已暂停', 4 => '已结项'];
                            $result = [
                                'name' => $model->name,
                                'grade' => $grade[$model->grade],
                                'status' => $status[$model->status],
                                'y_check_time' => $model->y_check_time,
                            ];
                            return $result;
                        });

                        $headers = ['项目名', '优先级', '状态', '预计交付时间'];

                        $table = new Table($headers, $projects->toarray());

                        $column->append(new Box('我的项目', $table->render()));
                    });

                }
            });
    }
}

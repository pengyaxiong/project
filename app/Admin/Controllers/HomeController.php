<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\Task;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;

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
                    $row->column(12, function (Column $column) {
                        $column->append(new Box('我的任务', view('admin.my_tasks')));
                    });

                    $row->column(12, function (Column $column) {
                        $column->append(new Box('我的项目', view('admin.my_projects')));
                    });

                }
            });
    }
}

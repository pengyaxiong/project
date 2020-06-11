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

        return $content
            ->title('图表统计')
            ->description('list...')
            //  ->row(Dashboard::title())
            ->row(function (Row $row) {

                $row->column(12, function (Column $column) {
                    $column->row(function (Row $row) {
                        $row->column(6, function (Column $column) {
                            $column->append(new Box('签单情况统计', view('admin.chartjs')));
                        });
                        $row->column(6, function (Column $column) {
                            $column->append(new Box('员工性别统计', view('admin.sex_count')));
                        });
                    });
                });

                $row->column(12, function (Column $column) {
                    $column->append(new Box('方案签约率统计', view('admin.case_count')));
                });

                $row->column(12, function (Column $column) {
                    $column->append(Dashboard::environment());
                });
            });
    }
}

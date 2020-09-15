<?php

namespace App\Admin\Controllers;

use App\Models\Finance;
use App\Models\Project;
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
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\InfoBox;

class StatisticsController extends AdminController
{
    protected $month_start;
    protected $month_end;
    protected $quarter_start;
    protected $quarter_end;
    protected $year_start;
    protected $year_end;

    public function __construct()
    {
        $this->month_start = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 1, date("Y")));
        $this->month_end = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("t"), date("Y")));
        $season = ceil((date('n')) / 3);//当月是第几季度
        $this->quarter_start = date('Y-m-d H:i:s', mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y')));
        $this->quarter_end = date('Y-m-d H:i:s', mktime(23, 59, 59, $season * 3, date('t', mktime(0, 0, 0, $season * 3, 1, date("Y"))), date('Y')));
        $this->year_start = date('Y-m-d H:i:s', strtotime(date("Y", time()) . "-1" . "-1"));
        $this->year_end = date('Y-m-d H:i:s', strtotime(date("Y", time()) . "-12" . "-31"));

    }

    public function finance(Content $content)
    {
        return $content
            ->title('回款统计')
            ->description('总览')
            ->row(function (Row $row) {
                $row->column(12, function (Column $column) {
                    //欠款
                    $money = Project::all()->sum('money');
                    $returned_money = Finance::all()->sum('returned_money');
                    $debtors = $money-$returned_money;

                    $column->append(new Box('', view('admin.statistics.finance', compact('debtors'))));
                });
                $row->column(12, function (Column $column) {
                    $column->row(function (Row $row) {
                        $row->column(2, function (Column $column) {
                            $money = Project::all()->sum('money');
                            $column->append($this->info_all($money));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::all()->sum('returned_money');
                            $column->append($this->info_come($money));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::where('status', 1)->sum('returned_money');
                            $column->append($this->info_1($money));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::where('status', 2)->sum('returned_money');
                            $column->append($this->info_2($money));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::where('status', 3)->sum('returned_money');
                            $column->append($this->info_3($money));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::where('status', 4)->sum('returned_money');
                            $column->append($this->info_4($money));
                        });
                    });
                });

                $row->column(12, function (Column $column) {
                    //未交付项目情况
                    $projects = Project::where('is_check', 0)->get()->map(function ($project) {
                        $status = [1 => '已立项', 2 => '进行中', 3 => '已暂停', 4 => '已结项'];
                        $color = [1 => 'info', 2 => 'primary', 3 => 'danger', 4 => 'success'];
                        $check_status = [1 => '签约审核成功', 2 => '设计验收成功', 3 => '前端验收成功', 4 => '整体验收成功', 5 => '设计评审成功', 6 => '前端评审成功'];

                        $returned_money=Finance::where('project_id',$project->id)->sum('returned_money');
                        $project->status='<span class="label label-'.$color[$project->status].'">'.$status[$project->status].'</span>';
                        $project->returned_money=$returned_money;
                        $project->check_status=$check_status[$project->check_status];
                        $project->is_check=$project->is_check?'<span class="label label-success">已交付</span>':'<span class="label label-danger">未交付</span>';
                        $project->rate=floor($returned_money/($project->money+1)*1000)/10;
                        return $project;
                    });
                    $column->append(new Box('未交付项目情况', view('admin.statistics.finance_detail', compact('projects'))));
                });

            });
    }

    public function finance_month(Content $content)
    {
        return $content
            ->title('回款统计')
            ->description('月度')
            ->row(function (Row $row) {
                $row->column(12, function (Column $column) {
                    $month = date('Y-m-d', strtotime($this->month_start)) . '--' . date('Y-m-d', strtotime($this->month_end));
                    $quarter = date('Y-m-d', strtotime($this->quarter_start)) . '--' . date('Y-m-d', strtotime($this->quarter_end));
                    $year = date('Y-m-d', strtotime($this->year_start)) . '--' . date('Y-m-d', strtotime($this->year_end));
                    //欠款
                    $money = Project::wherebetween('contract_time', [$this->month_start, $this->month_end])->sum('money');
                    $returned_money = Finance::wherebetween('created_at', [$this->month_start, $this->month_end])->sum('returned_money');
                    $debtors = $money-$returned_money;

                    $column->append(new Box('', view('admin.statistics.finance_month', compact('month', 'quarter', 'year', 'debtors'))));
                });
                $row->column(12, function (Column $column) {
                    $column->row(function (Row $row) {
                        $row->column(2, function (Column $column) {
                            $money = Project::wherebetween('contract_time', [$this->month_start, $this->month_end])->sum('money');
                            $column->append($this->info_all($money, $this->month_start, $this->month_end));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::wherebetween('created_at', [$this->month_start, $this->month_end])->sum('returned_money');
                            $column->append($this->info_come($money, $this->month_start, $this->month_end));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::where('status', 1)->wherebetween('created_at', [$this->month_start, $this->month_end])->sum('returned_money');
                            $column->append($this->info_1($money, $this->month_start, $this->month_end));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::where('status', 2)->wherebetween('created_at', [$this->month_start, $this->month_end])->sum('returned_money');
                            $column->append($this->info_2($money, $this->month_start, $this->month_end));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::where('status', 3)->wherebetween('created_at', [$this->month_start, $this->month_end])->sum('returned_money');
                            $column->append($this->info_3($money, $this->month_start, $this->month_end));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::where('status', 4)->wherebetween('created_at', [$this->month_start, $this->month_end])->sum('returned_money');
                            $column->append($this->info_4($money, $this->month_start, $this->month_end));
                        });
                    });
                });

                $row->column(12, function (Column $column) {
                    //未交付项目情况
                    $projects = Project::where('is_check', 0)->wherebetween('contract_time', [$this->month_start, $this->month_end])->get()->map(function ($project) {
                        $status = [1 => '已立项', 2 => '进行中', 3 => '已暂停', 4 => '已结项'];
                        $color = [1 => 'info', 2 => 'primary', 3 => 'danger', 4 => 'success'];
                        $check_status = [1 => '签约审核成功', 2 => '设计验收成功', 3 => '前端验收成功', 4 => '整体验收成功', 5 => '设计评审成功', 6 => '前端评审成功'];

                        $returned_money=Finance::where('project_id',$project->id)->wherebetween('created_at', [$this->month_start, $this->month_end])->sum('returned_money');
                        $project->status='<span class="label label-'.$color[$project->status].'">'.$status[$project->status].'</span>';
                        $project->returned_money=$returned_money;
                        $project->check_status=$check_status[$project->check_status];
                        $project->is_check=$project->is_check?'<span class="label label-success">已交付</span>':'<span class="label label-danger">未交付</span>';
                        $project->rate=floor($returned_money/($project->money+1)*1000)/10;
                        return $project;
                    });
                    $column->append(new Box('未交付项目情况', view('admin.statistics.finance_detail', compact('projects'))));
                });
            });
    }

    public function finance_quarter(Content $content)
    {
        return $content
            ->title('回款统计')
            ->description('季度')
            ->row(function (Row $row) {
                $row->column(12, function (Column $column) {
                    $month = date('Y-m-d', strtotime($this->month_start)) . '--' . date('Y-m-d', strtotime($this->month_end));
                    $quarter = date('Y-m-d', strtotime($this->quarter_start)) . '--' . date('Y-m-d', strtotime($this->quarter_end));
                    $year = date('Y-m-d', strtotime($this->year_start)) . '--' . date('Y-m-d', strtotime($this->year_end));

                    $money = Project::wherebetween('contract_time', [$this->quarter_start, $this->quarter_end])->sum('money');
                    $returned_money = Finance::wherebetween('created_at', [$this->quarter_start, $this->quarter_end])->sum('returned_money');
                    $debtors = $money-$returned_money;

                    $column->append(new Box('', view('admin.statistics.finance_quarter', compact('month', 'quarter', 'year', 'debtors'))));
                });
                $row->column(12, function (Column $column) {
                    $column->row(function (Row $row) {
                        $row->column(2, function (Column $column) {
                            $money = Project::wherebetween('contract_time', [$this->quarter_start, $this->quarter_end])->sum('money');
                            $column->append($this->info_all($money, $this->quarter_start, $this->quarter_end));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::wherebetween('created_at', [$this->quarter_start, $this->quarter_end])->sum('returned_money');
                            $column->append($this->info_come($money, $this->quarter_start, $this->quarter_end));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::where('status', 1)->wherebetween('created_at', [$this->quarter_start, $this->quarter_end])->sum('returned_money');
                            $column->append($this->info_1($money, $this->quarter_start, $this->quarter_end));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::where('status', 2)->wherebetween('created_at', [$this->quarter_start, $this->quarter_end])->sum('returned_money');
                            $column->append($this->info_2($money, $this->quarter_start, $this->quarter_end));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::where('status', 3)->wherebetween('created_at', [$this->quarter_start, $this->quarter_end])->sum('returned_money');
                            $column->append($this->info_3($money, $this->quarter_start, $this->quarter_end));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::where('status', 4)->wherebetween('created_at', [$this->quarter_start, $this->quarter_end])->sum('returned_money');
                            $column->append($this->info_4($money, $this->quarter_start, $this->quarter_end));
                        });
                    });
                });
                $row->column(12, function (Column $column) {
                    //未交付项目情况
                    $projects = Project::where('is_check', 0)->wherebetween('contract_time', [$this->quarter_start, $this->quarter_end])->get()->map(function ($project) {
                        $status = [1 => '已立项', 2 => '进行中', 3 => '已暂停', 4 => '已结项'];
                        $color = [1 => 'info', 2 => 'primary', 3 => 'danger', 4 => 'success'];
                        $check_status = [1 => '签约审核成功', 2 => '设计验收成功', 3 => '前端验收成功', 4 => '整体验收成功', 5 => '设计评审成功', 6 => '前端评审成功'];

                        $returned_money=Finance::where('project_id',$project->id)->wherebetween('created_at', [$this->quarter_start, $this->quarter_end])->sum('returned_money');
                        $project->status='<span class="label label-'.$color[$project->status].'">'.$status[$project->status].'</span>';
                        $project->returned_money=$returned_money;
                        $project->check_status=$check_status[$project->check_status];
                        $project->is_check=$project->is_check?'<span class="label label-success">已交付</span>':'<span class="label label-danger">未交付</span>';
                        $project->rate=floor($returned_money/($project->money+1)*1000)/10;
                        return $project;
                    });
                    $column->append(new Box('未交付项目情况', view('admin.statistics.finance_detail', compact('projects'))));
                });
            });
    }

    public function finance_year(Content $content)
    {
        return $content
            ->title('回款统计')
            ->description('年度')
            ->row(function (Row $row) {
                $row->column(12, function (Column $column) {
                    $month = date('Y-m-d', strtotime($this->month_start)) . '--' . date('Y-m-d', strtotime($this->month_end));
                    $quarter = date('Y-m-d', strtotime($this->quarter_start)) . '--' . date('Y-m-d', strtotime($this->quarter_end));
                    $year = date('Y-m-d', strtotime($this->year_start)) . '--' . date('Y-m-d', strtotime($this->year_end));

                    $money = Project::wherebetween('contract_time', [$this->year_start, $this->year_end])->sum('money');
                    $returned_money = Finance::wherebetween('created_at', [$this->year_start, $this->year_end])->sum('returned_money');
                    $debtors = $money-$returned_money;

                    $column->append(new Box('', view('admin.statistics.finance_year', compact('month', 'quarter', 'year', 'debtors'))));
                });
                $row->column(12, function (Column $column) {
                    $column->row(function (Row $row) {
                        $row->column(2, function (Column $column) {
                            $money = Project::wherebetween('contract_time', [$this->year_start, $this->year_end])->sum('money');
                            $column->append($this->info_all($money, $this->year_start, $this->year_end));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::wherebetween('created_at', [$this->year_start, $this->year_end])->sum('returned_money');
                            $column->append($this->info_come($money, $this->year_start, $this->year_end));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::where('status', 1)->wherebetween('created_at', [$this->year_start, $this->year_end])->sum('returned_money');
                            $column->append($this->info_1($money, $this->year_start, $this->year_end));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::where('status', 2)->wherebetween('created_at', [$this->year_start, $this->year_end])->sum('returned_money');
                            $column->append($this->info_2($money, $this->year_start, $this->year_end));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::where('status', 3)->wherebetween('created_at', [$this->year_start, $this->year_end])->sum('returned_money');
                            $column->append($this->info_3($money, $this->year_start, $this->year_end));
                        });
                        $row->column(2, function (Column $column) {
                            $money = Finance::where('status', 4)->wherebetween('created_at', [$this->year_start, $this->year_end])->sum('returned_money');
                            $column->append($this->info_4($money, $this->year_start, $this->year_end));
                        });
                    });
                });
                $row->column(12, function (Column $column) {
                    //未交付项目情况
                    $projects = Project::where('is_check', 0)->wherebetween('contract_time', [$this->year_start, $this->year_end])->get()->map(function ($project) {
                        $status = [1 => '已立项', 2 => '进行中', 3 => '已暂停', 4 => '已结项'];
                        $color = [1 => 'info', 2 => 'primary', 3 => 'danger', 4 => 'success'];
                        $check_status = [1 => '签约审核成功', 2 => '设计验收成功', 3 => '前端验收成功', 4 => '整体验收成功', 5 => '设计评审成功', 6 => '前端评审成功'];

                        $returned_money=Finance::where('project_id',$project->id)->wherebetween('created_at', [$this->year_start, $this->year_end])->sum('returned_money');
                        $project->status='<span class="label label-'.$color[$project->status].'">'.$status[$project->status].'</span>';
                        $project->returned_money=$returned_money;
                        $project->is_check=$project->is_check?'<span class="label label-success">已交付</span>':'<span class="label label-danger">未交付</span>';
                        $project->check_status=$check_status[$project->check_status];
                        $project->rate=floor($returned_money/($project->money+1)*1000)/10;
                        return $project;
                    });
                    $column->append(new Box('未交付项目情况', view('admin.statistics.finance_detail', compact('projects'))));
                });
            });
    }

    /*
   * .bg-red    .bg-yellow   .bg-aqua   .bg-blue   .bg-light-blue   .bg-green,
      .bg-navy   .bg-teal   .bg-olive   .bg-lime    .bg-orange   .bg-fuchsia   .bg-purple
      .bg-maroon    .bg-black
   */
    public function info_all($money, $start = null, $end = null)
    {
        // 参数1为标题 参数2为图标 参数3为颜色 参数4为跳转链接 参数5为数据
        $infoBox = new InfoBox('合同总额', 'dollar', 'orange', '/admin/finances?created_at[start]=' . $start . '&created_at[end]=' . $end, $money);
        return $infoBox->render();
    }

    public function info_come($money, $start = null, $end = null)
    {
        // 参数1为标题 参数2为图标 参数3为颜色 参数4为跳转链接 参数5为数据
        $infoBox = new InfoBox('回款总额', 'money', 'yellow', '/admin/finances?created_at[start]=' . $start . '&created_at[end]=' . $end, $money);
        return $infoBox->render();
    }

    public function info_1($money, $start = null, $end = null)
    {
        $infoBox = new InfoBox('签约审核收款', 'shopping-bag', 'aqua', '/admin/finances?status=1&created_at[start]=' . $start . '&created_at[end]=' . $end, $money);
        return $infoBox->render();
    }

    public function info_2($money, $start = null, $end = null)
    {
        $infoBox = new InfoBox('设计审核收款', 'photo', 'light-blue', '/admin/finances?status=2&created_at[start]=' . $start . '&created_at[end]=' . $end, $money);
        return $infoBox->render();
    }

    public function info_3($money, $start = null, $end = null)
    {
        $infoBox = new InfoBox('前端审核收款', 'html5', 'blue', '/admin/finances?status=3&created_at[start]=' . $start . '&created_at[end]=' . $end, $money);
        return $infoBox->render();
    }

    public function info_4($money, $start = null, $end = null)
    {
        $infoBox = new InfoBox('验收审核收款', 'smile-o', 'olive', '/admin/finances?status=4&created_at[start]=' . $start . '&created_at[end]=' . $end, $money);
        return $infoBox->render();
    }
}

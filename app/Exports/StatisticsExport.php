<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Finance;
use App\Models\Project;
class StatisticsExport implements WithHeadings, FromArray
{
    private $time;
    protected $month_start;
    protected $month_end;
    protected $quarter_start;
    protected $quarter_end;
    protected $year_start;
    protected $year_end;
    public function __construct($time)
    {
        $this->time = $time;
        $this->month_start = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 1, date("Y")));
        $this->month_end = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("t"), date("Y")));
        $season = ceil((date('n')) / 3);//当月是第几季度
        $this->quarter_start = date('Y-m-d H:i:s', mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y')));
        $this->quarter_end = date('Y-m-d H:i:s', mktime(23, 59, 59, $season * 3, date('t', mktime(0, 0, 0, $season * 3, 1, date("Y"))), date('Y')));
        $this->year_start = date('Y-m-d H:i:s', strtotime(date("Y", time()) . "-1" . "-1"));
        $this->year_end = date('Y-m-d H:i:s', strtotime(date("Y", time()) . "-12" . "-31"));
    }

    // 设置表头
    public function headings() : array
    {
        return [ '项目名称', '交付情况', '项目状态', '项目进度','合同金额', '回款金额','签约审核收款','设计审核收款','前端审核收款','验收审核收款', '回款进度' ];
    }

    public function array() : array
    {
        $time=$this->time;
        if ($time=='month'){
            $time_arr=[$this->month_start, $this->month_end];
        }elseif ($time=='quarter'){
            $time_arr=[$this->quarter_start, $this->quarter_end];
        }elseif ($time=='year'){
            $time_arr=[$this->year_start, $this->year_end];
        }else{
            $time_arr=['2020-01-01 00:00:00','2200-01-01 00:00:00'];
        }
        $projects=Project::where('is_check', 0)->wherebetween('contract_time', $time_arr)->get()->map(function ($project)use ($time_arr) {
            $status = [1 => '已立项', 2 => '进行中', 3 => '已暂停', 4 => '已结项'];
            $check_status = [1 => '签约审核成功', 2 => '设计验收成功', 3 => '前端验收成功', 4 => '整体验收成功', 5 => '设计评审成功', 6 => '前端评审成功'];

            $returned_money = Finance::where('project_id', $project->id)->wherebetween('created_at', $time_arr)->sum('returned_money');
            $result = [
                'name' => $project->name,
                'is_check' => $project->is_check ? '已交付' : '未交付',
                'status' => $status[$project->status],
                'check_status' => $check_status[$project->check_status],
                'money' => $project->money,
                'returned_money' =>$returned_money,
                'returned_money_1' =>Finance::where('project_id', $project->id)->where('status',1)->wherebetween('created_at', $time_arr)->sum('returned_money'),
                'returned_money_2' =>Finance::where('project_id', $project->id)->where('status',2)->wherebetween('created_at', $time_arr)->sum('returned_money'),
                'returned_money_3' =>Finance::where('project_id', $project->id)->where('status',3)->wherebetween('created_at', $time_arr)->sum('returned_money'),
                'returned_money_4' =>Finance::where('project_id', $project->id)->where('status',4)->wherebetween('created_at', $time_arr)->sum('returned_money'),
                'rate' =>floor($returned_money / ($project->money + 1) * 1000) / 10,
            ];
            return $result;
        });


        return $projects->toarray();
    }
}

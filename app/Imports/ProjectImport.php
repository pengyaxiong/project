<?php

namespace App\Imports;

use App\Models\ProjectNode;
use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectImport extends ExcelExporter implements WithMapping
{
    protected $fileName = '项目管理.xlsx';
    protected $grade_status = [1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E'];
    protected $status = [1 => '已立项', 2 => '进行中', 3 => '已暂停', 4 => '已结项'];
    protected $node_status = [1 => '未开始', 2 => '进行中', 3 => '已完成'];
    protected $check_status = [1 => '签约审核成功', 2 => '设计验收成功', 3 => '前端验收成功', 4 => '整体验收成功', 5 => '设计评审成功', 6 => '前端评审成功'];

    protected $columns = [
        'id' => 'ID',
        'name' => '名称',
        'grade' => '优先级',
        'status' => '状态',
        'customer_id' => '商务',
        'project.staffs' => '项目负责人',
        'project.days' => '总天数',
        'check_status' => '回款状态',
        'remark' => '备注',
        'money' => '金额',
        'is_check' => '是否交付',
        'check_time' => '交付时间',
        'is_add' => '是否新增需求',
        'y_check_time' => '预计交付时间',
        'contract_time' => '签约时间',
    ];

    public function map($arr): array
    {
        return [
            $arr->id,
            $arr->name,
            $this->grade_status[$arr->grade?$arr->grade:1],
            $this->status[$arr->status],
            $arr->customer?data_get($arr, 'customer.name'):'',
            implode(',', array_pluck(data_get($arr, 'staffs'), 'name')),
            ProjectNode::where('project_id', $arr->id)->sum('days'),
            $this->check_status[$arr->check_status],
            $arr->remark,
            $arr->money,
            $arr->is_check ? '是' : '否',
            $arr->check_time,
            $arr->is_add ? '是' : '否',
            $arr->y_check_time,
            $arr->contract_time,
        ];
    }
}

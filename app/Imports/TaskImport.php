<?php

namespace App\Imports;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

class TaskImport extends ExcelExporter implements WithMapping
{
    protected $fileName = '任务管理.xlsx';

    protected $columns = [
        'id' => 'ID',
        'node_id' => '类型',
        'name' => '名称',
        'staff_id' => '负责人',
        'customer_id' => '对接人',
        'remark' => '备注',
        'days' => '时间周期(天)',
        'is_contract' => '是否签约',
        'is_finish' => '是否完成',
        'start_time' => '开始时间',
        'contract_time' => '签约时间',
    ];

    public function map($arr): array
    {
        return [
            $arr->id,
            data_get($arr, 'node.name'),
            $arr->name,
            data_get($arr, 'staff.name'),
            data_get($arr, 'customer.name'),
            $arr->remark,
            $arr->days,
            $arr->is_contract?'是':'否',
            $arr->is_finish?'是':'否',
            $arr->start_time,
            $arr->contract_time,
        ];
    }
}

<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

class AuditionImport extends ExcelExporter implements WithMapping
{
    protected $fileName = '面试邀约.xlsx';
    protected $status = [0 => '拒绝', 1 => '通过', 2 => '保留'];

    protected $columns = [
        'id' => 'ID',
        'department_id' => '所属部门',
        'staff_id' => '面试官',
        'name' => '名称',
        'job' => '职位',
        'point' => '分数',
        'phone' => '手机号',
        'money' => '期望薪资',
        'status' => '	状态',
        'remark' => '备注',
        'start_time' => '面试时间',
    ];

    public function map($audition): array
    {
        return [
            $audition->id,
            data_get($audition, 'department.name'),
            data_get($audition, 'staff.name'),
            $audition->name,
            $audition->job,
            $audition->point,
            $audition->phone,
            $audition->money,
            $this->status[$audition->status],
            $audition->remark,
            $audition->start_time,
        ];
    }

}

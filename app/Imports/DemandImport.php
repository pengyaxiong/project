<?php

namespace App\Imports;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;


class DemandImport extends ExcelExporter implements WithMapping
{
    protected $fileName = '新增需求.xlsx';

    protected $columns = [
        'id' => 'ID',
        'project_id' => '项目名称',
        'pact' => '合同',
        'money' => '金额',
        'status' => '状态',
        'description' => '简介',
        'remark' => '备注',
    ];

    public function map($arr): array
    {
        return [
            $arr->id,
            data_get($arr, 'project.name'),
            $arr->pact ? '有' : '无',
            $arr->money,
            $arr->status? '已审核' : '未审核',
            $arr->description,
            $arr->remark,
        ];
    }
}

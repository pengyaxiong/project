<?php

namespace App\Imports;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;


class StaffImport extends ExcelExporter implements WithMapping
{
    protected $fileName = '技术管理.xlsx';
    protected $sex_status = [1 => '男',2 => '女',0 => '其它'];

    protected $columns = [
        'id' => 'ID',
        'name' => '名称',
        'department_id' => '所属部门',
        'sex' => '性别',
        'mobile' => '手机号',
        'email' => '邮箱',
        'remark' => '备注',
    ];

    public function map($arr): array
    {
        return [
            $arr->id,
            $arr->name,
            data_get($arr, 'department.name'),
            $this->sex_status[$arr->sex],
            $arr->mobile,
            $arr->email,
            $arr->remark,
        ];
    }
}

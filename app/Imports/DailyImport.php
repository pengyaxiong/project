<?php

namespace App\Imports;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;


class DailyImport extends ExcelExporter implements WithMapping
{
    protected $fileName = '日志管理.xlsx';

    protected $columns = [
        'id' => 'ID',
        'staff_id' => '名称',
        'work' => '今日工作内容',
        'problem' => '待处理问题',
        'done' => '完成情况',
        'created_at' => '时间',
    ];

    public function map($arr): array
    {
        return [
            $arr->id,
            data_get($arr, 'staff.name'),
            $arr->work,
            $arr->problem,
            $arr->done,
            $arr->created_at,
        ];
    }
}

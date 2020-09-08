<?php

namespace App\Imports;

use App\Models\Patron;
use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerImport extends ExcelExporter implements WithMapping
{
    protected $fileName = '商务管理.xlsx';
    protected $sex_status = [1 => '男',2 => '女',0 => '其它'];

    protected $columns = [
        'id' => 'ID',
        'name' => '名称',
        'status' => '状态',
        'customer.children' => '组员',
        'customer.patrons' => '客户',
        'sex' => '性别',
        'tel' => '电话',
        'remark' => '备注',
    ];

    public function map($arr): array
    {
        $patrons = Patron::where('customer_id',$arr->id)->orwherein('customer_id',array_pluck(data_get($arr, 'children'), 'id'))->pluck('name')->toarray();
        return [
            $arr->id,
            $arr->name,
            $arr->status ? '正常' : '禁用',
            implode(',', array_pluck(data_get($arr, 'children'), 'name')),
            implode(',',$patrons),
            $this->sex_status[$arr->sex],
            $arr->tel,
            $arr->remark,
        ];
    }
}

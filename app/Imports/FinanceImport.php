<?php

namespace App\Imports;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;


class FinanceImport extends ExcelExporter implements WithMapping
{
    protected $fileName = '项目回款.xlsx';
    protected $check_status = [1 => '签约审核收款', 2 => '设计审核收款', 3 => '前端审核收款', 4 => '验收审核收款'];

    protected $columns = [
        'id' => 'ID',
        'project_id' => '项目名称',
        'status' => '状态',
        'staff_id' => '审核者',
        'patron_id' => '客户名称',
        'customer_id' => '商务名称',
        'pact' => '合同',
        'money' => '合同金额',
        'returned_money' => '回款金额',
        'rebate' => '返渠道费',
        'returned_bag' => '回款账户',
        'debtors' => '未结余额',
        'description' => '开票情况',
        'remark' => '备注',
    ];

    public function map($arr): array
    {
        return [
            $arr->id,
            data_get($arr, 'project.name'),
            $this->check_status[$arr->status],
            data_get($arr, 'staff.name'),
            data_get($arr, 'patron.name'),
            data_get($arr, 'customer.name'),
            $arr->pact ? '有' : '无',
            $arr->money,
            $arr->returned_money,
            $arr->rebate,
            $arr->returned_bag,
            $arr->debtors,
            $arr->description,
            $arr->remark,
        ];
    }
}

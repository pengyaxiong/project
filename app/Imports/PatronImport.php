<?php

namespace App\Imports;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

class PatronImport extends ExcelExporter implements WithMapping
{
    protected $fileName = '客户资讯.xlsx';
    protected $from_status = [0 => '线上',1 => '线下',2 => '其它'];
    protected $need_status = [0=>'APP',1=>'小程序',2=>'网站',3=>'系统软件',4=>'其它'];
    protected $status = [0=>'待签约',1=>'已签约',2=>'已审核'];

    protected $columns = [
        'id' => 'ID',
        'customer_id' => '所属商务',
        'from' => '来源',
        'company_name' => '公司名称',
        'name' => '客户名称',
        'phone' => '手机号',
        'job' => '职位',
        'need' => '需求',
        'money' => '预算',
        'status' => '状态',
        'start_time' => '预计开始时间',
        'relation' => '客户关系',
        'follow' => '跟进记录',
        'remark' => '备注',
    ];

    public function map($arr): array
    {
        return [
            $arr->id,
            data_get($arr, 'customer.name'),
            $this->from_status[$arr->from],
            $arr->company_name,
            $arr->name,
            $arr->phone,
            $arr->job,
            $this->need_status[$arr->need],
            $arr->money,
            $this->status[$arr->status],
            $arr->start_time,
            $arr->relation,
            implode(',',array_pluck($arr->follow,'content')),
            $arr->remark,
        ];
    }
}

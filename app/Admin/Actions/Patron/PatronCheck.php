<?php

namespace App\Admin\Actions\Patron;

use App\Models\Company;
use App\Models\Finance;
use App\Models\Project;
use App\Models\ProjectCustomer;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PatronCheck extends RowAction
{
    public $name = '签约审核';

    public function handle(Model $model, Request $request)
    {
        // $model ...
        if ($model->status == 0) {
            return $this->response()->error('未签单.')->refresh();
        }
        if ($model->status == 2) {
            return $this->response()->error('已审核.')->refresh();
        }
        $name = $request->get('name');
        $customer_id = $model->customer_id;
        $remark = $request->get('remark');
        $money = $request->get('money');

        $project = Project::create([
            'name' => $name,
            'remark' => $remark,
            'money' => $money,
            'contract_time' => $model->updated_at,
            'qy_rate' => $request->get('qy_rate'),
            'sj_rate' => $request->get('sj_rate'),
            'qd_rate' => $request->get('qd_rate'),
            'ys_rate' => $request->get('ys_rate'),
        ]);

        ProjectCustomer::create([
            'customer_id' => $customer_id,
            'project_id' => $project->id,
        ]);
        $model->project_id = $project->id;
        $model->status = 2;
        $model->save();

        Finance::create([
            'customer_id' => $customer_id,
            'project_id' => $project->id,
            'patron_id' => $model->id,
            'status' => 1,
            'pact' => $request->get('pact'),
            'money' => $request->get('money'),
            'returned_money' => $request->get('returned_money'),
            'rebate' => $request->get('rebate'),
            'returned_bag' => $request->get('returned_bag'),
            'debtors' => $request->get('debtors'),
            'description' => $request->get('description'),
            'remark' => $request->get('remark'),
        ]);

        return $this->response()->success('签约审核成功.')->redirect('/admin/projects');
        return $this->response()->success('签约审核成功.')->refresh();
    }

    public function form(Model $model)
    {
        $this->text('name', '项目名称')->default($model->company_name . '-' . $model->name);
        $this->text('money', '合同金额')->default($model->money);

        $this->text('qy_rate', '签约付款比列')->help('占合同总额百分比(%)')->default(40);
        $this->text('sj_rate', '设计付款比列')->help('占合同总额百分比(%)')->default(30);
        $this->text('qd_rate', '前端付款比列')->help('占合同总额百分比(%)')->default(20);
        $this->text('ys_rate', '验收付款比列')->help('占合同总额百分比(%)')->default(10);

        //汇款记录表
        $this->select('status', __('项目进度状态'))->options([1 => '签约审核成功', 2 => '设计审核成功', 3 => '前端审核成功', 4 => '验收审核成功'])->default(1)->disable();
        $this->radio('pact', __('合同（有/无）'))->options([1 => '有', 0 => '无'])->default(1);
        $this->text('returned_money', '回款金额')->default(40 * $model->money / 100);
        $this->text('rebate', '返渠道费');
        $this->text('returned_bag', '回款账户');
        $this->text('debtors', '未结余额');
        $this->textarea('description', '开票情况');
        $this->textarea('remark', '项目备注');


        $this->confirm('确认签约已成功？', '确定？', []);

        $this->modalLarge();
    }

}
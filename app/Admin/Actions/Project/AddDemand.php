<?php

namespace App\Admin\Actions\Project;

use App\Models\Demand;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
class AddDemand extends RowAction
{
    public $name = '新增需求';

    public function handle(Model $model, Request $request)
    {
        // $model ...
        Demand::create([
            'project_id' => $model->id,
            'status' => 0,
            'pact' => $request->get('pact'),
            'money' => $model->money,
            'description' => $request->get('description'),
            'remark' => $request->get('remark'),
        ]);

        $model->is_add = 1;
        $model->save();

        return $this->response()->success('新增需求成功,等待审核.')->refresh();
    }

    public function form(Model $model)
    {
        $this->text('name', '项目名称')->default($model->name)->disable();
        //新增需求
        $this->radio('pact', __('合同（有/无）'))->options([1 => '有', 0 => '无'])->default(1);
        $this->text('money', '金额');
        $this->textarea('description', '需求情况');
        $this->textarea('remark', '备注');


        $this->confirm('确认新增需求？', '确定？', []);

        $this->modalLarge();
    }
}
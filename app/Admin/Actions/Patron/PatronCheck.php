<?php

namespace App\Admin\Actions\Patron;

use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectCustomer;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
class PatronCheck extends RowAction
{
    public $name = '签约审核';

    public function handle(Model $model,Request $request)
    {
        // $model ...
        if ($model->status==0){
            return $this->response()->error('未签单.')->refresh();
        }
        if ($model->status==2){
            return $this->response()->error('已审核.')->refresh();
        }
        $company_id=$request->get('company_id');
        $name=$request->get('name');
        $customer_id=$model->customer_id;
        $remark=$request->get('remark');
        $money=$request->get('money');

        $project=Project::create([
            'company_id'=>$company_id,
            'name'=>$name,
            'remark'=>$remark,
            'money'=>$money,
            'contract_time'=>$model->updated_at,
        ]);

        ProjectCustomer::create([
            'customer_id'=>$customer_id,
            'project_id'=>$project->id,
        ]);
        $model->project_id=$project->id;
        $model->status=2;
        $model->save();

        return $this->response()->success('签约审核成功.')->redirect('/admin/projects');
        return $this->response()->success('签约审核成功.')->refresh();
    }

    public function form(Model $model)
    {
        $companies = Company::all()->toArray();
        $select_array = array_column($companies, 'name', 'id');
        $this->radio('company_id', '所属公司')->options($select_array);
        $this->text('name','名称')->default($model->company_name.'-'.$model->name);
        $this->text('money','金额')->default($model->money);
        $this->text('remark','备注')->default($model->remark);

    }

}
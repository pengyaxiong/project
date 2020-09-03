<?php

namespace App\Admin\Actions\Project;

use App\Models\Project;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\Staff;
use App\Notifications\TopicReplied;
use App\Models\Finance;
use App\Models\Patron;
class YsCheck extends RowAction
{
    public $name = '整体验收';

    public function handle(Model $model,Request $request)
    {
        // $model ...
        if ($model->check_status!=3){
            return $this->response()->error('审核状态错误')->refresh();
        }


        $patron=Patron::where('project_id',$model->id)->first();
        Finance::create([
            'staff_id' => auth('admin')->user()->id,
            'customer_id'=>$patron?$patron->customer_id:0,
            'project_id'=>$model->id,
            'patron_id'=>$patron?$patron->id:0,
            'status'=>4,
            'pact'=>$request->get('pact'),
            'money'=>$model->money,
            'returned_money'=>$request->get('returned_money'),
            'rebate'=>$request->get('rebate'),
            'returned_bag'=>$request->get('returned_bag'),
            'debtors'=>$request->get('debtors'),
            'description'=>$request->get('description'),
            'remark'=>$request->get('remark'),
        ]);


        $model->check_status=4;
        $model->save();

        $project=Project::find($model->id);
        activity()->inLog(8)
            ->performedOn($project)
            ->causedBy(auth('admin')->user())
            ->withProperties([])
            ->log('更新'.$project->name.'状态为：整体验收成功');

        $lastLoggedActivity = Activity::all()->last();

        $staffs = Staff::where('admin_id', 1)->get();
        //执行消息分发
        dispatch(new \App\Jobs\SendNotice($staffs, new TopicReplied($lastLoggedActivity), 5));

        return $this->response()->success('整体验收成功.')->refresh();

    }

    /**
     * @return string
     */
    public function href()
    {
        return "/admin/projects/ys/".$this->getKey();
    }

//    public function form(Model $model)
//    {
//        $this->text('name','项目名称')->default($model->name)->disable();
//        $this->text('money','合同金额')->default($model->money)->disable();
//        $this->text('qy_rate','签约付款比列')->help('占合同总额百分比(%)')->default($model->qy_rate)->disable();
//        $this->text('sj_rate','设计付款比列')->help('占合同总额百分比(%)')->default($model->sj_rate)->disable();
//        $this->text('qd_rate','前端付款比列')->help('占合同总额百分比(%)')->default($model->qd_rate)->disable();
//        $this->text('ys_rate','验收付款比列')->help('占合同总额百分比(%)')->default($model->ys_rate)->disable();
//
//        //汇款记录表
//        $this->select('status', __('项目进度状态'))->options([1 => '签约审核成功', 2 => '设计审核成功', 3 => '前端审核成功', 4 => '验收审核成功'])->default(1)->disable();
//        $this->radio('pact', __('合同（有/无）'))->options([1 => '有', 0=> '无'])->default(1);
//        $this->text('returned_money','回款金额')->default($model->ys_rate*$model->money/100);
//        $this->text('rebate','返渠道费');
//        $this->text('returned_bag','回款账户');
//        $this->text('debtors','未结余额')->default(0);
//        $this->textarea('description','开票情况');
//        $this->textarea('remark','项目备注');
//
//
//        $this->confirm('确认验收已完成？','确定？',[]);
//
//        $this->modalLarge();
//    }

}
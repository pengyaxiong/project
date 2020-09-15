<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Button\FinanceStatistics;
use App\Imports\FinanceImport;
use App\Models\Customer;
use App\Models\Finance;
use App\Models\Patron;
use App\Models\Project;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FinanceController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '项目回款';
    protected $check_status = [];

    public function __construct()
    {
        $this->check_status = [1 => '签约审核收款', 2 => '设计审核收款', 3 => '前端审核收款', 4 => '验收审核收款'];
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Finance());

//        $grid->selector(function (Grid\Tools\Selector $selector) {
//            $selector->selectOne('created_at', '回款金额', ['月度回款', '季度回款', '年度回款'], function ($query, $value) {
//                $month_start=date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),1,date("Y")));
//                $month_end=date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("t"),date("Y")));
//                $season = ceil((date('n'))/3);//当月是第几季度
//                $jidu_start=date('Y-m-d H:i:s', mktime(0, 0, 0,$season*3-3+1,1,date('Y')));
//                $jidu_end=date('Y-m-d H:i:s', mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date("Y"))),date('Y')));
//                $year_start=date('Y-m-d H:i:s',strtotime(date("Y",time())."-1"."-1"));
//                $year_end=date('Y-m-d H:i:s',strtotime(date("Y",time())."-12"."-31"));
//                $between = [
//                    [$month_start, $month_end],
//                    [$jidu_start, $jidu_end],
//                    [$year_start, $year_end]
//                ];
//                $query->whereBetween('created_at', $between[$value]);
//            });
//        });


        $auth = auth('admin')->user();
        $slug = $auth->roles->pluck('slug')->toarray();

        $grid->column('id', __('Id'));
        $grid->column('project.name', __('项目名称'))->limit(10);
        $grid->column('status', __('Status'))->using($this->check_status)->label([
            1 => 'default',
            2 => 'info',
            3 => 'primary',
            4 => 'success',
        ]);
        $grid->column('staff.name', __('审核者'))->display(function (){
            if ($this->staff_id==1){
                return '超级管理员';
            }else{
                return $this->staff->name;
            }
        });
        $grid->column('patron.name', __('客户名称'));
        $grid->column('customer.name', __('商务名称'));
        $grid->column('pact', __('合同（有/无）'))->bool();
        $grid->column('money', __('合同金额'));
        $grid->column('returned_money', __('回款金额'));
        $grid->column('rebate', __('返渠道费'));
        $grid->column('returned_bag', __('回款账户'));
        $grid->column('debtors', __('未结余额'));
        $grid->column('description', __('开票情况'))->limit(10);
        $grid->column('remark', __('Remark'))->limit(10);
        $grid->column('created_at', __('Created at'))->display(function ($model){
            if ($model){
                return date('Y-m-d',strtotime($model));
            }
        });
        $grid->column('updated_at', __('Updated at'))->display(function ($model){
            if ($model){
                return date('Y-m-d',strtotime($model));
            }
        });

        $grid->exporter(new FinanceImport());

        $grid->filter(function ($filter) {

            $projects = Project::all()->toArray();
            $project_array = array_column($projects, 'name', 'id');
            //创建select
            $filter->equal('project_id', '项目名称')->select($project_array);

            $patrons = Patron::all()->toArray();
            $patron_array = array_column($patrons, 'name', 'id');
            //创建select
            $filter->equal('patron_id', '客户名称')->select($patron_array);

            $customers = Customer::all()->toArray();
            $customer_array = array_column($customers, 'name', 'id');
            //创建select
            $filter->equal('customer_id', '商务名称')->select($customer_array);

            $filter->equal('status',__('Status'))->select($this->check_status);

            $filter->between('created_at', __('Created at'))->date();

            $status_text = [1 => '有', 0 => '无'];
            $filter->equal('pact', __('合同（有/无）'))->select($status_text);
        });

        $grid->actions(function ($actions) use ($auth,$slug) {
            if (!in_array($auth->id,[1,2]) && !in_array('apply', $slug)) {
                $actions->disableView();
                //  $actions->disableEdit();
                $actions->disableDelete();
            }
        });

        $grid->tools(function ($tools) use ($auth,$slug) {

            if (in_array($auth->id,[1,2])){
                $tools->append(new FinanceStatistics());
            }
            if (!in_array($auth->id,[1,2]) && !in_array('apply', $slug)) {
                // 禁用批量删除按钮
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            }
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Finance::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('project.name', __('项目名称'));
        $show->field('patron.name', __('客户名称'));
        $show->field('customer.name', __('商务名称'));
        $show->field('pact', __('合同（有/无）'));
        $show->field('money', __('合同金额'));
        $show->field('returned_money', __('回款金额'));
        $show->field('rebate', __('返渠道费'));
        $show->field('returned_bag', __('回款账户'));
        $show->field('debtors', __('未结余额'));
        $show->field('description', __('开票情况'));
        $show->field('remark', __('Remark'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Finance());

        $projects = Project::all()->toArray();
        $project_array = array_column($projects, 'name', 'id');
        //创建select
        $form->select('project_id', '项目名称')->options($project_array);

        $patrons = Patron::all()->toArray();
        $patron_array = array_column($patrons, 'name', 'id');
        //创建select
        $form->select('patron_id', '客户名称')->options($patron_array);

        $customers = Customer::all()->toArray();
        $customer_array = array_column($customers, 'name', 'id');
        //创建select
        $form->select('customer_id', '商务名称')->options($customer_array);


        $form->radio('pact', __('合同（有/无）'))->options([1 => '有', 0 => '无'])->default(1);
        $form->decimal('money', __('合同金额'))->default(1000.00);
        $form->decimal('returned_money', __('回款金额'))->default(0.00);
        $form->decimal('rebate', __('返渠道费'))->default(0.00);
        $form->text('returned_bag', __('回款账户'));
        $form->decimal('debtors', __('未结余额'))->default(0.00);
        $form->date('created_at', __('Created at'));
        $form->textarea('description', __('开票情况'));
        $form->textarea('remark', __('Remark'));

        return $form;
    }
}

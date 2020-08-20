<?php

namespace App\Admin\Controllers;

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

        $auth = auth('admin')->user();

        $grid->column('id', __('Id'));
        $grid->column('project.name', __('项目名称'));
        $grid->column('status', __('Status'))->using($this->check_status)->label([
            1 => 'default',
            2 => 'info',
            3 => 'primary',
            4 => 'success',
        ]);
        $grid->column('patron.name', __('客户名称'));
        $grid->column('customer.name', __('商务名称'));
        $grid->column('pact', __('合同（有/无）'))->bool();
        $grid->column('money', __('合同金额'));
        $grid->column('returned_money', __('回款金额'));
        $grid->column('rebate', __('返渠道费'));
        $grid->column('returned_bag', __('回款账户'));
        $grid->column('debtors', __('未结余额'));
        $grid->column('description', __('开票情况'));
        $grid->column('remark', __('Remark'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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

            $filter->between('created_at', __('Created at'))->date();

            $status_text = [1 => '有', 0 => '无'];
            $filter->equal('pact', __('合同（有/无）'))->select($status_text);
        });

        $grid->actions(function ($actions) use ($auth) {
            if ($auth->id > 1) {
                $actions->disableView();
                //  $actions->disableEdit();
                $actions->disableDelete();
            }
        });

        $grid->tools(function ($tools) use ($auth) {
            if ($auth->id > 1) {
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
        $form->textarea('description', __('开票情况'));
        $form->textarea('remark', __('Remark'));

        return $form;
    }
}

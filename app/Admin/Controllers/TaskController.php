<?php

namespace App\Admin\Controllers;

use App\Models\Company;
use App\Models\Project;
use App\Models\Staff;
use App\Models\Task;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TaskController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '任务管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Task());

        $grid->column('id', __('Id'));
        $grid->column('company.name', __('所属公司'));
        $grid->column('type', __('类型'));
        $grid->column('name', __('Name'));
        $grid->column('principal.name', __('负责人'));
        $grid->column('access.name', __('对接人'));
        $grid->column('remark', __('Remark'));
        $grid->column('days', __('时间周期(天)'));
        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');
        $states = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $grid->column('is_contract', __('是否签约'))->switch($states);
        $grid->column('start_time', __('开始时间'));
        $grid->column('contract_time', __('Contract time'));
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();

        $grid->filter(function ($filter) {
            $filter->like('name', __('Name'));
            $filter->between('start_time', __('开始时间'))->date();
            $status_text = [
                1 => '签约',
                0 => '未签约'
            ];
            $filter->equal('is_contract', __('是否签约'))->select($status_text);

            $staffs = Staff::all()->toArray();
            $staffs_array = array_column($staffs, 'name', 'id');
            $filter->equal('principal_id', __('负责人'))->select($staffs_array);
            $filter->equal('access_id', __('对接人'))->select($staffs_array);

            $companies = Company::all()->toArray();
            $select_array = array_column($companies, 'name', 'id');
            $filter->equal('company_id', __('所属公司'))->select($select_array);
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
        $show = new Show(Task::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('company_id', __('Company id'));
        $show->field('type', __('Type'));
        $show->field('name', __('Name'));
        $show->field('principal_id', __('Principal id'));
        $show->field('access_id', __('Access id'));
        $show->field('remark', __('Remark'));
        $show->field('days', __('Days'));
        $show->field('sort_order', __('Sort order'));
        $show->field('is_contract', __('Is contract'));
        $show->field('start_time', __('Start time'));
        $show->field('contract_time', __('Contract time'));
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
        $form = new Form(new Task());

        $companies = Company::all()->toArray();
        $select_array = array_column($companies, 'name', 'id');
        //创建select
        $form->select('company_id', '所属公司')->options($select_array);

        $staffs = Staff::all()->toArray();
        $staffs_array = array_column($staffs, 'name', 'id');

        $form->text('type', __('Type'))->help('方案&案例设计&设计图&功能清单');
        $form->text('name', __('Name'));
        $form->select('principal_id', __('负责人'))->options($staffs_array);
        $form->select('access_id', __('对接人'))->options($staffs_array);
        $form->textarea('remark', __('Remark'));
        $form->decimal('days', __('周期'))->default(0.00)->help('单位（天）');

        $states = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $form->switch('is_contract', __('是否签约'))->states($states)->default(0);
        $form->datetime('start_time', __('开始时间'))->default(date('Y-m-d H:i:s'));
        $form->datetime('contract_time', __('Contract time'))->default(null);
        $form->number('sort_order', __('Sort order'))->default(99);

        //保存后回调
        $form->saved(function (Form $form) {
            $is_contract = $form->model()->is_contract;
            if (!$is_contract) {
                $form->contract_time=date('Y-m-d H:i:s',time());

                Project::create([
                    'company_id'=>$form->model()->company_id,
                    'name'=>$form->model()->name,
                    'contract_time'=>date('Y-m-d H:i:s',time())
                ]);
            }
        });
        return $form;
    }
}

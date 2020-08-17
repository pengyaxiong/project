<?php

namespace App\Admin\Controllers;

use App\Models\Audition;
use App\Models\Company;
use App\Models\Department;
use App\Models\Staff;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AuditionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '面试邀约';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Audition());

        $grid->column('id', __('Id'));
        $grid->column('company.name', __('所属公司'));
        $grid->column('department.name', __('所属部门'));
        $grid->column('staff.name', __('面试官'));
        $grid->column('name', __('Name'));
        $grid->column('job', __('Job'));
        $grid->column('point', __('分数'));
        $grid->column('phone', __('Phone'));
        $grid->column('money', __('期望薪资'))->sortable()->editable();
        $states = [
            'on' => ['value' => 1, 'text' => '通过', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '拒绝', 'color' => 'danger'],
        ];
        $grid->column('status', __('Status'))->switch($states);
        $grid->column('remark', __('Remark'));
        $grid->column('start_time', __('面试时间'));
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();


        $grid->filter(function ($filter) {
            $filter->like('name', __('Name'));
            $filter->like('phone', __('Phone'));
            $filter->between('start_time', __('面试时间'))->date();
            $status_text = [
                1 => '通过',
                0 => '拒绝'
            ];
            $filter->equal('status', __('Status'))->select($status_text);
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
        $show = new Show(Audition::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('company_id', __('所属公司'));
        $show->field('department_id', __('所属部门'));
        $show->field('staff_id', __('面试官'));
        $show->field('name', __('Name'));
        $show->field('job', __('Job'));
        $show->field('point', __('分数'));
        $show->field('phone', __('Phone'));
        $show->field('money', __('期望薪资'));
        $show->field('status', __('Status'));
        $show->field('remark', __('Remark'));
        $show->field('start_time', __('面试时间'));
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
        $form = new Form(new Audition());

        $companies = Company::all()->toArray();
        $company_array = array_column($companies, 'name', 'id');
        //创建select
        $form->select('company_id', '所属公司')->options($company_array);

        $departments=Department::all()->toArray();
        $department_array=array_column($departments, 'name', 'id');

        $form->select('department_id', __('所属部门'))->options($department_array);

        $staffs=Staff::all()->toArray();
        $staff_array=array_column($staffs, 'name', 'id');

        $form->select('staff_id', __('面试官'))->options($staff_array);

        $form->text('name', __('Name'));
        $form->text('job', __('Job'));
        $form->number('point', __('分数'))->default(100);
        $form->text('phone', __('Phone'));
        $form->decimal('money', __('期望薪资'))->default(1000.00);
        $states = [
            'on' => ['value' => 1, 'text' => '通过', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '拒绝', 'color' => 'danger'],
        ];
        $form->switch('status', __('Status'))->states($states)->default(0);
        $form->textarea('remark', __('Remark'));
        $form->datetime('start_time', __('面试时间'))->default(date('Y-m-d H:i:s'));

        return $form;
    }


}

<?php

namespace App\Admin\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\Staff;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StaffController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '员工管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Staff());

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('company.name', __('所属公司'));
        $grid->column('department.name', __('所属部门'));
        $grid->column('mobile', __('Mobile'));
        $grid->column('sex', __('Sex'))->using([
            1 => '男',
            2 => '女',
            0 => '保密',
        ], '保密')->dot([
            1 => 'primary',
            2 => 'danger',
            0 => 'success',
        ], 'warning');
        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');
        $grid->column('remark', __('Remark'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        $grid->filter(function ($filter) {
            $filter->like('name', __('Name'));
            $filter->like('mobile', __('Mobile'));
            $status_text = [
                1 => '男',
                2 => '女',
                0 => '保密'
            ];
            $filter->equal('sex', __('Sex'))->select($status_text);

            $companies = Company::all()->toArray();
            $select_array = array_column($companies, 'name', 'id');
            $filter->equal('company_id', __('所属公司'))->select($select_array);

            $departments = Department::all()->toArray();
            $select_array = array_column($departments, 'name', 'id');
            $filter->equal('department_id', __('所属部门'))->select($select_array);
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
        $show = new Show(Staff::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('company.name', __('所属公司'));
        $show->field('department_id', __('所属部门'));
        $show->field('mobile', __('Mobile'));
        $show->field('sex', __('Sex'));
        $show->field('sort_order', __('Sort order'));
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
        $form = new Form(new Staff());

        $form->text('name', __('Name'))->rules('required');
        $form->mobile('mobile', __('Mobile'));
        $companies = Company::all()->toArray();
        $select_array = array_column($companies, 'name', 'id');
        //创建select
        $form->select('company_id', '所属公司')->options($select_array);

        $departments = Department::all()->toArray();
        $select_array = array_column($departments, 'name', 'id');
        //创建select
        $form->select('department_id', '所属部门')->options($select_array);
        //创建select
        $form->select('sex', __('Sex'))->options([1=>'男',2=>'女',0=>'保密']);
        $form->number('sort_order', __('Sort order'))->default(99);
        $form->textarea('remark', __('Remark'));

        return $form;
    }
}

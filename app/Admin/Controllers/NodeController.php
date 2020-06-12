<?php

namespace App\Admin\Controllers;

use App\Models\Department;
use App\Models\Node;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class NodeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '节点管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Node());

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'))->editable();
        $grid->column('department.name', __('所属部门'));
        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');

        $grid->filter(function ($filter) {
            $filter->like('name', __('Name'));

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
        $show = new Show(Node::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('department_id', __('所属部门'));
        $show->field('sort_order', __('Sort order'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Node());

        $form->text('name', __('Name'))->rules('required');

        $departments = Department::all()->toArray();
        $select_array = array_column($departments, 'name', 'id');
        //创建select
        $form->select('department_id', '所属部门')->options($select_array);

        $form->number('sort_order', __('Sort order'))->default(99);

        return $form;
    }
}

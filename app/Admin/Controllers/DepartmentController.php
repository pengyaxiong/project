<?php

namespace App\Admin\Controllers;

use App\Models\Department;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

class DepartmentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '部门管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Department());

        $grid->model()->orderby('sort_order');


        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'))->editable();
        $grid->column('', __('部门节点'))->display(function (){
            return '查看';
        })->expand(function ($model) {
            $nodes = $model->nodes()->get()->map(function ($node) {
                return $node->only(['id', 'name']);
            });
            return new Table(['ID', '名称'], $nodes->toArray());
        });
        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');


        #禁用行选择checkbox
        $grid->disableRowSelector();

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
        $show = new Show(Department::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
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
        $form = new Form(new Department());

        $form->text('name', __('Name'));
        $form->number('sort_order', __('Sort order'))->default(99);

        return $form;
    }
}

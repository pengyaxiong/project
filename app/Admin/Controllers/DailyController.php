<?php

namespace App\Admin\Controllers;

use App\Models\Daily;
use App\Models\Department;
use App\Models\Staff;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Carbon\Carbon;

class DailyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '日志管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Daily());
        $auth = auth('admin')->user();

        $grid->header(function ($query) {
            $dailies = $query->wherebetween('created_at', [Carbon::today(), Carbon::tomorrow()])->get()->pluck('staff_id')->toArray();
            $staffs = Staff::wherenotin('id', $dailies)->pluck('name')->toArray();
            return '<span class="label" style="font-weight:unset; color: #fff; background-color: #d9534f">今日未完成人员:</span>&nbsp;' . implode('--', $staffs);
        });

        if ($auth->id > 1) {
            $staff_id = Staff::where('admin_id', $auth->id)->first()->id;
            $grid->model()->where('staff_id', $staff_id);
        }

        $grid->model()->with('staff.department');

        $grid->column('id', __('Id'));
        $grid->column('staff.name', __('Name'));
        $grid->column('staff.department.name', __('所属部门'))->display(function ($model) {
            return $model['name'];
        });
        $grid->column('staff.mobile', __('Mobile'));
        $grid->column('staff.sex', __('Sex'))->using([
            1 => '男',
            2 => '女',
            0 => '保密',
        ], '保密')->dot([
            1 => 'primary',
            2 => 'danger',
            0 => 'success',
        ], 'warning');
        $grid->column('work', __('今日工作内容'));
        $grid->column('problem', __('待处理问题'));
        $grid->column('done', __('完成情况'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        $grid->filter(function ($filter) {
            $filter->like('staff.name', __('Name'));
            $filter->like('staff.mobile', __('Mobile'));
            $status_text = [
                1 => '男',
                2 => '女',
                0 => '保密'
            ];
            $filter->equal('staff.sex', __('Sex'))->select($status_text);

            $departments = Department::all()->toArray();
            $select_array = array_column($departments, 'name', 'id');
            $filter->equal('staff.department.id', __('所属部门'))->select($select_array);
        });

        if ($auth->id > 1) {
            #禁用创建按钮
            //$grid->disableCreateButton();
            #禁用导出数据按钮
            $grid->disableExport();
            #禁用行选择checkbox
            $grid->disableRowSelector();
        }

        $grid->tools(function ($tools) use ($auth) {
            if ($auth->id > 1) {
                // 禁用批量删除按钮
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            }
        });

        $grid->actions(function ($actions) use ($auth) {
            if ($auth->id > 1) {
                $actions->disableDelete();
                $actions->disableView();
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
        $show = new Show(Daily::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('staff.name', __('Name'));
        $show->field('work', __('今日工作内容'));
        $show->field('problem', __('待处理问题'));
        $show->field('done', __('完成情况'));
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
        $form = new Form(new Daily());
        $auth = auth('admin')->user();
        if ($auth->id == 1) {
            $staffs = Staff::orderby('sort_order')->pluck('name', 'id')->toArray();
        } else {
            $staffs = Staff::where('admin_id', $auth->id)->pluck('name', 'id')->toArray();
        }
        //创建select
        $form->select('staff_id', __('Name'))->options($staffs);

        $form->textarea('work', __('今日工作内容'));
        $form->textarea('problem', __('待处理问题'));
        $form->textarea('done', __('完成情况'));

        return $form;
    }
}

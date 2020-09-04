<?php

namespace App\Admin\Controllers;

use App\Models\Department;
use App\Models\Node;
use App\Models\Project;
use App\Models\ProjectNode;
use App\Models\Staff;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

class StaffController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '技术管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Staff());

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'))->display(function ($name) {
            return $name;
        })->expand(function ($staff) {

            $project_nodes = ProjectNode::where('staff_id', $staff->id)->orderby('project_id')->get()->map(function ($model) {
                $staff = Staff::find($model->staff_id);
                $node = Node::find($model->node_id);
                $staff_name = isset($staff) ? $staff->name : '';
                $node_name = isset($node) ? $node->name : '';

                $project = Project::find($model->project_id);

                $node_status = $model->status;
                $status = '';
                if ($node_status == 2) {
                    $status = '<span class="label" style="font-weight:unset; color: #444; background-color: #DFFA99"><i class="fa fa-clock-o"></i>&nbsp;进行中</span>';
                } else if ($node_status == 3) {
                    $status = '<span class="label" style="font-weight:unset; color: #444; background-color: #87FAC1"><i class="fa fa-check-circle-o"></i>&nbsp;已完成</span>';
                } else {
                    $status = '<span class="label" style="font-weight:unset; color: #444; background-color: #FAC0D6"><i class="fa fa-frown-o"></i>&nbsp;未开始</span>';
                }
                $project_status = '';
                $pstatus = isset($project) ? $project->status : '';

                if ($pstatus == 2) {
                    $project_status = '<span class="label" style="font-weight:unset; color: #444; background-color: #8EFFB9"><i class="fa fa-paper-plane-o"></i>&nbsp;进行中</span>';
                } elseif ($pstatus == 3) {
                    $project_status = '<span class="label" style="font-weight:unset; color: #444; background-color: #FFA3BE"><i class="fa fa-pause-circle"></i>&nbsp;已暂停</span>';
                } elseif ($pstatus == 4) {
                    $project_status = '<span class="label" style="font-weight:unset; color: #444; background-color: #d2d6de"><i class="fa fa-power-off"></i>&nbsp;已结项</span>';
                } elseif ($pstatus == 1) {
                    $project_status = '<span class="label" style="font-weight:unset; color: #444; background-color: #AEDAFF"><i class="fa fa-plus-circle"></i>&nbsp;已立项</span>';
                }else{
                    $project_status ='';
                }

                $nodes = [
                    'project_id' => isset($project) ?$project->id : '',
                    'project_name' => isset($project) ?'<a target="_blank" href="/admin/projects/' . $project->id . '/edit">' . $project->name . '</a>': '',
                    'project_status' => $project_status,
                    'node_name' => $node_name,
                    'node_status' => $status,
                    'staff_name' => $staff_name,
                    'start_time' => $model->start_time,
                    'end_time' => $model->end_time,
                    'days' => $model->days,
                    'content' => $model->content,
                ];
                return $nodes;
            });

            return new Table(['项目ID', '项目名称', '项目状态', '节点名称', '节点状态', '负责人', '开始时间', '结束时间', '耗时(天)', '详情'], $project_nodes->toArray());
        });
        $states = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $grid->column('is_notice', __('是否接收项目动态'))->switch($states);

        $grid->column('admin.name', __('管理员'));
        $grid->column('department.name', __('所属部门'));
        $grid->column('mobile', __('Mobile'));
        $grid->column('email', __('Email'))->hide();
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
        $show->field('admin.name', __('管理员'));
        $show->field('department_id', __('所属部门'));
        $show->field('mobile', __('Mobile'));
        $show->field('email', __('Email'));
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
        $form->email('email', __('Email'));

        $states = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $form->switch('is_notice', __('是否接收项目动态'))->states($states)->default(0);

        $class = config('admin.database.users_model');
        $admin=new $class();
        $admins = $admin::all()->toArray();
        $select_array = array_column($admins, 'name', 'id');
        //创建select
        $form->select('admin_id', '管理员')->options($select_array);

        $departments = Department::all()->toArray();
        $select_array = array_column($departments, 'name', 'id');
        //创建select
        $form->select('department_id', '所属部门')->options($select_array);
        //创建select
        $form->select('sex', __('Sex'))->options([1 => '男', 2 => '女', 0 => '保密']);
        $form->number('sort_order', __('Sort order'))->default(99);
        $form->textarea('remark', __('Remark'));

        return $form;
    }
}

<?php

namespace App\Admin\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Node;
use App\Models\Project;
use App\Models\ProjectCustomer;
use App\Models\ProjectNode;
use App\Models\ProjectStaff;
use App\Models\Staff;
use App\Models\Task;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProjectController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '项目管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Project());

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('company.name', __('所属公司'));
        $grid->column('task.name', __('任务名称'));
        $grid->column('node', __('Node'))->hide();
        $grid->column('content', __('Content'))->hide();
        // 不存在的`full_name`字段
        $grid->column('customer_name', '甲方人员')->display(function () {
            $customer_ids = ProjectCustomer::where('project_id', $this->id)->pluck('customer_id')->toArray();
            $customer_name=Customer::wherein('id',$customer_ids)->pluck('name')->toArray();
            return $customer_name;
        })->label();
        $grid->column('staff_name', '项目人员')->display(function () {
            $staff_ids = ProjectStaff::where('project_id', $this->id)->pluck('staff_id')->toArray();
            $staff_name=Staff::wherein('id',$staff_ids)->pluck('name')->toArray();
            return $staff_name;
        })->label();

        $grid->column('remark', __('Remark'));
        $grid->column('money', __('Money'))->editable();
        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');
        $states = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $grid->column('is_check', __('Is check'))->switch($states);

        $grid->column('contract_time', __('Contract time'))->editable('datetime');
        $grid->column('check_time', __('Check time'));
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();


        $grid->filter(function ($filter) {
            $filter->like('name', __('Name'));
            $filter->between('contract_time', __('Contract time'))->date();
            $status_text = [
                1 => '审核',
                0 => '未审核'
            ];
            $filter->equal('is_check', __('Is check'))->select($status_text);

            $companies = Company::all()->toArray();
            $select_array = array_column($companies, 'name', 'id');
            $filter->equal('company_id', __('所属公司'))->select($select_array);


            $filter->where(function ($query) {

                $query->whereHas('customers', function ($query) {
                    $query->where('name', 'like', "%{$this->input}%");
                });

            }, '甲方人员');

            $filter->where(function ($query) {

                $query->whereHas('staffs', function ($query) {
                    $query->where('name', 'like', "%{$this->input}%");
                });

            }, '项目人员');

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
        $show = new Show(Project::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('company.name', __('所属公司'));
        $show->field('task.name', __('任务名称'));
        $show->field('node', __('Node'))->as(function ($node) {
            return json_encode($node);
        });
        $show->field('content', __('Content'));
        $show->field('remark', __('Remark'));
        $show->field('money', __('Money'));
        $show->field('sort_order', __('Sort order'));
        $show->field('is_check', __('Is check'));
        $show->field('contract_time', __('Contract time'));
        $show->field('check_time', __('Check time'));
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
        $form = new Form(new Project());

        $form->text('name', __('Name'))->rules('required');

        $companies = Company::all()->toArray();
        $select_array = array_column($companies, 'name', 'id');
        //创建select
        $form->select('company_id', '所属公司')->options($select_array);

        $tasks = Task::all()->toArray();
        $select_ = array_prepend($tasks, ['id' => 0, 'name' => '其它']);
        $select_task = array_column($select_, 'name', 'id');

        //创建select
        $form->select('task_id', '任务名称')->options($select_task);

        $staffs = Staff::orderby('sort_order')->pluck('name', 'id')->toArray();
        $customers = Customer::orderby('sort_order')->pluck('name', 'id')->toArray();
        if ($form->isEditing()) {
            $id = request()->route()->parameters()['project'];
            $customer_ids = ProjectCustomer::where('project_id', $id)->pluck('customer_id')->toArray();
            $staff_ids = ProjectStaff::where('project_id', $id)->pluck('staff_id')->toArray();

            $form->multipleSelect('customers', __('甲方人员'))
                ->options($customers)->default($customer_ids);
            $form->multipleSelect('staffs', __('项目人员'))
                ->options($staffs)->default($staff_ids);
        } else {
            $form->multipleSelect('customers', __('甲方人员'))
                ->options($customers);
            $form->multipleSelect('staffs', __('项目人员'))
                ->options($staffs);
        }

        $form->ueditor('content', __('Content'));
        $form->textarea('remark', __('Remark'));

        $form->table('node', __('节点情况'), function ($table) {
            $staffs=Staff::all()->toArray();
            $select_staff = array_column($staffs, 'name', 'id');
            $table->select('staff_id', '负责人')->options($select_staff);

            $nodes=Node::where('is_project',true)->get()->toArray();
            $select_node = array_column($nodes, 'name', 'id');
            $table->select('node_id', '节点')->options($select_node);

            $table->datetime('start_time', '开始时间')->default(date('Y-m-d',time()));
            $table->datetime('end_time', '结束时间')->default(date('Y-m-d',time()));
            $table->number('days', '耗时')->help('天');
            $table->textarea('content', '详情');
        });

        $form->decimal('money', __('Money'))->default(0.00);
        $form->number('sort_order', __('Sort order'))->default(99);
        $states = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $form->switch('is_check', __('Is check'))->states($states)->default(0);
        $form->datetime('contract_time', __('Contract time'));
        $form->datetime('check_time', __('Check time'));

        //保存前回调
        $form->saving(function (Form $form) {
            $is_check = $form->model()->is_check;
            if (!$is_check) {
                $form->check_time=date('Y-m-d H:i:s',time());
            }else{
                $form->check_time=null;
            }
        });

        //保存后回调
        $form->saved(function (Form $form) {
            $id = $form->model()->id;
            $node = array_filter(\Request('node'));
//           dump($node);
//           exit();
            if (!empty($node)) {
                ProjectNode::where('project_id',$id)->delete();
                foreach ($node as $value) {
                    ProjectNode::create([
                        'staff_id' => $value['staff_id'],
                        'node_id' => $value['node_id'],
                        'project_id' => $id,
                        'start_time' => $value['start_time'],
                        'end_time' => $value['end_time'],
                        'days' => $value['days'],
                        'content' => $value['content'],
                    ]);
                }

            }
        });

        return $form;
    }
}

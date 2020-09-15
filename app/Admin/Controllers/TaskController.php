<?php

namespace App\Admin\Controllers;

use App\Imports\TaskImport;
use App\Models\Customer;
use App\Models\Node;
use App\Models\Project;
use App\Models\Staff;
use App\Models\Task;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Illuminate\Support\Facades\DB;

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

        $grid->model()->orderBy('sort_order')->orderBy('created_at', 'desc');
        $auth = auth('admin')->user();
        if (!in_array($auth->id,[1,2])) {
            $staff_id = Staff::where('admin_id', $auth->id)->first()->id;
            $grid->model()->where('staff_id', $staff_id);
        }

        $grid->column('id', __('Id'));
        $grid->column('node.name', __('类型'));
        $grid->column('name', __('Name'))->limit(10);
        $grid->column('staff.name', __('负责人'));
        $grid->column('customer.name', __('对接人'));
        $grid->column('remark', __('Remark'))->limit(10);
        $grid->column('days', __('时间周期(天)'));
        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');
        $states = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        if (!in_array($auth->id,[1,2])) {
            $grid->column('is_contract', __('是否签约'))->bool();
        } else {
            $grid->column('is_contract', __('是否签约'))->switch($states);
        }
        $grid->column('is_finish', __('是否完成'))->switch($states);
        $grid->column('start_time', __('开始时间'));
        $grid->column('contract_time', __('Contract time'));
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();

        $grid->filter(function ($filter) {
            $filter->like('name', __('Name'));
            $filter->between('start_time', __('开始时间'))->datetime();
            $status_text = [
                1 => '签约',
                0 => '未签约'
            ];
            $filter->equal('is_contract', __('是否签约'))->select($status_text);

            $staffs = Staff::all()->toArray();
            $staffs_array = array_column($staffs, 'name', 'id');
            $filter->equal('staff_id', __('负责人'))->select($staffs_array);

            $customers = Customer::all()->toArray();
            $customers_array = array_column($customers, 'name', 'id');
            $filter->equal('customer_id', __('对接人'))->select($customers_array);

        });

        $grid->header(
            function ($query) {

                return new Box('周期比列', view('admin.task_days'));

            }
        );

        $grid->footer(function ($query) {
            $days = $query->sum('days');
            return "<div style='padding: 5px;'>总时长 ： $days 天</div>";
        });


        $grid->exporter(new TaskImport());

        if (!in_array($auth->id,[1,2])) {
            #禁用创建按钮
            $grid->disableCreateButton();
            #禁用导出数据按钮
            $grid->disableExport();
            #禁用行选择checkbox
            $grid->disableRowSelector();
        }
        $grid->tools(function ($tools) use ($auth) {
            if (!in_array($auth->id,[1,2])) {
                // 禁用批量删除按钮
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            }
        });

        $grid->actions(function ($actions) use ($auth) {
            if (!in_array($auth->id,[1,2])) {
                $actions->disableDelete();
                $actions->disableView();
                $actions->disableEdit();
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
        $show = new Show(Task::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('node.name', __('类型'));
        $show->field('name', __('Name'));
        $show->field('staff_id', __('Principal id'));
        $show->field('customer_id', __('Access id'));
        $show->field('remark', __('Remark'));
        $show->field('days', __('Days'));
        $show->field('sort_order', __('Sort order'));
        $show->field('is_contract', __('Is contract'));
        $show->field('is_finish', __('Is finish'));
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

        $nodes = Node::where('is_task', true)->get()->toArray();
        $select_node = array_column($nodes, 'name', 'id');
        //创建select
        $form->select('node_id', '类型')->options($select_node);


        $staffs = Staff::all()->toArray();
        $staffs_array = array_column($staffs, 'name', 'id');

        $customers = Customer::all()->toArray();
        $customers_array = array_column($customers, 'name', 'id');

        $form->text('name', __('Name'));
        $form->select('staff_id', __('负责人'))->options($staffs_array);
        $form->select('customer_id', __('对接人'))->options($customers_array);
        $form->textarea('remark', __('Remark'));
        $form->decimal('days', __('周期'))->default(0.00)->help('单位（天）');

        $states = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $form->switch('is_contract', __('是否签约'))->states($states)->default(0);
        $form->switch('is_finish', __('是否完成'))->states($states)->default(0);
        $form->datetime('start_time', __('开始时间'))->default(date('Y-m-d H:i:s'));
        $form->datetime('contract_time', __('Contract time'))->default(null);
        $form->number('sort_order', __('Sort order'))->default(99);

        //保存后回调
        $form->saved(function (Form $form) {
            $id = $form->model()->id;
            $task = Task::find($id);
            if ($task->is_contract == 1) {
                $contract_time = $task->contract_time ? $task->contract_time : date('Y-m-d H:i:s', time());
                $project = Project::where('task_id', $id)->first();
                if (!$project) {
                    Project::create([
                        'task_id' => $form->model()->id,
                        'name' => $form->model()->name,
                        'contract_time' => $contract_time
                    ]);
                }
            }
        });
        return $form;
    }



}

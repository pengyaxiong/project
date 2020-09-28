<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Project\AddDemand;
use App\Admin\Actions\Project\Calendar;
use App\Admin\Actions\Project\QdCheck;
use App\Admin\Actions\Project\SjCheck;
use App\Admin\Actions\Project\YsCheck;
use App\Imports\ProjectImport;
use App\Models\Customer;
use App\Models\Demand;
use App\Models\DesignCheck;
use App\Models\Finance;
use App\Models\HtmlCheck;
use App\Models\Node;
use App\Models\Patron;
use App\Models\Project;
use App\Models\ProjectNode;
use App\Models\ProjectNodeInfo;
use App\Models\ProjectStaff;
use App\Models\Staff;
use App\Models\Task;
use App\Notifications\TopicReplied;
use Carbon\Carbon;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Http\Request;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Support\MessageBag;
use Spatie\Activitylog\Models\Activity;
use Field\Interaction\FieldTriggerTrait;
use Field\Interaction\FieldSubscriberTrait;

class ProjectController extends AdminController
{
    use FieldTriggerTrait, FieldSubscriberTrait;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '项目管理';
    protected $grade = [];
    protected $status = [];
    protected $node_status = [];
    protected $check_status = [];
    protected $finance_status = [];

    public function __construct()
    {
        $this->grade = [1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E'];
        $this->status = [1 => '已立项', 2 => '进行中', 3 => '已暂停', 4 => '已结项'];
        $this->node_status = [1 => '未开始', 2 => '进行中', 3 => '已完成'];

        $this->check_status = [1 => '签约审核成功', 2 => '设计验收成功', 3 => '前端验收成功', 4 => '整体验收成功', 5 => '设计评审成功', 6 => '前端评审成功'];

        $this->finance_status = [1 => '签约审核收款', 2 => '设计审核收款', 3 => '前端审核收款', 4 => '验收审核收款'];
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Project());

        $grid->model()->orderBy('sort_order')->orderBy('contract_time', 'desc');
        $auth = auth('admin')->user();
        $slug = $auth->roles->pluck('slug')->toarray();

        if (!in_array($auth->id, [1, 2]) && !in_array('apply', $slug)) {
            $staff_id = Staff::where('admin_id', $auth->id)->first()->id;
            $project_ids = ProjectNode::where('staff_id', $staff_id)->pluck('project_id')->toarray();
            $project_staff_ids = ProjectStaff::where('staff_id', $staff_id)->pluck('project_id')->toarray();

            $ids=array_merge($project_ids,$project_staff_ids);

            $grid->model()->whereIn('id', $ids);
        }

        $grid->column('id', __('Id'));
        if (!in_array($auth->id, [1, 2])) {
            $grid->column('name', __('Name'))->limit(10);
        } else {
            $grid->column('name', __('Name'))->display(function () {
                return '<a href="/admin/projects/' . $this->id . '/edit">' . sub($this->name, 10) . '</a>';
            });
        }

        $grid->column('task.name', __('任务名称'))->hide();
        $grid->column('grade', __('优先级'))->using($this->grade)->label([
            1 => 'default',
            2 => 'info',
            3 => 'warning',
            4 => 'success',
            5 => 'danger',
        ]);
        $grid->column('status', __('Status'))->using($this->status)->display(function () {
            $status = $this->status;
            switch ($status) {
                case 1:
                    return '<span class="label" style="font-weight:unset; color: #444; background-color: #AEDAFF"><i class="fa fa-plus-circle"></i>&nbsp;已立项</span>';
                case 2:
                    return '<span class="label" style="font-weight:unset; color: #444; background-color: #8EFFB9"><i class="fa fa-paper-plane-o"></i>&nbsp;进行中</span>';
                case 3:
                    return '<span class="label" style="font-weight:unset; color: #444; background-color: #FFA3BE"><i class="fa fa-pause-circle"></i>&nbsp;已暂停</span>';
                case 4:
                    return '<span class="label" style="font-weight:unset; color: #444; background-color: #d2d6de"><i class="fa fa-power-off"></i>&nbsp;已结项</span>';
            }
        });

        $grid->column('content', __('Content'))->hide();
        // 不存在的`full_name`字段
        $grid->column('customer.name', '商务');

        $grid->column('staff_name', '项目负责人')->display(function () {
            $staff_ids = ProjectStaff::where('project_id', $this->id)->pluck('staff_id')->toArray();
            $staff_name = Staff::wherein('id', $staff_ids)->pluck('name')->toArray();
            return $staff_name;
        })->map(function ($staff_name) {
            return $staff_name;
        })->implode(',');

        $grid->column('project_nodes', __('节点情况'))->display(function () {
            return '查看';
            implode('&nbsp;', $html);
        })->expand(function ($model) {

            $project_nodes = $this->project_nodes->map(function ($model) {
                $staff_name = $model->staff ? $model->staff->name : '';
                $node_name = $model->node ? $model->node->name : '';

                $node_status = $model->status;
                $status = '';
                if ($node_status == 2) {
                    $status = '<span class="label" style="font-weight:unset; color: #444; background-color: #DFFA99"><i class="fa fa-clock-o"></i>&nbsp;进行中</span>';
                } else if ($node_status == 3) {
                    $status = '<span class="label" style="font-weight:unset; color: #444; background-color: #87FAC1"><i class="fa fa-check-circle-o"></i>&nbsp;已完成</span>';
                } else {
                    $status = '<span class="label" style="font-weight:unset; color: #444; background-color: #FAC0D6"><i class="fa fa-frown-o"></i>&nbsp;未开始</span>';
                }

                $nodes_info = ProjectNodeInfo::where('project_node_id', $model->id)->get()->map(function ($model) {
                    $nodes_info_list = [
                        'created_at' => $model->created_at,
                        'content' => $model->content,
                        'remark' => $model->remark,
                    ];
                    return $nodes_info_list;
                });
                $nodes_info = new Table(['时间', '详情', '备注'], $nodes_info->toArray());
                $nodes = [
                    'id' => $model->id,
                    'node_name' => $node_name,
                    'node_status' => $status,
                    'staff_name' => $staff_name,
                    'start_time' => $model->start_time,
                    'end_time' => $model->end_time,
                    'days' => $model->days,
                    'content' => $model->content,
                    'info' => $nodes_info
                ];
                return $nodes;
            });

            return new Table(['ID', '节点', '状态', '节点负责人', '开始时间', '结束时间', '耗时(天)', '备注', '详情'], $project_nodes->toArray());
        });


        $grid->column('days', __('总天数'))->display(function ($days) {
            $result = ProjectNode::where('project_id', $this->id)->sum('days');
            return $result;
        });

        $auth = auth('admin')->user();
        $slug = $auth->roles->pluck('slug')->toarray();

        if (in_array($auth->id, [1, 2]) || in_array('apply', $slug)) {
            $grid->column('check_status', __('回款状态'))->using($this->check_status)->expand(function ($model) {
                $check_status = [1 => '签约审核成功', 2 => '设计验收成功', 3 => '前端验收成功', 4 => '整体验收成功'];
                $apply_status = [1 => 'qy_rate', 2 => 'sj_rate', 3 => 'qd_rate', 4 => 'ys_rate'];

                $finances = $model->finances->map(function ($model) use ($check_status, $apply_status) {
                    $nodes = [
                        'id' => $model->id,
                        'patron_name' => $model->patron ? $model->patron->name : '',
                        'money' => $model->money,
                        'status' => $check_status[$model->status],
                        'returned' => $model->project[$apply_status[$model->status]] * $model->money / 100,
                        'returned_money' => $model->returned_money,
                        'rebate' => $model->rebate,
                        'returned_bag' => $model->returned_bag,
                        'debtors' => $model->debtors,
                        'info' => '<a target="_blank" href="/admin/finances?project_id=' . $model->project_id . '">详情</a>',
                    ];
                    return $nodes;
                });

                return new Table(['ID', '客户名称', '合同金额', '状态', '预计回款金额', '实际回款金额', '返渠道费', '回款账户', '未结余额', '详情'], $finances->toarray());
            });

            $grid->column('remark', __('Remark'))->limit(10);
            $grid->column('money', __('Money'));
        }

        $grid->column('demands', __('新增需求'))->display(function ($model) {
            return empty($model) ? false : '查看';
        })->expand(function ($model) {

            $project_demands = Demand::where('project_id', $model->id)->get()->map(function ($model) {
                $pact = $model->pact ? '<i class="fa fa-check text-green"></i>' : '<i class="fa fa-close text-red"></i>';
                $status = $model->status ? '<span class="label label-success">已审核</span>' : '<span class="label label-danger">未审核</span>';
                $nodes = [
                    'pact' => $pact,
                    'status' => $status,
                    'money' => $model->money,
                    'description' => $model->description,
                    'remark' => $model->remark,
                ];
                return $nodes;
            });

            return new Table(['合同', '状态', '金额', '详情', '备注'], $project_demands->toArray());
        })->hide();

        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');
        $states = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        if (!in_array($auth->id, [1, 2])) {
            $grid->column('is_check', __('是否交付'))->bool();
            $grid->column('check_time', __('交付时间'))->display(function ($model) {
                if ($model) {
                    return date('Y-m-d', strtotime($model));
                }
            });
            $grid->column('y_check_time', __('预计交付时间'))->display(function ($model) {
                if ($model) {
                    return date('Y-m-d', strtotime($model));
                }
            });
        } else {
            $grid->column('is_check', __('是否交付'))->switch($states);
            $grid->column('contract_time', __('Contract time'))->display(function ($model) {
                if ($model) {
                    return date('Y-m-d', strtotime($model));
                }
            });
            $grid->column('check_time', __('交付时间'))->display(function ($model) {
                if ($model) {
                    return date('Y-m-d', strtotime($model));
                }
            });
            $grid->column('y_check_time', __('预计交付时间'))->display(function ($model) {
                if ($model) {
                    return date('Y-m-d', strtotime($model));
                }
            });
        }

        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();

        $grid->fixColumns(1, -1);

        $grid->exporter(new ProjectImport());

        $grid->filter(function ($filter) {

            $filter->like('name', __('Name'));
            $filter->between('contract_time', __('Contract time'))->date();
            $status_text = [
                1 => '交付',
                0 => '未交付'
            ];
            $filter->equal('is_check', __('是否交付'))->select($status_text);

            $filter->equal('check_status', __('回款状态'))->select([1 => '签约审核成功', 2 => '设计验收成功', 3 => '前端验收成功', 4 => '整体验收成功', 5 => '设计评审成功', 6 => '前端评审成功']);

            $filter->equal('grade', __('优先级'))->select($this->grade);
            $filter->equal('status', __('Status'))->select($this->status);

            $customers = Customer::all()->toArray();
//            $select_ = array_prepend($customers, ['id' => 0, 'name' => '公有池']);
            $customer_array = array_column($customers, 'name', 'id');
            //创建select
            $filter->equal('customer_id', '商务')->select($customer_array);

            $filter->where(function ($query) {

                $query->whereHas('staffs', function ($query) {
                    $query->where('name', 'like', "%{$this->input}%");
                });

            }, '项目负责人');

        });

//        $grid->export(function ($export) {
//
//            $export->filename('项目列表');
//
//            $export->originalValue(['money', 'contract_time']);  //比如对列使用了$grid->column('name')->label()方法之后，那么导出的列内容会是一段HTML，如果需要某些列导出存在数据库中的原始内容，使用originalValue方法
//
//            // $export->only(['name', 'nickname', 'sex']); //用来指定只能导出哪些列。
//
//            $export->except(['sort_order', 'updated_at']); //用来指定哪些列不需要被导出
//            $export->column('customer_name', function ($value, $original) {
//                return  cutstr_html($value);
//            });
//            $export->column('staff_name', function ($value, $original) {
//                return  cutstr_html($value);
//            });
//            $export->column('is_check', function ($value, $original) {
//                switch ($original) {
//                    case 1:
//                        return '是';
//                    default:
//                        return '否';
//                }
//            });
//        });
        if (!in_array($auth->id, [1, 2])) {
            #禁用创建按钮
            $grid->disableCreateButton();
            #禁用导出数据按钮
            $grid->disableExport();
            #禁用行选择checkbox
            $grid->disableRowSelector();
        }
        $grid->tools(function ($tools) use ($auth) {
            if (!in_array($auth->id, [1, 2])) {
                // 禁用批量删除按钮
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            }
        });

        $grid->actions(function ($actions) use ($auth) {
            if (!in_array($auth->id, [1, 2])) {
                $actions->disableDelete();
                $actions->disableView();
                $actions->disableEdit();

                $slug = $auth->roles->pluck('slug')->toarray();

                if (in_array('apply', $slug)) {
                    $actions->add(new SjCheck());
                    $actions->add(new QdCheck());
                    $actions->add(new YsCheck());
                }

                $actions->add(new AddDemand());
            } else {
                $actions->add(new AddDemand());
                $actions->add(new SjCheck());
                $actions->add(new QdCheck());
                $actions->add(new YsCheck());
//                $actions->add(new Calendar());
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
        $show = new Show(Project::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('task.name', __('任务名称'));
        $show->field('grade', __('优先级'))->using($this->grade);
        $show->field('status', __('Status'))->using($this->status);
        $show->field('project_nodes', __('节点情况'))->as(function () {
            $nodes = $this->project_nodes;
            foreach ($nodes as $k => $v) {
                $staff_name = $v->staff ? $v->staff->name : '';
                $node_name = $v->node ? $v->node->name : '';
                $nodes[$k] = [
                    'node_name' => $node_name,
                    'staff_name' => $staff_name,
                    'start_time' => $v['start_time'],
                    'end_time' => $v['end_time'],
                    'days' => $v['days'],
                    'content' => isset($v['content']) ? $v['content'] : '',
                ];
            }

            return new Table(['节点', '项目负责人', '开始时间', '结束时间', '耗时(天)', '详情'], $nodes->toarray());
        })->json();
        $show->field('content', __('Content'));
        $show->field('remark', __('Remark'));
        $show->field('money', __('Money'));
        $show->field('sort_order', __('Sort order'));
        $show->field('is_check', __('是否交付'));
        $show->field('contract_time', __('Contract time'));
        $show->field('check_time', __('交付时间'));
        $show->field('y_check_time', __('预计交付时间'));
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

        $auth = auth('admin')->user();
        $slug = $auth->roles->pluck('slug')->toarray();

        if (in_array($auth->id, [1, 2]) || in_array('apply', $slug)) {


            // 弄一个触发事件的Script对象。
            $triggerScript = $this->createTriggerScript($form);
// 弄-个接收并处理事件的Script对象。
            $subscribeScript = $this->createSubscriberScript($form, function($builder){
                // 添加事件响应函数
                $builder->subscribe('money', 'change', function($event){

                    // 这里填写处理事件的javascript脚本，注意：一定要返回一个完整的 javascript function ，否则报错！！！！
                    return <<< EOT
               
               // function中的参数data，是事件自带数据，方便做逻辑处理！data会因为事件不同而类型不同，具体可以在chrome中的console中查看。
               
                function (data) {
                        console.log ('catch an event -> {$event}');
                       console.log(data);
                }
               
EOT;
                });
            });

            $form->tab('基础信息', function ($form)use ($triggerScript, $subscribeScript) {

                $form->text('name', __('Name'))->rules('required');

                $tasks = Task::all()->toArray();
                $select_ = array_prepend($tasks, ['id' => 0, 'name' => '其它']);
                $select_task = array_column($select_, 'name', 'id');

                //创建select
                $form->select('task_id', '任务名称')->options($select_task);

                $staffs = Staff::orderby('sort_order')->pluck('name', 'id')->toArray();

                $customers = Customer::orderby('sort_order')->get()->toArray();
                $customer_array = array_column($customers, 'name', 'id');

                $form->select('customer_id', __('商务'))->options($customer_array)->load('patron_id', '/api/customer_patron');

                $form->select('patron_id', '客户名称');

                if ($form->isEditing()) {
                    $id = request()->route()->parameters()['project'];
                    $staff_ids = ProjectStaff::where('project_id', $id)->pluck('staff_id')->toArray();
                    $form->multipleSelect('staffs', __('项目负责人'))
                        ->options($staffs)->default($staff_ids);
                } else {
                    $form->multipleSelect('staffs', __('项目负责人'))
                        ->options($staffs);
                }

                $form->radio('grade', '优先级')->options($this->grade)->default(1);
                $form->radio('status', __('Status'))->options($this->status)->default(1);


                $form->textarea('remark', __('Remark'));

                $form->ueditor('content', __('Content'));

                $form->decimal('money', __('Money'))->default(0.00)->rules('required');

                $form->number('sort_order', __('Sort order'))->default(99);
                $states = [
                    'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
                    'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
                ];
                $form->switch('is_check', __('是否交付'))->states($states)->default(0);
                $form->datetime('contract_time', __('Contract time'));
                $form->datetime('check_time', __('交付时间'));
                $form->datetime('y_check_time', __('预计交付时间'));


                // 最后把 $triggerScript 和 $subscribeScript 注入到Form中去。
                // scriptinjecter 第一个参数可以为任何字符，但不能为空！！！！
                $form->scriptinjecter('anyname_but_not_null', $triggerScript, $subscribeScript);

            }, true);


            $form->tab('回款情况', function ($form) {
                $form->rate('qy_rate', '签约付款比列')->help('占合同总额百分比')->default(40);
                $form->rate('sj_rate', '设计付款比列')->help('占合同总额百分比')->default(30);
                $form->rate('qd_rate', '前端付款比列')->help('占合同总额百分比')->default(20);
                $form->rate('ys_rate', '验收付款比列')->help('占合同总额百分比')->default(10);

                $form->select('check_status', '回款状态')->options($this->check_status)->default(1);

                // 子表字段
                $form->hasMany('finances', __('回款记录'), function (Form\NestedForm $form) {
                    $staffs = Staff::all()->toArray();
                    $select_ = array_prepend($staffs, ['id' => 1, 'name' => '超级管理员']);
                    $select_staff = array_column($select_, 'name', 'id');
                    $form->select('staff_id', '审核人')->options($select_staff)->default(1);
                    $form->select('status', __('Status'))->options($this->finance_status);
                    $states = [
                        'on' => ['value' => 1, 'text' => '有', 'color' => 'success'],
                        'off' => ['value' => 0, 'text' => '无', 'color' => 'danger'],
                    ];
                    $form->switch('pact', __('合同（有/无）'))->states($states)->default(0);

                    $form->text('returned_money', '回款金额');
                    $form->text('rebate', '返渠道费');
                    $form->text('returned_bag', '回款账户');
                    $form->text('debtors', '未结余额');
                    $form->textarea('description', '开票情况');
                    $form->textarea('remark', '项目备注');
                });
            });

        }

        $form->tab('节点情况', function ($form) {
            $form->hasMany('project_nodes', __('节点情况'), function (Form\NestedForm $form) {
                $staffs = Staff::all()->toArray();
                $select_staff = array_column($staffs, 'name', 'id');
                $form->select('staff_id', '项目负责人')->options($select_staff)->rules('required');

                $nodes = Node::where('is_project', true)->get()->toArray();
                $select_node = array_column($nodes, 'name', 'id');
                $form->select('node_id', '节点')->options($select_node);

                $form->select('status', __('Status'))->options($this->node_status);

                $form->datetime('start_time', '开始时间')->default(date('Y-m-d', time()));
                $form->datetime('end_time', '结束时间')->default(date('Y-m-d', time()));
                $form->textarea('content', '备注');

                $states = [
                    'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
                    'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
                ];
                $form->switch('is_notice', __('发送通知消息'))->states($states)->default(0);
            });
        });

        $form->tab('设计评审人员', function ($form) {
            // 子表字段
            $form->hasMany('design_checks', __('设计评审人员'), function (Form\NestedForm $form) {
                $staffs = Staff::all()->toArray();
                $select_staff = array_column($staffs, 'name', 'id');
                $form->select('staff_id', '审核人')->options($select_staff);
                $states = [
                    'on' => ['value' => 1, 'text' => '已审核', 'color' => 'success'],
                    'off' => ['value' => 0, 'text' => '待审核', 'color' => 'danger'],
                ];
                $form->switch('status', __('Status'))->states($states)->default(0);
                $form->textarea('description', __('Description'));
                $form->textarea('remark', __('Remark'));
            });
        });

        $form->tab('前端评审人员', function ($form) {
            // 子表字段
            $form->hasMany('html_checks', __('前端评审人员'), function (Form\NestedForm $form) {
                $staffs = Staff::all()->toArray();
                $select_staff = array_column($staffs, 'name', 'id');
                $form->select('staff_id', '审核人')->options($select_staff);
                $states = [
                    'on' => ['value' => 1, 'text' => '已审核', 'color' => 'success'],
                    'off' => ['value' => 0, 'text' => '待审核', 'color' => 'danger'],
                ];
                $form->switch('status', __('Status'))->states($states)->default(0);
                $form->textarea('description', __('Description'));
                $form->textarea('remark', __('Remark'));
            });
        });

        $this->style = <<<EOT
//               h4{background-color: cornflowerblue;
//    padding: 9px;
//    border-radius: 24%;};
EOT;
        Admin::style($this->style);


        //保存前回调
        $form->saving(function (Form $form) {

            $project_nodes = \Request('project_nodes');
            if ($project_nodes==null){
                $error = new MessageBag([
                    'title'   => '错误...',
                    'message' => '项目节点不能为空....',
                ]);

                return back()->with(compact('error'));
            }

            $is_check = \Request('is_check');
            if ($is_check == 'on') {
                $form->check_time = date('Y-m-d H:i:s', time());
            } else {
                $form->check_time = null;
            }

        });

        //保存后回调
        $form->saved(function (Form $form) {
            $id = $form->model()->id;
            $project_nodes = array_filter(\Request('project_nodes'));

            $finances = array_filter(\Request('finances'));

            Finance::where('project_id', $id)->update([
                'money' => $form->model()->money,
                'customer_id' =>$form->model()->customer_id,
                'patron_id' =>$form->model()->patron_id,
            ]);

//           dump($id);
//           exit();
            if (!empty($project_nodes)) {
                foreach ($project_nodes as $value) {
                    ProjectNode::where('project_id', $id)->where('node_id', $value['node_id'])->where('staff_id', $value['staff_id'])->update([
                        'days' => $this->get_weekend_days($value['start_time'], $value['end_time']),
                    ]);

                    //是否发送消息通知
                    $is_notice = $value['is_notice'];
                    $name = \Request('name');
                    $staff = Staff::find($value['staff_id']);
                    $node_name = Node::find($value['node_id'])->name;
                    if ($is_notice == 'on') {
                        activity()->inLog(9)
                            ->performedOn($staff)
                            ->causedBy(auth('admin')->user())
                            ->withProperties([])
                            ->log($name . '项目' . $node_name . '任务：' . $value['content']);

                        $lastLoggedActivity = Activity::all()->last();

                        $when = Carbon::now()->addSeconds(10);
                        $staff->notify((new TopicReplied($lastLoggedActivity))->delay($when));
                    }

                }
            }


        });

        return $form;
    }

    /**
     * | @param char|int $start_date 一个有效的日期格式，例如：20091016，2009-10-16
     * | @param char|int $end_date 同上
     * | @param int $weekend_days 一周休息天数
     * | @return array
     * | array[total_days]  给定日期之间的总天数
     * | array[total_relax] 给定日期之间的周末天数
     */
    function get_weekend_days($start_date, $end_date, $weekend_days = 2)
    {

        $data = array();
        if (strtotime($start_date) > strtotime($end_date)) list($start_date, $end_date) = array($end_date, $start_date);

        $start_reduce = $end_add = 0;
        $start_N = date('N', strtotime($start_date));
        $start_reduce = ($start_N == 7) ? 1 : 0;

        $end_N = date('N', strtotime($end_date));

        // 进行单、双休判断，默认按单休计算
        $weekend_days = intval($weekend_days);
        switch ($weekend_days) {
            case 2:
                in_array($end_N, array(6, 7)) && $end_add = ($end_N == 7) ? 2 : 1;
                break;
            case 1:
            default:
                $end_add = ($end_N == 7) ? 1 : 0;
                break;
        }

        $days = round(abs(strtotime($end_date) - strtotime($start_date)) / 86400) + 1;
        $data['total_days'] = $days;
        $data['total_relax'] = floor(($days + $start_N - 1 - $end_N) / 7) * $weekend_days - $start_reduce + $end_add;
        $data['total_work'] = $days - (floor(($days + $start_N - 1 - $end_N) / 7) * $weekend_days - $start_reduce + $end_add);

        return $data['total_work'];
    }

    public function project_node(Content $content, $id)
    {
        $project = Project::with('project_nodes')->find($id);
        return $content
            ->title($project->name)
            ->description('Doing')
            ->row(function (Row $row) use ($project) {
                $row->column(12, function (Column $column) use ($project) {

                    $project_nodes = $project->project_nodes->map(function ($model) {
                        $staff_name = $model->staff ? $model->staff->name : '';
                        $node_name = $model->node ? $model->node->name : '';

                        $node_status = $model->status;
                        $status = '';
                        if ($node_status == 2) {
                            $status = '<span class="label" style="font-weight:unset; color: #444; background-color: #DFFA99"><i class="fa fa-clock-o"></i>&nbsp;进行中</span>';
                        } else if ($node_status == 3) {
                            $status = '<span class="label" style="font-weight:unset; color: #444; background-color: #87FAC1"><i class="fa fa-check-circle-o"></i>&nbsp;已完成</span>';
                        } else {
                            $status = '<span class="label" style="font-weight:unset; color: #444; background-color: #FAC0D6"><i class="fa fa-frown-o"></i>&nbsp;未开始</span>';
                        }

                        $nodes = [
                            'id' => $model->id,
                            'node_name' => $node_name,
                            'node_status' => $status,
                            'staff_name' => $staff_name,
                            'start_time' => $model->start_time,
                            'end_time' => $model->end_time,
                            'days' => $model->days,
                            'content' => "<a class='btn btn-xs action-btn btn-danger grid-row-refuse' data-id='{$model->id}'><i class='fa fa-eye' title='详情'>详情</i></a>"
                        ];
                        return $nodes;
                    });

                    $table = new Table(['ID', '节点', '状态', '项目负责人', '开始时间', '结束时间', '耗时(天)', '操作'], $project_nodes->toArray());

                    $column->append(new Box('任务情况', $table->render()));

                    //新增需求
                    if ($project->is_add) {
                        $project_demands = Demand::where('project_id', $project->id)->get()->map(function ($model) {
                            $pact = $model->pact ? '<i class="fa fa-check text-green"></i>' : '<i class="fa fa-close text-red"></i>';
                            $status = $model->status ? '<span class="label label-success">已审核</span>' : '<span class="label label-danger">未审核</span>';
                            $nodes = [
                                'pact' => $pact,
                                'status' => $status,
                                'money' => $model->money,
                                'description' => $model->description,
                                'remark' => $model->remark,
                            ];
                            return $nodes;
                        });

                        $demands = new Table(['合同', '状态', '金额', '详情', '备注'], $project_demands->toArray());

                        $column->append(new Box('新增需求', $demands->render()));
                    }

                    /**
                     * 创建模态框
                     */
                    $this->script = <<<EOT
                    $('.grid-row-refuse').unbind('click').click(function() {
                        var id = $(this).data('id');
                        $.ajax({
                            method: 'get',
                            url: '/admin/projects/info/' + id,
                            success: function (data) {
                                console.log(data);
                                var content = "无记录";
                                if (data.length>0) {
                                    var html1="<table class='table'>"
                                        + "<thead><tr>"
                                        + "     <th> 详情</th> <th>备注</th> <th>时间</th>"
                                        + "</tr></thead><tbody>";
                                       
                                     var html2="</tbody></table>"
                                     var html='';
                                     for (var i=0;i<data.length;i++)
                                        { 
                                           html+='<tr><td>'+data[i]['content']+'</td><td>'+data[i]['remark']+'</td><td>'+data[i]['updated_at']+'</td></tr>';
                                        }
                                     content  = html1+html+html2;
                                }

                                swal.fire({
                                    title: '<strong>记录</strong>',
                                 //   type: 'info',
                                    html: content, // HTML
                                    focusConfirm: true, //聚焦到确定按钮
                                    showCloseButton: true,//右上角关闭
                                    customClass: "Alerttable",
                                })
                            }
                        });
                    });
EOT;
                    $this->style = <<<EOT
               .Alerttable{width: 90%; font-size: 14px;}
               .Alerttable th{text-align: center;}
EOT;
                    Admin::script($this->script);
                    Admin::style($this->style);

                    $column->row(function (Row $row) use ($project) {
                        $row->column(6, function (Column $column) use ($project) {

                            $form = new \Encore\Admin\Widgets\Form();
                            $form->action('/admin/projects/work');

                            $auth = auth('admin')->user();
                            $staff = Staff::where(['admin_id' => $auth->id])->first();

                            $id = ProjectNode::where(['project_id' => $project->id, 'staff_id' => $staff->id])->first()->id;
                            $form->hidden('id')->default($id);

                            $form->textarea('content', '详情');
                            $form->textarea('remark', '备注');

                            $column->append(new Box('更新工作...', $form->render()));
                        });

                        $row->column(6, function (Column $column) use ($project) {

                            $form = new \Encore\Admin\Widgets\Form();
                            $form->action('/admin/projects/status');

                            $auth = auth('admin')->user();
                            $staff = Staff::where(['admin_id' => $auth->id])->get()->toarray();
                            $select_staff = array_column($staff, 'name', 'id');

                            $node_ids = ProjectNode::where(['project_id' => $project->id, 'staff_id' => $staff[0]['id']])->pluck('node_id');
                            $nodes = Node::wherein('id', $node_ids)->get()->toArray();
                            $select_node = array_column($nodes, 'name', 'id');

                            $id = ProjectNode::where(['project_id' => $project->id, 'staff_id' => $staff[0]['id']])->first()->id;
                            $form->hidden('id')->default($id);

                            $form->select('staff_id', '项目负责人')->options($select_staff)->default(key($select_staff))->rules('required');

                            $form->select('node_id', '节点')->options($select_node)->default(key($select_node))->rules('required');

                            $form->select('status', __('Status'))->options($this->node_status)->default(key($this->node_status));

                            $column->append(new Box('更新状态...', $form->render()));
                        });
                    });
                });
            });
    }

    public function project_info($id)
    {
        $list = ProjectNodeInfo::where('project_node_id', $id)->get();

        return $list;
    }

    public function project_work(Request $request)
    {
        ProjectNodeInfo::create([
            'project_node_id' => $request->id,
            'content' => $request->input('content'),
            'remark' => $request->remark,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $success = new MessageBag([
            'title' => 'Success',
            'message' => '提交成功....',
        ]);
        return back()->with(compact('success'));

    }

    public function project_status(Request $request)
    {
        ProjectNode::where(['id' => $request->id])->update([
            'status' => $request->input('status'),
        ]);

        $success = new MessageBag([
            'title' => 'Success',
            'message' => '提交成功....',
        ]);
        return back()->with(compact('success'));

    }


    public function design(Content $content, $id)
    {
        $design_check = DesignCheck::with('project', 'staff')->find($id);
        return $content
            ->title($design_check->project->name)
            ->description($design_check->staff->name)
            ->row(function (Row $row) use ($design_check) {
                $row->column(6, function (Column $column) use ($design_check) {

                    $checks = DesignCheck::with('staff')->where('project_id', $design_check->project->id)->get()->map(function ($model) {
                        $result = [
                            'name' => $model->staff->name,
                            'status' => $model->status ? '<span class="label label-success">已审核</span>' : '<span class="label label-danger">待审核</span>',
                            'description' => $model->remark,
                        ];
                        return $result;
                    });

                    $headers = ['姓名', '状态', '备注'];

                    $table = new Table($headers, $checks->toarray());

                    $column->append(new Box('评审人员', $table->render()));
                });

                $row->column(6, function (Column $column) use ($design_check) {

                    $form = new \Encore\Admin\Widgets\Form();
                    $form->action('/admin/projects/design_check');

                    $form->hidden('id')->default($design_check->id);

                    $form->select('status', __('Status'))->options([1 => '已审核', 0 => '待审核'])->default($design_check->status);

                    $form->textarea('description', '简介')->default($design_check->description)->rules('required');

                    $form->textarea('remark', '备注')->default($design_check->remark)->rules('required');


                    $column->append(new Box('设计评审', $form->render()));
                });
            });
    }

    public function design_check(Request $request)
    {
        $design_check = DesignCheck::find($request->id);

        $project = Project::find($design_check->project_id);
        if ($project->check_status != 1) {
            $error = new MessageBag([
                'title' => 'Error',
                'message' => '提交失败,请先完成签约审核....',
            ]);
            return back()->with(compact('error'));
        }

        $design_check->description = $request->description;
        $design_check->remark = $request->remark;
        $design_check->status = $request->status;
        $design_check->save();

        $result = DesignCheck::where('project_id', $design_check->project_id)->where('status', 0)->exists();
        if (!$result) {
            $project->check_status = 5;
        } else {
            $project->check_status = 1;
        }
        $project->save();

        $stause = $request->status ? '设计评审成功' : '设计评审失败';
        activity()->inLog(3)
            ->performedOn($project)
            ->causedBy(auth('admin')->user())
            ->withProperties(['description' => $request->description, 'remark' => $request->remark])
            ->log('更新' . $project->name . '状态为：' . $stause);

        $lastLoggedActivity = Activity::all()->last();

        $staffs = Staff::where('is_notice', 1)->get();
        //执行消息分发
        dispatch(new \App\Jobs\SendNotice($staffs, new TopicReplied($lastLoggedActivity), 5));

        $success = new MessageBag([
            'title' => 'Success',
            'message' => '提交成功....',
        ]);
        return back()->with(compact('success'));
    }

    public function html(Content $content, $id)
    {
        $html_check = HtmlCheck::with('project', 'staff')->find($id);
        return $content
            ->title($html_check->project->name)
            ->description($html_check->staff->name)
            ->row(function (Row $row) use ($html_check) {
                $row->column(6, function (Column $column) use ($html_check) {

                    $checks = HtmlCheck::with('staff')->where('project_id', $html_check->project->id)->get()->map(function ($model) {
                        $result = [
                            'name' => $model->staff->name,
                            'status' => $model->status ? '<span class="label label-success">已审核</span>' : '<span class="label label-danger">待审核</span>',
                            'description' => $model->remark,
                        ];
                        return $result;
                    });

                    $headers = ['姓名', '状态', '备注'];

                    $table = new Table($headers, $checks->toarray());

                    $column->append(new Box('评审人员', $table->render()));
                });

                $row->column(6, function (Column $column) use ($html_check) {
                    $form = new \Encore\Admin\Widgets\Form();
                    $form->action('/admin/projects/html_check');

                    $form->hidden('id')->default($html_check->id);

                    $form->select('status', __('Status'))->options([1 => '已审核', 0 => '待审核'])->default($html_check->status);

                    $form->textarea('description', '简介')->default($html_check->description)->rules('required');

                    $form->textarea('remark', '备注')->default($html_check->remark)->rules('required');


                    $column->append(new Box('前端评审', $form->render()));
                });
            });
    }

    public function html_check(Request $request)
    {
        $html_check = HtmlCheck::find($request->id);

        $project = Project::find($html_check->project_id);
        if ($project->check_status != 2) {
            $error = new MessageBag([
                'title' => 'Error',
                'message' => '提交失败,请先完成设计审核....',
            ]);
            return back()->with(compact('error'));
        }

        $html_check->description = $request->description;
        $html_check->remark = $request->remark;
        $html_check->status = $request->status;
        $html_check->save();

        $result = HtmlCheck::where('project_id', $html_check->project_id)->where('status', 0)->exists();
        if (!$result) {
            $project->check_status = 6;
        } else {
            $project->check_status = 2;
        }
        $project->save();

        $stause = $request->status ? '前端评审成功' : '前端评审失败';
        activity()->inLog(5)
            ->performedOn($project)
            ->causedBy(auth('admin')->user())
            ->withProperties(['description' => $request->description, 'remark' => $request->remark])
            ->log('更新' . $project->name . '状态为：' . $stause);

        $lastLoggedActivity = Activity::all()->last();

        $staffs = Staff::where('is_notice', 1)->get();
        //执行消息分发
        dispatch(new \App\Jobs\SendNotice($staffs, new TopicReplied($lastLoggedActivity), 5));

        $success = new MessageBag([
            'title' => 'Success',
            'message' => '提交成功....',
        ]);
        return back()->with(compact('success'));
    }


    //新增需求
    public function demand(Content $content, $id)
    {
        $project = Project::find($id);
        return $content
            ->title($project->name)
            ->description('新增需求')
            ->row(function (Row $row) use ($project) {
                $row->column(12, function (Column $column) use ($project) {
                    $form = new \Encore\Admin\Widgets\Form();
                    $form->action('/admin/projects/add_demand');

                    $form->hidden('project_id')->default($project->id);

                    $form->radio('pact', __('合同（有/无）'))->options([1 => '有', 0 => '无'])->default(1);
                    $form->text('money', '金额');
                    $form->textarea('description', '需求情况');
                    $form->textarea('remark', '备注');


                    $form->html('<a class="btn btn-sm btn-default mallto-next"  href="/admin/projects"><i class="fa fa-arrow-left"></i>&nbsp;返回</a>');

                    $column->append(new Box('新增需求', $form->render()));
                });
            });
    }

    public function add_demand(Request $request)
    {
        $project = Project::find($request->project_id);
        Demand::create([
            'project_id' => $request->project_id,
            'status' => 0,
            'pact' => $request->get('pact'),
            'money' => $project->money,
            'description' => $request->get('description'),
            'remark' => $request->get('remark'),
        ]);

        $project->is_add = 1;
        $project->save();

        $success = new MessageBag([
            'title' => 'Success',
            'message' => '新增需求成功,等待审核....',
        ]);
        return back()->with(compact('success'));
    }

    //设计验收
    public function sj(Content $content, $id)
    {
        $project = Project::find($id);
        return $content
            ->title($project->name)
            ->description('设计验收')
            ->row(function (Row $row) use ($project) {
                $row->column(12, function (Column $column) use ($project) {
                    $form = new \Encore\Admin\Widgets\Form();
                    $form->action('/admin/projects/sj_check');
                    $form->hidden('project_id')->default($project->id);

                    $form->text('name', '项目名称')->default($project->name)->disable();
                    $form->text('money', '合同金额')->default($project->money)->disable();
                    $form->text('qy_rate', '签约付款比列')->help('占合同总额百分比(%)')->default($project->qy_rate)->disable();
                    $form->text('sj_rate', '设计付款比列')->help('占合同总额百分比(%)')->default($project->sj_rate)->disable();
                    $form->text('qd_rate', '前端付款比列')->help('占合同总额百分比(%)')->default($project->qd_rate)->disable();
                    $form->text('ys_rate', '验收付款比列')->help('占合同总额百分比(%)')->default($project->ys_rate)->disable();

                    $form->select('status', __('项目进度状态'))->options([1 => '签约审核成功', 2 => '设计审核成功', 3 => '前端审核成功', 4 => '验收审核成功'])->default(1)->disable();
                    $form->radio('pact', __('合同（有/无）'))->options([1 => '有', 0 => '无'])->default(1);
                    $form->text('returned_money', '回款金额')->default($project->sj_rate * $project->money / 100);
                    $form->text('rebate', '返渠道费');
                    $form->text('returned_bag', '回款账户');
                    $form->text('debtors', '未结余额')->default($project->qd_rate * $project->money / 100 + $project->ys_rate * $project->money / 100);
                    $form->textarea('description', '开票情况');
                    $form->textarea('remark', '项目备注');

                    $form->html('<a class="btn btn-sm btn-default mallto-next"  href="/admin/projects"><i class="fa fa-arrow-left"></i>&nbsp;返回</a>');

                    $column->append(new Box('设计验收', $form->render()));
                });
            });
    }

    public function sj_check(Request $request)
    {
        $project = Project::find($request->project_id);

//        if ($project->check_status != 5) {
//
//            $error = new MessageBag([
//                'title' => 'Error',
//                'message' => '提交失败,设计评审未完成....',
//            ]);
//            return back()->with(compact('error'));
//        }

        $patron = Patron::where('project_id', $project->id)->first();
        Finance::create([
            'staff_id' => auth('admin')->user()->id,
            'customer_id' => $patron ? $patron->customer_id : 0,
            'project_id' => $project->id,
            'patron_id' => $patron ? $patron->id : 0,
            'status' => 2,
            'pact' => $request->get('pact'),
            'money' => $project->money,
            'returned_money' => $request->get('returned_money'),
            'rebate' => $request->get('rebate'),
            'returned_bag' => $request->get('returned_bag'),
            'debtors' => $request->get('debtors'),
            'description' => $request->get('description'),
            'remark' => $request->get('remark'),
        ]);


        $project->check_status = 2;
        $project->save();

        activity()->inLog(4)
            ->performedOn($project)
            ->causedBy(auth('admin')->user())
            ->withProperties([])
            ->log('更新' . $project->name . '状态为：设计验收成功');

        $lastLoggedActivity = Activity::all()->last();

        $staffs = Staff::where('is_notice', 1)->get();
        //执行消息分发
        dispatch(new \App\Jobs\SendNotice($staffs, new TopicReplied($lastLoggedActivity), 5));

        $success = new MessageBag([
            'title' => 'Success',
            'message' => '设计验收成功....',
        ]);
        return back()->with(compact('success'));
    }

    //前端验收
    public function qd(Content $content, $id)
    {
        $project = Project::find($id);
        return $content
            ->title($project->name)
            ->description('前端验收')
            ->row(function (Row $row) use ($project) {
                $row->column(12, function (Column $column) use ($project) {
                    $form = new \Encore\Admin\Widgets\Form();
                    $form->action('/admin/projects/qd_check');
                    $form->hidden('project_id')->default($project->id);

                    $form->text('name', '项目名称')->default($project->name)->disable();
                    $form->text('money', '合同金额')->default($project->money)->disable();
                    $form->text('qy_rate', '签约付款比列')->help('占合同总额百分比(%)')->default($project->qy_rate)->disable();
                    $form->text('sj_rate', '设计付款比列')->help('占合同总额百分比(%)')->default($project->sj_rate)->disable();
                    $form->text('qd_rate', '前端付款比列')->help('占合同总额百分比(%)')->default($project->qd_rate)->disable();
                    $form->text('ys_rate', '验收付款比列')->help('占合同总额百分比(%)')->default($project->ys_rate)->disable();

                    $form->select('status', __('项目进度状态'))->options([1 => '签约审核成功', 2 => '设计审核成功', 3 => '前端审核成功', 4 => '验收审核成功'])->default(1)->disable();
                    $form->radio('pact', __('合同（有/无）'))->options([1 => '有', 0 => '无'])->default(1);
                    $form->text('returned_money', '回款金额')->default($project->qd_rate * $project->money / 100);
                    $form->text('rebate', '返渠道费');
                    $form->text('returned_bag', '回款账户');
                    $form->text('debtors', '未结余额')->default($project->ys_rate * $project->money / 100);
                    $form->textarea('description', '开票情况');
                    $form->textarea('remark', '项目备注');

                    $form->html('<a class="btn btn-sm btn-default mallto-next"  href="/admin/projects"><i class="fa fa-arrow-left"></i>&nbsp;返回</a>');

                    $column->append(new Box('前端验收', $form->render()));
                });
            });
    }

    public function qd_check(Request $request)
    {
        $project = Project::find($request->project_id);

//        if ($project->check_status != 6) {
//            $error = new MessageBag([
//                'title' => 'Error',
//                'message' => '提交失败,前端评审未完成....',
//            ]);
//            return back()->with(compact('error'));
//        }

        $patron = Patron::where('project_id', $project->id)->first();
        Finance::create([
            'staff_id' => auth('admin')->user()->id,
            'customer_id' => $patron ? $patron->customer_id : 0,
            'project_id' => $project->id,
            'patron_id' => $patron ? $patron->id : 0,
            'status' => 3,
            'pact' => $request->get('pact'),
            'money' => $project->money,
            'returned_money' => $request->get('returned_money'),
            'rebate' => $request->get('rebate'),
            'returned_bag' => $request->get('returned_bag'),
            'debtors' => $request->get('debtors'),
            'description' => $request->get('description'),
            'remark' => $request->get('remark'),
        ]);


        $project->check_status = 3;
        $project->save();

        activity()->inLog(6)
            ->performedOn($project)
            ->causedBy(auth('admin')->user())
            ->withProperties([])
            ->log('更新' . $project->name . '状态为：前端验收成功');

        $lastLoggedActivity = Activity::all()->last();

        $staffs = Staff::where('is_notice', 1)->get();
        //执行消息分发
        dispatch(new \App\Jobs\SendNotice($staffs, new TopicReplied($lastLoggedActivity), 5));

        $success = new MessageBag([
            'title' => 'Success',
            'message' => '前端验收成功....',
        ]);
        return back()->with(compact('success'));
    }

    //整体验收
    public function ys(Content $content, $id)
    {
        $project = Project::find($id);
        return $content
            ->title($project->name)
            ->description('整体验收')
            ->row(function (Row $row) use ($project) {
                $row->column(12, function (Column $column) use ($project) {
                    $form = new \Encore\Admin\Widgets\Form();
                    $form->action('/admin/projects/ys_check');
                    $form->hidden('project_id')->default($project->id);

                    $form->text('name', '项目名称')->default($project->name)->disable();
                    $form->text('money', '合同金额')->default($project->money)->disable();
                    $form->text('qy_rate', '签约付款比列')->help('占合同总额百分比(%)')->default($project->qy_rate)->disable();
                    $form->text('sj_rate', '设计付款比列')->help('占合同总额百分比(%)')->default($project->sj_rate)->disable();
                    $form->text('qd_rate', '前端付款比列')->help('占合同总额百分比(%)')->default($project->qd_rate)->disable();
                    $form->text('ys_rate', '验收付款比列')->help('占合同总额百分比(%)')->default($project->ys_rate)->disable();

                    $form->select('status', __('项目进度状态'))->options([1 => '签约审核成功', 2 => '设计审核成功', 3 => '前端审核成功', 4 => '验收审核成功'])->default(1)->disable();
                    $form->radio('pact', __('合同（有/无）'))->options([1 => '有', 0 => '无'])->default(1);
                    $form->text('returned_money', '回款金额')->default($project->ys_rate * $project->money / 100);
                    $form->text('rebate', '返渠道费');
                    $form->text('returned_bag', '回款账户');
                    $form->text('debtors', '未结余额')->default(0);
                    $form->textarea('description', '开票情况');
                    $form->textarea('remark', '项目备注');

                    $form->html('<a class="btn btn-sm btn-default mallto-next"  href="/admin/projects"><i class="fa fa-arrow-left"></i>&nbsp;返回</a>');

                    $column->append(new Box('整体验收', $form->render()));
                });
            });
    }

    public function ys_check(Request $request)
    {
        $project = Project::find($request->project_id);

        if ($project->check_status != 3) {
            $error = new MessageBag([
                'title' => 'Error',
                'message' => '提交失败,审核状态错误....',
            ]);
            return back()->with(compact('error'));
        }


        $patron = Patron::where('project_id', $project->id)->first();
        Finance::create([
            'staff_id' => auth('admin')->user()->id,
            'customer_id' => $patron ? $patron->customer_id : 0,
            'project_id' => $project->id,
            'patron_id' => $patron ? $patron->id : 0,
            'status' => 4,
            'pact' => $request->get('pact'),
            'money' => $project->money,
            'returned_money' => $request->get('returned_money'),
            'rebate' => $request->get('rebate'),
            'returned_bag' => $request->get('returned_bag'),
            'debtors' => $request->get('debtors'),
            'description' => $request->get('description'),
            'remark' => $request->get('remark'),
        ]);


        $project->check_status = 4;
        $project->save();

        activity()->inLog(8)
            ->performedOn($project)
            ->causedBy(auth('admin')->user())
            ->withProperties([])
            ->log('更新' . $project->name . '状态为：整体验收成功');

        $lastLoggedActivity = Activity::all()->last();

        $staffs = Staff::where('is_notice', 1)->get();
        //执行消息分发
        dispatch(new \App\Jobs\SendNotice($staffs, new TopicReplied($lastLoggedActivity), 5));

        $success = new MessageBag([
            'title' => 'Success',
            'message' => '整体验收成功....',
        ]);
        return back()->with(compact('success'));
    }
}

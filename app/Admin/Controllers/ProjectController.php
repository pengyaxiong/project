<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Project\AddDemand;
use App\Admin\Actions\Project\Calendar;
use App\Admin\Actions\Project\QdCheck;
use App\Admin\Actions\Project\SjCheck;
use App\Admin\Actions\Project\YsCheck;
use App\Models\Customer;
use App\Models\Demand;
use App\Models\Finance;
use App\Models\Node;
use App\Models\Project;
use App\Models\ProjectCustomer;
use App\Models\ProjectNode;
use App\Models\ProjectNodeInfo;
use App\Models\ProjectStaff;
use App\Models\Staff;
use App\Models\Task;
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

class ProjectController extends AdminController
{
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

    public function __construct()
    {
        $this->grade = [1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E'];
        $this->status = [1 => '已立项', 2 => '进行中', 3 => '已暂停', 4 => '已结项'];
        $this->node_status = [1 => '未开始', 2 => '进行中', 3 => '已完成'];

        $this->check_status = [1 => '签约审核成功', 2 => '设计审核成功', 3 => '前端审核成功', 4 => '验收审核成功'];
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Project());

//        $projects=Project::all();
//        foreach ($projects as $project){
//            $result=ProjectNode::where('project_id',$project->id)->get()->map(function ($model){
//                $nodes = [
//                    'node_id' => $model->node_id,
//                    'staff_id' => $model->staff_id,
//                    'status' => $model->status,
//                    'start_time' => $model->start_time,
//                    'end_time' => $model->end_time,
//                    'content' => $model->content
//                ];
//              return $nodes;
//
//            });
//            Project::where('id',$project->id)->update([
//                'node'=>json_encode(array_values($result->toarray()))
//            ]);
//        }

        $grid->model()->orderBy('sort_order')->orderBy('contract_time', 'desc');
        $auth = auth('admin')->user();
        $slug = $auth->roles->pluck('slug')->toarray();

        if ($auth->id > 1 && !in_array('apply', $slug)) {
            $staff_id = Staff::where('admin_id', $auth->id)->first()->id;
            $project_ids = ProjectNode::where('staff_id', $staff_id)->pluck('project_id');
            $grid->model()->whereIn('id', $project_ids);
        }

        $grid->column('id', __('Id'));
        if ($auth->id > 1) {
            $grid->column('name', __('Name'));
        } else {
            $grid->column('name', __('Name'))->display(function () {
                return '<a href="/admin/projects/' . $this->id . '/edit">' . $this->name . '</a>';
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
        $grid->column('customer_name', '商务')->display(function () {
            $customer_ids = ProjectCustomer::where('project_id', $this->id)->pluck('customer_id')->toArray();
            $customer_name = Customer::wherein('id', $customer_ids)->pluck('name')->toArray();
            return $customer_name;
        })->map(function ($customer_name) {
            return $customer_name;
        })->implode(',');


        $grid->column('staff_name', '项目负责人')->display(function () {
            $staff_ids = ProjectStaff::where('project_id', $this->id)->pluck('staff_id')->toArray();
            $staff_name = Staff::wherein('id', $staff_ids)->pluck('name')->toArray();
            return $staff_name;
        })->map(function ($staff_name) {
            return $staff_name;
        })->implode(',');


        $grid->column('node', __('Node'))->display(function () {
            $html = [];
            $node = ProjectNode::with('nodes_info')->where('project_id', $this->id)->get()->toarray();
            foreach ($node as $k => $v) {
                if (!isset($v["staff_id"]) || !isset($v["days"])) {
                    continue;
                }
                $staff = Staff::find($v["staff_id"]);
                $node_ = Node::find($v["node_id"]);
                $name = isset($staff) ? $staff->name : '';
                $node_name = isset($node_) ? $node_->name : '';

                $html[] = '<span class="label" style="background-color: #00b7ee">' . $name . '</span><span class="label label-default">' . $node_name . $v["days"] . '天</span>';
            }
            return '查看';
            implode('&nbsp;', $html);
        })->expand(function ($model) {
            $project_nodes = ProjectNode::where('project_id', $model->id)->get()->map(function ($model) {
                $staff = Staff::find($model->staff_id);
                $node = Node::find($model->node_id);
                $staff_name = isset($staff) ? $staff->name : '';
                $node_name = isset($node) ? $node->name : '';

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

        if ($auth->id == 1 || in_array('apply', $slug)) {
            $grid->column('check_status', __('回款状态'))->using($this->check_status)->label([
                1 => 'default',
                2 => 'info',
                3 => 'warning',
                4 => 'danger',
            ])->expand(function ($model) {
                $check_status = [1 => '签约审核成功', 2 => '设计审核成功', 3 => '前端审核成功', 4 => '验收审核成功'];
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

            $grid->column('remark', __('Remark'))->width(288)->editable('textarea');
            $grid->column('money', __('Money'))->editable();
        }

        $grid->column('demands', __('新增需求'))->display(function ($model) {
            return empty($model) ? false : '查看';
        })->expand(function ($model) {

            $project_demands = Demand::where('project_id', $model->id)->get()->map(function ($model) {
                $pact = $model->pact?'<i class="fa fa-check text-green"></i>':'<i class="fa fa-close text-red"></i>';
                $status = $model->status?'<span class="label label-success">已审核</span>':'<span class="label label-danger">未审核</span>';
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
        });

        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');
        $states = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        if ($auth->id > 1) {
            $grid->column('is_check', __('是否交付'))->bool();
            $grid->column('check_time', __('交付时间'));
            $grid->column('y_check_time', __('预计交付时间'));
        } else {
            $grid->column('is_check', __('是否交付'))->switch($states);
            $grid->column('contract_time', __('Contract time'))->date('Y-m-d')->editable('combodate');
            $grid->column('check_time', __('交付时间'))->date('Y-m-d');
            $grid->column('y_check_time', __('预计交付时间'))->date('Y-m-d')->editable('combodate');
        }

        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();

        $grid->fixColumns(3, -1);

        $grid->filter(function ($filter) {

            $filter->like('name', __('Name'));
            $filter->between('contract_time', __('Contract time'))->date();
            $status_text = [
                1 => '交付',
                0 => '未交付'
            ];
            $filter->equal('is_check', __('是否交付'))->select($status_text);

            $filter->equal('check_status', __('回款状态'))->select([1 => '签约审核成功', 2 => '设计审核成功', 3 => '前端审核成功', 4 => '验收审核成功']);

            $filter->equal('grade', __('优先级'))->select($this->grade);
            $filter->equal('status', __('Status'))->select($this->status);

            $filter->where(function ($query) {

                $query->whereHas('customers', function ($query) {
                    $query->where('name', 'like', "%{$this->input}%");
                });

            }, '商务');

            $filter->where(function ($query) {

                $query->whereHas('staffs', function ($query) {
                    $query->where('name', 'like', "%{$this->input}%");
                });

            }, '项目负责人');

        });

        $grid->export(function ($export) {

            $export->filename('项目列表');

            $export->originalValue(['money', 'contract_time']);  //比如对列使用了$grid->column('name')->label()方法之后，那么导出的列内容会是一段HTML，如果需要某些列导出存在数据库中的原始内容，使用originalValue方法

            // $export->only(['name', 'nickname', 'sex']); //用来指定只能导出哪些列。

            $export->except(['sort_order', 'updated_at']); //用来指定哪些列不需要被导出
            $export->column('customer_name', function ($value, $original) {
                return $this->cutstr_html($value);
            });
            $export->column('staff_name', function ($value, $original) {
                return $this->cutstr_html($value);
            });
            $export->column('is_check', function ($value, $original) {
                switch ($original) {
                    case 1:
                        return '是';
                    default:
                        return '否';
                }
            });
        });
        if ($auth->id > 1) {
            #禁用创建按钮
            $grid->disableCreateButton();
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

    //去掉文本中的HTML标签
    public function cutstr_html($string, $length = 0, $ellipsis = '…')
    {
        $string = strip_tags($string);
        $string = preg_replace('/\n/is', '', $string);
        $string = preg_replace('/ |　/is', '', $string);
        $string = preg_replace('/ /is', '', $string);
        $string = preg_replace('/<br \/>([\S]*?)<br \/>/', '<p>$1<\/p>', $string);
        preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $string, $string);
        if (is_array($string) && !empty($string[0])) {
            if (is_numeric($length) && $length) {
                $string = join('', array_slice($string[0], 0, $length)) . $ellipsis;
            } else {
                $string = implode('', $string[0]);
            }
        } else {
            $string = '';
        }
        return $string;
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
        $show->field('node', __('Node'))->as(function () {
            $nodes = ProjectNode::where('project_id', $this->id)->get()->toarray();
            foreach ($nodes as $k => $v) {
                $staff = Staff::find($v['staff_id']);
                $node = Node::find($v['node_id']);
                $staff_name = isset($staff) ? $staff->name : '';
                $node_name = isset($node) ? $node->name : '';
                $nodes[$k] = [
                    'node_name' => $node_name,
                    'staff_name' => $staff_name,
                    'start_time' => $v['start_time'],
                    'end_time' => $v['end_time'],
                    'days' => $v['days'],
                    'content' => isset($v['content']) ? $v['content'] : '',
                ];
            }

            return new Table(['节点', '项目负责人', '开始时间', '结束时间', '耗时(天)', '详情'], $nodes);
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
        if ($auth->id == 1 || in_array('apply', $slug)) {
            $form->text('name', __('Name'))->rules('required');

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

                $form->multipleSelect('customers', __('商务'))
                    ->options($customers)->default($customer_ids);
                $form->multipleSelect('staffs', __('项目负责人'))
                    ->options($staffs)->default($staff_ids);
            } else {
                $form->multipleSelect('customers', __('商务'))
                    ->options($customers);
                $form->multipleSelect('staffs', __('项目负责人'))
                    ->options($staffs);
            }

            $form->radio('grade', '优先级')->options($this->grade)->default(1);
            $form->radio('status', __('Status'))->options($this->status)->default(1);


            $form->textarea('remark', __('Remark'));
        }
        $form->ueditor('content', __('Content'));
        $form->table('node', __('节点情况'), function ($table) {
            $staffs = Staff::all()->toArray();
            $select_staff = array_column($staffs, 'name', 'id');
            $table->select('staff_id', '项目负责人')->options($select_staff);

            $nodes = Node::where('is_project', true)->get()->toArray();
            $select_node = array_column($nodes, 'name', 'id');
            $table->select('node_id', '节点')->options($select_node);

            $table->select('status', __('Status'))->options($this->node_status);

            $table->datetime('start_time', '开始时间')->default(date('Y-m-d', time()));
            $table->datetime('end_time', '结束时间')->default(date('Y-m-d', time()));
            $table->textarea('content', '备注');
        });
        if ($auth->id == 1 || in_array('apply', $slug)) {
            $form->decimal('money', __('Money'))->default(0.00);

            $form->number('sort_order', __('Sort order'))->default(99);
            $states = [
                'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
            ];
            $form->switch('is_check', __('是否交付'))->states($states)->default(0);
            $form->datetime('contract_time', __('Contract time'));
            $form->datetime('check_time', __('交付时间'));
            $form->datetime('y_check_time', __('预计交付时间'));
        }
        //保存前回调
        $form->saving(function (Form $form) {

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
            $node = array_filter(\Request('node'));
//           dump($is_check);
//           exit();
            if (!empty($node)) {
                ProjectNode::where('project_id', $id)->delete();
                foreach ($node as $value) {
                    $project_node = ProjectNode::where('project_id', $id)->where('node_id', $value['node_id'])->where('staff_id', $value['staff_id'])->exists();
                    if (!$project_node) {
                        ProjectNode::create([
                            'staff_id' => $value['staff_id'],
                            'node_id' => $value['node_id'],
                            'status' => $value['status'],
                            'project_id' => $id,
                            'start_time' => $value['start_time'],
                            'end_time' => $value['end_time'],
                            'days' => $this->get_weekend_days($value['start_time'], $value['end_time']),
                            'content' => $value['content'],
                        ]);
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
        $project = Project::find($id);
        return $content
            ->title($project->name)
            ->description('Doing')
            ->row(function (Row $row) use ($project) {
                $row->column(12, function (Column $column) use ($project) {

                    $project_nodes = ProjectNode::where('project_id', $project->id)->get()->map(function ($model) {
                        $staff = Staff::find($model->staff_id);
                        $node = Node::find($model->node_id);
                        $staff_name = isset($staff) ? $staff->name : '';
                        $node_name = isset($node) ? $node->name : '';
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


}

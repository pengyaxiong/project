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
    protected $grade=[];
    protected $status=[];
    public function __construct()
    {

        $this->grade =[1=>'A',2=>'B',3=>'C',4=>'D',5=>'E'];
        $this->status =[1=>'已立项',2=>'进行中',3=>'已暂停',4=>'已结项'];
    }

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
        $grid->column('grade', __('优先级'))->using($this->grade)->label([
            1 => 'default',
            2 => 'info',
            3 => 'warning',
            4 => 'success',
            5 => 'danger',
        ]);
        $grid->column('status', __('Status'))->using($this->status)->label([
            1 => 'info',
            2 => 'success',
            3 => 'danger',
            4 => 'default',
        ]);
        $grid->column('node', __('Node'))->display(function ($node) {
            $html=[];
            foreach ($node as $k=>$v){
                $name=Staff::find($v["staff_id"])->name;
                $html[]='<span class="label label-success">'.$name.'</span><span class="label label-danger">'.$v["days"].'天</span>';
            }
            return implode('&nbsp;',$html);
        });
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

        $grid->column('days', __('总天数'))->display(function ($days){
            $result=ProjectNode::where('project_id',$this->id)->sum('days');
            return $result;
        });

        $grid->column('remark', __('Remark'));
        $grid->column('money', __('Money'))->editable();
        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');
        $states = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $grid->column('is_check', __('是否交付'))->switch($states);

        $grid->column('contract_time', __('Contract time'))->editable('datetime');
        $grid->column('check_time', __('交付时间'));
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();


        $grid->filter(function ($filter) {
            $filter->like('name', __('Name'));
            $filter->between('contract_time', __('Contract time'))->date();
            $status_text = [
                1 => '交付',
                0 => '未交付'
            ];
            $filter->equal('is_check', __('是否交付'))->select($status_text);

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

        $grid->export(function ($export) {

            $export->filename('项目列表');

            $export->originalValue(['money','contract_time']);  //比如对列使用了$grid->column('name')->label()方法之后，那么导出的列内容会是一段HTML，如果需要某些列导出存在数据库中的原始内容，使用originalValue方法

            // $export->only(['name', 'nickname', 'sex']); //用来指定只能导出哪些列。

            $export->except(['sort_order', 'updated_at' ]); //用来指定哪些列不需要被导出
            $export->column('customer_name', function ($value, $original) {
                return $this->cutstr_html($value);
            });
            $export->column('staff_name', function ($value, $original) {
                return $this->cutstr_html($value);
            });
            $export->column('is_check', function ($value, $original) {
                switch ($original){
                    case 1:
                        return '是';
                    default:
                        return '否';
                }
            });
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
        $string = preg_replace('/<br \/>([\S]*?)<br \/>/','<p>$1<\/p>',$string);
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
        $show->field('company.name', __('所属公司'));
        $show->field('task.name', __('任务名称'));
        $show->field('grade', __('优先级'))->using($this->grade);
        $show->field('status', __('Status'))->using($this->status);
        $show->field('node', __('Node'))->as(function ($node) {
            return json_encode($node);
        });
        $show->field('content', __('Content'));
        $show->field('remark', __('Remark'));
        $show->field('money', __('Money'));
        $show->field('sort_order', __('Sort order'));
        $show->field('is_check', __('是否交付'));
        $show->field('contract_time', __('Contract time'));
        $show->field('check_time', __('交付时间'));
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

        $form->radio('grade', '优先级')->options($this->grade)->default(1);
        $form->radio('status', __('Status'))->options($this->status)->default(1);

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
        $form->switch('is_check', __('是否交付'))->states($states)->default(0);
        $form->datetime('contract_time', __('Contract time'));
        $form->datetime('check_time', __('交付时间'));

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

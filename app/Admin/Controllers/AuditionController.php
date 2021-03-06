<?php

namespace App\Admin\Controllers;

use App\Imports\AuditionImport;
use App\Models\Audition;
use App\Models\Department;
use App\Models\Staff;
use App\Notifications\TopicReplied;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Spatie\Activitylog\Models\Activity;

class AuditionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '面试邀约';
    protected $status = [];

    public function __construct()
    {
        $this->status = [0 => '拒绝', 1 => '通过', 2 => '保留'];
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Audition());

        $grid->column('id', __('Id'));
        $grid->column('department.name', __('所属部门'));
        $grid->column('staff.name', __('面试官'));
        $grid->column('name', __('Name'));
        $grid->column('job', __('Job'));
        $grid->column('point', __('分数'));
        $grid->column('phone', __('Phone'));
        $grid->column('money', __('期望薪资'))->sortable()->editable();
        $grid->column('status', __('Status'))->editable('select', $this->status)->dot([
            0 => 'danger',
            1 => 'success',
            2 => 'info',
        ]);
        $grid->column('images', __('Images'))->carousel();
        $grid->column('remark', __('Remark'))->limit(10);
        $grid->column('start_time', __('面试时间'));
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();


        $grid->exporter(new AuditionImport());

        $grid->filter(function ($filter) {

            $departments = Department::all()->toArray();
            $department_array = array_column($departments, 'name', 'id');

            $filter->equal('department_id', __('所属部门'))->select($department_array);

            $staffs = Staff::all()->toArray();
            $staff_array = array_column($staffs, 'name', 'id');

            $filter->equal('staff_id', __('面试官'))->select($staff_array);

            $filter->like('name', __('Name'));
            $filter->like('phone', __('Phone'));
            $filter->between('start_time', __('面试时间'))->date();
            $filter->between('point', __('分数'));
            $filter->equal('status', __('Status'))->radio($this->status);
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
        $show = new Show(Audition::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('department.name', __('所属部门'));
        $show->field('staff.name', __('面试官'));
        $show->field('name', __('Name'));
        $show->field('job', __('Job'));
        $show->field('point', __('分数'));
        $show->field('phone', __('Phone'));
        $show->field('money', __('期望薪资'));
        $show->field('status', __('Status'));
        $show->field('images', __('Images'))->carousel();
        $show->field('remark', __('Remark'));
        $show->field('start_time', __('面试时间'));
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
        $form = new Form(new Audition());

        $departments = Department::all()->toArray();
        $department_array = array_column($departments, 'name', 'id');

        $form->select('department_id', __('所属部门'))->options($department_array);

        $staffs = Staff::all()->toArray();
        $staff_array = array_column($staffs, 'name', 'id');

        $form->select('staff_id', __('面试官'))->options($staff_array);

        $form->text('name', __('Name'));
        $form->text('job', __('Job'));
        $form->number('point', __('分数'))->default(100);
        $form->text('phone', __('Phone'));
        $form->decimal('money', __('期望薪资'))->default(1000.00);
        $form->radio('status', __('Status'))->options($this->status)->default(0);
        $form->multipleImage('images', __('Images'))->removable()->sortable()->help('简历、作品展示');
        $form->textarea('remark', __('Remark'));
        $form->datetime('start_time', __('面试时间'))->default(date('Y-m-d H:i:s'));

        //保存后回调
        $form->saved(function (Form $form) {

            activity()->inLog(1)
                ->performedOn($form->model())
                ->causedBy(auth('admin')->user())
                ->withProperties([])
                ->log('更新' . $form->model()->name . '状态为：' . $this->status[$form->model()->status]);
            $lastLoggedActivity = Activity::all()->last();

            $staffs = Staff::where('is_notice', 1)->get();
            //执行消息分发
            dispatch(new \App\Jobs\SendNotice($staffs, new TopicReplied($lastLoggedActivity), 5));
            //SendMessage::dispatch($notice)

        });

        return $form;
    }


}

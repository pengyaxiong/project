<?php

namespace App\Admin\Controllers;

use App\Models\Department;
use App\Models\Notice;
use App\Models\Staff;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

class NoticeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '公告管理';
    protected $departments = [];

    public function __construct()
    {
        $departments = Department::orderBy('sort_order')->get()->toArray();
        $select_ = array_prepend($departments, ['id' => 0, 'name' => '所有人']);
        $this->departments = array_column($select_, 'name', 'id');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $auth = auth('admin')->user();
        $slug = $auth->roles->pluck('slug')->toarray();
        $grid = new Grid(new Notice());
        if ($auth->id > 1 && !in_array('auditions', $slug)) {
            $staff = Staff::where('admin_id', $auth->id)->first();
            $grid->model()->where('department_id', $staff->department_id)->orwhere('department_id', 0)->orderBy('sort_order');
        } else {
            $grid->model()->orderBy('sort_order');
        }
        $grid->column('id', __('Id'));
        $grid->column('title', __('Title'));
        $grid->column('department_id', __('可见'))->using($this->departments);
        $grid->column('description', __('Description'))->limit(10);
        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'))->hide();

        $grid->filter(function ($filter) {

            $filter->like('title', __('Title'));

            $filter->equal('department_id', __('可见'))->select($this->departments);

            $filter->between('start_time', __('开始时间'))->date();

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
        $show = new Show(Notice::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('department_id', __('可见'));
        $show->field('description', __('Description'));
        $show->field('sort_order', __('Sort order'));
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
        $form = new Form(new Notice());

        $form->text('title', __('Title'))->rules('required');
        $form->select('department_id', __('可见'))->options($this->departments);
        $form->textarea('description', __('Description'));
        $form->number('sort_order', __('Sort order'))->default(99);
        return $form;
    }
}

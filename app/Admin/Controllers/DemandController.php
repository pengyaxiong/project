<?php

namespace App\Admin\Controllers;

use App\Models\Demand;
use App\Models\Project;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DemandController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '新增需求';
    protected $status = [];

    public function __construct()
    {
        $this->status = [0 => '未审核', 1 => '已审核'];
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Demand());

        $grid->column('id', __('Id'));
        $grid->column('project_id', __('项目名称'))->display(function () {
            return "<a href='/admin/projects/'  .$this->project_id . '>" . $this->project->name . "</a>";
        });
        $grid->column('pact', __('合同（有/无）'))->bool();
        $grid->column('money', __('Money'));

        $states = [
            'on' => ['value' => 1, 'text' => '已审核', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '未审核', 'color' => 'danger'],
        ];
        $grid->column('status', __('Status'))->switch($states);
        $grid->column('description', __('Description'));
        $grid->column('remark', __('Remark'))->width(288)->editable('textarea');
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();

        $grid->filter(function ($filter) {
            $projects = Project::all()->toArray();
            $project_array = array_column($projects, 'name', 'id');
            //创建select
            $filter->equal('project_id', '项目名称')->select($project_array);

            $filter->between('created_at', __('Created at'))->date();
            $filter->equal('status', __('Status'))->radio($this->status);
            $status_text = [
                1 => '有',
                0 => '无'
            ];
            $filter->equal('pact', __('合同（有/无）'))->radio($status_text);
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
        $show = new Show(Demand::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('project_id', __('Project id'));
        $show->field('pact', __('Pact'));
        $show->field('money', __('Money'));
        $show->field('status', __('Status'));
        $show->field('description', __('Description'));
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
        $form = new Form(new Demand());

        $projects = Project::all()->toArray();
        $project_array = array_column($projects, 'name', 'id');
        //创建select
        $form->select('project_id', '项目名称')->options($project_array);

        $form->radio('pact', __('合同（有/无）'))->options([1 => '有', 0 => '无'])->default(1);

        $form->decimal('money', __('Money'))->default(1000.00);

        $states = [
            'on' => ['value' => 1, 'text' => '已审核', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '未审核', 'color' => 'danger'],
        ];
        $form->switch('status', __('Status'))->states($states);
        $form->textarea('description', __('Description'));
        $form->textarea('remark', __('Remark'));


        //保存后回调
        $form->saved(function (Form $form) {
            activity()->inLog(7)
                ->performedOn($form->model())
                ->causedBy(auth('admin')->user())
                ->withProperties(['description'=>$form->model()->description,'remark'=>$form->model()->remark])
                ->log('更新状态为：'.$this->status[$form->model()->status]);
        });
        return $form;
    }
}

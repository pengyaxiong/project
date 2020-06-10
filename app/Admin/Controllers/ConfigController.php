<?php

namespace App\Admin\Controllers;

use App\Models\Config;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ConfigController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {

        $this->title =__('Config', [], app()->getLocale());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Config());

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        // 设置text、color、和存储值
        $states = [
            'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $grid->column('show_customer', __('显示客户'))->switch($states);
        $grid->column('show_group', __('显示成员'))->switch($states);
        $grid->column('address', __('Address'))->hide();
        $grid->column('email', __('Email'));
        $grid->column('tel', __('Tel'));
        $grid->column('copyright', __('Copyright'))->hide();
        $grid->column('icp', __('Icp'))->hide();
        $grid->column('description', __('Description'))->hide();
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();

        //禁用创建按钮
        $grid->disableCreateButton();

        $grid->actions(function ($actions) {
            $actions->disableView();
            //  $actions->disableEdit();
            $actions->disableDelete();
        });

        $grid->tools(function ($tools) {
            // 禁用批量删除按钮
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
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
        $show = new Show(Config::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('address', __('Address'));
        $show->field('email', __('Email'));
        $show->field('tel', __('Tel'));
        $show->field('copyright', __('Copyright'));
        $show->field('icp', __('Icp'));
        $show->field('description', __('Description'));
        $show->field('地图')->latlong('lat', 'lng', $height = 400, $zoom = 16);
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
        $form = new Form(new Config());

        $form->text('name', __('Name'))->rules('required');
        $form->image('image', __('Image'))->rules('required|image');
        $form->text('address', __('Address'))->rules('required');

        $form->latlong('lat', 'lng', '地图')->default(['lat' => 114.3679, 'lng' => 30.5214]);

        $form->email('email', __('Email'))->rules('required');
        $form->text('tel', __('Tel'))->rules('required');
        $form->text('copyright', __('Copyright'))->rules('required');
        $form->text('icp', __('Icp'))->rules('required');
        $form->textarea('description', __('Description'))->rules('required');
        $form->ueditor('content', __('Content'))->rules('required');

        $states = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $form->switch('show_customer', __('显示客户'))->states($states)->default(1)->rules('required');
        $form->switch('show_group', __('显示成员'))->states($states)->default(1)->rules('required');
        return $form;
    }
}

<?php

namespace App\Admin\Controllers;

use App\Models\Customer;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CustomerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '客户管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Customer());

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'))->editable();
        $grid->column('openid', __('Openid'))->copyable();
        $grid->column('nickname', __('Nickname'))->copyable();
        $grid->column('headimgurl', __('Headimgurl'))->image();
        $grid->column('sex', __('Sex'))->using([
            1 => '男',
            2 => '女',
            0 => '其它',
        ], '未知')->dot([
            1 => 'primary',
            2 => 'danger',
            0 => 'success',
        ], 'warning');
        $grid->column('language', __('Language'))->hide();
        $grid->column('tel', __('Tel'))->editable();
        $grid->column('country', __('Country'))->hide();
        $grid->column('province', __('Province'))->hide();
        $grid->column('city', __('City'))->hide();
        $grid->column('email', __('Email'))->hide();
        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');
        $grid->column('remark', __('Remark'))->editable();
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        //禁用创建按钮
      //  $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableView();
         //   $actions->disableEdit();
            $actions->disableDelete();
        });

        $grid->filter(function ($filter) {
            $filter->like('nickname', '微信昵称');
            $filter->like('openid', 'OpenId');
            $filter->like('name', __('Name'));
            $filter->like('tel', __('Tel'));
            $status_text = [
                1 => '男',
                2 => '女',
                0 => '其它'
            ];
            $filter->equal('sex', __('Sex'))->select($status_text);
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
        $show = new Show(Customer::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('openid', __('Openid'));
        $show->field('sex', __('Sex'));
        $show->field('language', __('Language'));
        $show->field('nickname', __('Nickname'));
        $show->field('headimgurl', __('Headimgurl'));
        $show->field('tel', __('Tel'));
        $show->field('country', __('Country'));
        $show->field('province', __('Province'));
        $show->field('city', __('City'));
        $show->field('email', __('Email'));
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
        $form = new Form(new Customer());

        $form->text('name', __('Name'));
        $form->text('openid', __('Openid'));
        $form->select('sex', __('Sex'))->options([1=>'男',2=>'女',0=>'保密']);
        $form->text('language', __('Language'));
        $form->text('nickname', __('Nickname'));
        $form->text('headimgurl', __('Headimgurl'));
        $form->text('tel', __('Tel'));
        $form->text('country', __('Country'));
        $form->text('province', __('Province'));
        $form->text('city', __('City'));
        $form->email('email', __('Email'));
        $form->number('sort_order', __('Sort order'))->default(99);
        $form->textarea('remark', __('Remark'));

        //保存后回调
        $form->saved(function (Form $form) {


        });

        //保存前回调
        $form->saving(function (Form $form) {
            $customer = $form->model();

        });

        return $form;
    }
}

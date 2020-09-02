<?php

namespace App\Admin\Controllers;

use App\Models\Customer;
use App\Models\Patron;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

class CustomerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商务管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Customer());

        $grid->model()->where('parent_id', 0);

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'))->editable();
        $states = [
            'on' => ['value' => 1, 'text' => '正常', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '禁用', 'color' => 'danger'],
        ];
        $grid->column('status', __('Status'))->switch($states);
        $grid->column('parent_id', '组员')->display(function () {
            return '查看';
        })->expand(function ($model) {
            $children = $model->children->map(function ($child) {
                return $child->only(['id', 'name', 'tel', 'remark']);
            });
            $array = $children->toArray();
            foreach ($array as $k => $v) {
                $url = route('admin.customers.edit', $v['id']);
                $array[$k]['edit'] = '<div class="btn">
              <a class=""  href="' . $url . '" rel="external" >
              <i class="fa fa-edit"></i> 编辑</a>
                 </div><div class="btn">
                 </div>';
            }

            return new Table(['ID', __('名称'), __('Tel'), __('Remark'), '操作'], $array);
        });
        $grid->column('patrons', __('客户列表'))->display(function () {
            return '查看';
        })->expand(function ($model) {

            $patrons = Patron::with('customer')->where('customer_id',$model->id)->orwherein('customer_id',$model->children->pluck('id'))->get()->map(function ($model) {
                $from_arr = [0 => '线上', 1 => '线下', 2 => '其它'];
                $need_arr = [0 => 'APP', 1 => '小程序', 2 => '网站', 3 => '系统软件', 4 => '其它'];
                $result = [
                    'id' => $model->id,
                    'customer' => $model->customer->name,
                    'from' => $from_arr[$model->from],
                    'company_name' => $model->company_name,
                    'name' => $model->name,
                    'phone' => $model->phone,
                    'job' => $model->job,
                    'need' => $need_arr[$model->need],
                    'money' => $model->money,
                    'des' => '<a target="_blank" href="/admin/patrons/' . $model->id . '/edit">查看</a>',
                ];
                return $result;
            });
            return new Table(['ID', '录入者', '信息来源', ' 公司名称', '客户姓名', '客户电话', '客户职位', '需求', '预算', '详情'], $patrons->toArray());
        });
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

            $status_text = [
                1 => '正常',
                0 => '禁用'
            ];
            $filter->equal('status', __('Status'))->select($status_text);
        });


        $grid->export(function ($export) {

            $export->filename('客户列表');

            $export->originalValue(['name', 'openid', 'nickname', 'headimgurl', 'tel', 'remark']);  //比如对列使用了$grid->column('name')->label()方法之后，那么导出的列内容会是一段HTML，如果需要某些列导出存在数据库中的原始内容，使用originalValue方法

            // $export->only(['name', 'nickname', 'sex']); //用来指定只能导出哪些列。

            $export->except(['sort_order', 'updated_at']); //用来指定哪些列不需要被导出

            $export->column('sex', function ($value, $original) {
                switch ($original) {
                    case 1:
                        return '男';
                    case 2:
                        return '女';
                    default:
                        return '其它';
                }
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

        $parents = Customer::where('parent_id', 0)->get()->toArray();
        $select_ = array_prepend($parents, ['id' => 0, 'name' => '顶级（组长）']);
        $select_array = array_column($select_, 'name', 'id');
        //创建select
        $form->select('parent_id', '上级')->options($select_array);


        $states = [
            'on' => ['value' => 1, 'text' => '正常', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '禁用', 'color' => 'danger'],
        ];
        $form->switch('status', __('Status'))->states($states)->default(0);
        $form->text('openid', __('Openid'));
        $form->select('sex', __('Sex'))->options([1 => '男', 2 => '女', 0 => '保密']);
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

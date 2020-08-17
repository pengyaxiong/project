<?php

namespace App\Admin\Controllers;

use App\Models\Customer;
use App\Models\Patron;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
class PatronController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '客户资讯';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Patron());

        $grid->column('id', __('Id'));
        $grid->column('customer.name', __('所属商务'));
        $grid->column('from', __('From'))->using([
            0 => '线上',
            1 => '线下',
            2 => '其它',
        ], '其它')->dot([
            0 => 'primary',
            1 => 'success',
            2 => 'info',
        ], 'info');
        $grid->column('company_name', __('Company name'));
        $grid->column('name', __('Name'));
        $grid->column('phone', __('Phone'));
        $grid->column('job', __('Job'));
        $grid->column('need', __('Need'))->using([
            0=>'APP',1=>'小程序',2=>'网站',3=>'系统软件',4=>'其它'
        ])->label([
            0 => 'info',
            1 => 'info',
            2 => 'info',
            3 => 'info',
            4 => 'primary',
        ]);
        $grid->column('money', __('预算'));

        $grid->column('status', __('Status'))->using([
            0=>'待签约',1=>'已签约'
        ])->display(function () {
            $status = $this->status;
            switch ($status) {
                case 0:
                    return '<span class="label" style="font-weight:unset; color: #444; background-color: #f0ad1499"><i class="fa fa-plus-circle"></i>&nbsp;待签约</span>';
                case 1:
                    return '<span class="label" style="font-weight:unset; color: #444; background-color: #8EFFB9"><i class="fa fa-paper-plane-o"></i>&nbsp;已签约</span>';
            }
        });
        $grid->column('start_time', __('开始时间'));
        $grid->column('relation', __('客户关系'));
        $grid->column('follow', __('跟进记录'));
        $grid->column('remark', __('Remark'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        $grid->filter(function ($filter) {

            $customers = Customer::all()->toArray();
            $select_ = array_prepend($customers, ['id' => 0, 'name' => '公有池']);
            $customer_array = array_column($select_, 'name', 'id');
            //创建select
            $filter->equal('customer_id', '所属商务')->select($customer_array);

            $filter->like('name', __('Name'));
            $filter->like('phone', __('Phone'));
            $filter->between('start_time', __('开始时间'))->date();
            $status_text = [
                0 => '线上',
                1 => '线下',
                2 => '其它'
            ];
            $filter->equal('from', __('From'))->select($status_text);
            $status_text = [
                1 => '已签约',
                0 => '待签约'
            ];
            $filter->equal('status', __('Status'))->select($status_text);
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
        $show = new Show(Patron::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('customer_id', __('所属商务'));
        $show->field('from', __('From'));
        $show->field('company_name', __('Company name'));
        $show->field('name', __('Name'));
        $show->field('phone', __('Phone'));
        $show->field('job', __('Job'));
        $show->field('need', __('Need'));
        $show->field('money', __('预算'));
        $show->field('status', __('Status'));
        $show->field('start_time', __('开始时间'));
        $show->field('relation', __('客户关系'));
        $show->field('follow', __('跟进记录'))->as(function ($follow) {
            foreach ($follow as $k => $v) {
                $follow[$k] = [
                    'time' => $v['time'],
                    'content' => isset($v['content']) ? $v['content'] : '',
                ];
            }

            return new Table(['时间', '详情'], $follow);
        });
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
        $form = new Form(new Patron());

        $customers = Customer::all()->toArray();
        $select_ = array_prepend($customers, ['id' => 0, 'name' => '公有池']);
        $customer_array = array_column($select_, 'name', 'id');
        //创建select
        $form->select('customer_id', '所属商务')->options($customer_array);

        $form->select('from', __('From'))->options([0=>'线上',1=>'线下',2=>'其它']);
        $form->text('company_name', __('Company name'));
        $form->text('name', __('Name'));
        $form->text('phone', __('Phone'));
        $form->text('job', __('Job'));
        $form->select('need', __('Need'))->options([0=>'APP',1=>'小程序',2=>'网站',3=>'系统软件',4=>'其它']);
        $form->decimal('money', __('预算'))->default(1000.00);
        $states = [
            'on' => ['value' => 1, 'text' => '已签约', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '待签约', 'color' => 'danger'],
        ];
        $form->switch('status', __('Status'))->states($states)->default(0);
        $form->datetime('start_time', __('开始时间'))->default(date('Y-m-d H:i:s'));
        $form->textarea('relation', __('客户关系'));
        $form->table('follow', __('跟进记录'), function ($table) {
            $table->datetime('time', '时间')->default(date('Y-m-d', time()));
            $table->textarea('content', '跟进内容');
        });
        $form->textarea('remark', __('Remark'));

        return $form;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/14
 * Time: 19:13
 */

namespace App\Admin\Extensions\Button;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;

class FinanceStatistics extends AbstractTool
{

    protected function script()
    {
        return <<<SCRIPT
        $("#finance_statistics").click(function(){
                    window.location.href = '/admin/finance_statistics'
                });
SCRIPT;
    }

    public function render()
    {
        Admin::script($this->script());
        return view('admin.tools.finance_statistics');
    }
}
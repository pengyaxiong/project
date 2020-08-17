<?php

namespace App\Admin\Actions\Project;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Calendar extends RowAction
{
    public $name = '日历图';

    public function handle(Model $model)
    {
        // $model ...

        return $this->response()->redirect('/admin/calendar/' . $model->id);
    }


}
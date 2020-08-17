<?php

namespace App\Admin\Actions\Patron;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class PatronBatchCheck extends BatchAction
{
    public $name = '签约批量审核';

    public function handle(Collection $collection)
    {
        foreach ($collection as $model) {
            // ...
        }

        return $this->response()->success('Success message...')->refresh();
    }

}
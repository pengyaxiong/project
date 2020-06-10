<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'wechat_customer';

    public function projects()
    {
        return $this->belongsToMany(Project::class,'wechat_project_customer','project_id','customer_id')->withPivot(
            'project_id',
            'customer_id'
        );
    }

}

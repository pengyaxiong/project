<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectCustomer extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'wechat_project_customer';

    public $timestamps = false;

    public function projects()
    {
        return $this->hasMany(Project::class,'project_id');
    }
}

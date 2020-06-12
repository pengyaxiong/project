<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'wechat_department';

    public $timestamps = false;

    public function staffs()
    {
        return $this->hasMany(Staff::class,'department_id');
    }

    public function nodes()
    {
        return $this->hasMany(Node::class,'department_id');
    }
}

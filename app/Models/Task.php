<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'wechat_task';

    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

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

    public function principal()
    {
        return $this->belongsTo(Staff::class);
    }

    public function access()
    {
        return $this->belongsTo(Staff::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

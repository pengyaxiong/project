<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'wechat_node';


    public $timestamps = false;

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class,'wechat_project_node','project_id','node_id')->withPivot(
            'project_id',
            'staff_id',
            'start_time',
            'end_time',
            'days',
            'content',
            'node_id'
        );
    }
}

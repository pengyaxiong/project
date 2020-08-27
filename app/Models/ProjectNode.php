<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectNode extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'wechat_project_node';

    public $timestamps = false;

    public function project()
    {
        return $this->belongsTo(Project::class,'project_id');
    }

    public function node()
    {
        return $this->belongsTo(Node::class,'node_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class,'staff_id');
    }

    public function nodes_info()
    {
        return $this->hasMany(ProjectNodeInfo::class,'project_node_id');
    }
}

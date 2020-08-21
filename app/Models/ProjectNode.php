<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectNode extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'wechat_project_node';

    public $timestamps = false;

    public function projects()
    {
        return $this->hasMany(Project::class,'project_id');
    }

    public function nodes()
    {
        return $this->hasMany(Node::class,'node_id');
    }

    public function nodes_info()
    {
        return $this->hasMany(ProjectNodeInfo::class,'project_node_id');
    }
}

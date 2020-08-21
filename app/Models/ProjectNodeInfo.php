<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectNodeInfo extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'wechat_project_node_info';

    public function project_node()
    {
        return $this->belongsTo(ProjectNode::class);
    }
}

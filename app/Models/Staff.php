<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'wechat_staff';

    public function projects()
    {
        return $this->belongsToMany(Project::class,'wechat_project_staff','project_id','staff_id')->withPivot(
            'project_id',
            'staff_id'
        );
    }

    public function admin()
    {
        return $this->belongsTo(config('admin.database.users_model'));
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function dailies()
    {
        return $this->hasMany(Daily::class,'daily_id');
    }

    public function project_nodes()
    {
        return $this->hasMany(ProjectNode::class);
    }
}

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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function principal_tasks()
    {
        return $this->hasMany(Task::class,'principal_id');
    }

    public function access_tasks()
    {
        return $this->hasMany(Task::class,'access_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    //黑名单为空
    protected $guarded = ['staffs','customers'];
    protected $fillable = [];
    protected $table = 'wechat_project';

    public function getNodeAttribute($node)
    {
        return array_values(json_decode($node, true) ?: []);
    }

    public function setNodeAttribute($node)
    {
        $this->attributes['node'] = json_encode(array_values($node));
    }

    public static function boot()
    {
        parent::boot();

        static::deleted(function ($model)
        {
            //这样可以拿到当前操作id
            $project_id=$model->id;
            ProjectNode::where('project_id',$project_id)->delete();
            ProjectStaff::where('project_id',$project_id)->delete();
        });
    }

    public function staffs()
    {
        return $this->belongsToMany(Staff::class,'wechat_project_staff','project_id','staff_id')->withPivot(
            'staff_id',
            'project_id'
        );
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function project_nodes()
    {
        return $this->hasMany(ProjectNode::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function patron()
    {
        return $this->belongsTo(Patron::class);
    }

    public function finances()
    {
        return $this->hasMany(Finance::class,'project_id');
    }


    public function demands()
    {
        return $this->hasMany(Demand::class,'project_id');
    }

    public function design_checks()
    {
        return $this->hasMany(DesignCheck::class,'project_id');
    }

    public function html_checks()
    {
        return $this->hasMany(HtmlCheck::class,'project_id');
    }
}

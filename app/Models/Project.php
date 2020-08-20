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
            ProjectCustomer::where('project_id',$project_id)->delete();
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

    public function customers()
    {
        return $this->belongsToMany(Customer::class,'wechat_project_customer','project_id','customer_id')->withPivot(
            'customer_id',
            'project_id'
        );
    }

    public function nodes()
    {
        return $this->belongsToMany(Node::class,'wechat_project_node','project_id','node_id')->withPivot(
            'node_id',
            'staff_id',
            'start_time',
            'end_time',
            'days',
            'content',
            'project_id'
        );
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function finances()
    {
        return $this->hasMany(Finance::class,'project_id');
    }


}

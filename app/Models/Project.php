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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}

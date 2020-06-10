<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'wechat_company';


    public $timestamps = false;


    public function staffs()
    {
        return $this->hasMany(Staff::class,'company_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class,'company_id');
    }
}

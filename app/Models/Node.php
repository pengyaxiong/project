<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'wechat_node';


    public $timestamps = false;
}

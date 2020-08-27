<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Demand extends Model
{
    protected $guarded = [];

    protected $table = 'wechat_demand';

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audition extends Model
{
    protected $guarded = [];

    protected $table = 'wechat_audition';

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}

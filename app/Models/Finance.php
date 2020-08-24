<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Finance extends Model
{
    protected $guarded = [];

    protected $table = 'wechat_finance';

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function patron()
    {
        return $this->belongsTo(Patron::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}

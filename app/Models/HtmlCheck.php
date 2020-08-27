<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HtmlCheck extends Model
{
    protected $guarded = [];

    protected $table = 'wechat_html_check';

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}

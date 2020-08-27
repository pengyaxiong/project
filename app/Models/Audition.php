<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audition extends Model
{
    protected $guarded = [];

    protected $table = 'wechat_audition';

    public function getImagesAttribute($images)
    {
        return array_values(json_decode($images, true) ?: []);
    }

    public function setImagesAttribute($images)
    {
        $this->attributes['images'] = json_encode(array_values($images));
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}

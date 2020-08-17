<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patron extends Model
{
    protected $guarded = [];

    protected $table = 'wechat_patron';

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getFollowAttribute($follow)
    {
        return array_values(json_decode($follow, true) ?: []);
    }

    public function setFollowAttribute($follow)
    {
        $this->attributes['follow'] = json_encode(array_values($follow));
    }

}

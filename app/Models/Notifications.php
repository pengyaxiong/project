<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    protected $guarded = [];

    protected $table = 'notifications';

    public function getDataAttribute($data)
    {
        return json_decode($data, true) ?: [];
    }
}

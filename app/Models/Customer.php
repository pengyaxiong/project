<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
class Customer extends Authenticatable
{
    use Notifiable;
    //黑名单为空
//    protected $guarded = [];
    protected $table = 'wechat_customer';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email','status', 'password','openid', 'nickname', 'sex', 'language', 'city', 'province', 'country', 'headimgurl','tel','sort_order','remark'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'wechat_project_customer', 'project_id', 'customer_id')->withPivot(
            'project_id',
            'customer_id'
        );
    }

    public function patrons()
    {
        return $this->hasMany(Patron::class);
    }


    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub
        static::saving(function ($model){
            if (empty($model->password)){
                $model->password=Hash::make(123456);
            }
        });
    }
}

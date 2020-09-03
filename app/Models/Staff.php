<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Staff extends Authenticatable
{
    //别名
    use Notifiable {
        notify as protected laravelNotify;
    }

    public function notify($instance)
    {
        // 如果要通知的人是当前用户，就不必通知了！
//        $admin = auth('admin')->user();
//        if ($this->admin_id == $admin->id) {
//            return;
//        }
        $this->laravelNotify($instance);// 发送通知
        // 只有数据库类型通知才需提醒，直接发送 Email 或者其他的都 Pass
        if (method_exists($instance, 'toDatabase')) {
            $this->increment('notification_count'); //字段加1
        }


    }

    /*
     * $user->unreadNotifications; // 获取所有未读通知
        $user->readNotifications; // 获取所有已读通知
        $user->notifications; // 获取所有通知
     */
    public function markAsRead()
    {
        $this->notification_count = 0;
        $this->save();
        $this->unreadNotifications->markAsRead();
        //批量更新方式标记通知为已读
       // $this->unreadNotifications()->update(['read_at' => Carbon::now()]);
    }

    //黑名单为空
    protected $guarded = [];
    protected $table = 'wechat_staff';

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'wechat_project_staff', 'project_id', 'staff_id')->withPivot(
            'project_id',
            'staff_id'
        );
    }

    public function admin()
    {
        return $this->belongsTo(config('admin.database.users_model'));
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function dailies()
    {
        return $this->hasMany(Daily::class, 'daily_id');
    }

    public function project_nodes()
    {
        return $this->hasMany(ProjectNode::class);
    }
}

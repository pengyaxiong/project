<?php

namespace App\Notifications;

use App\Models\Customer;
use App\Models\Staff;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Spatie\Activitylog\Models\Activity;

class TopicReplied extends Notification
{
    use Queueable;

    public $activity;

    public function __construct(Activity $activity)
    {
        // 注入回复实体，方便 toDatabase 方法中的使用
        $this->activity = $activity;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // return ['mail'];
        // 开启通知的频道
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        if ($this->activity->causer_type == 'App\Models\Customer') {
            $staff = Customer::find($this->activity->causer_id);
        } else {
            $staff = Staff::where('admin_id', $this->activity->causer_id)->first();
        }
        $arr = [1 => '面试审核', 2 => '签约审核', 3 => '设计评审', 4 => '设计验收', 5 => '前端评审', 6 => '前端验收', 7 => '新增需求审核', 8 => '整体验收'];
        // 存入数据库里的数据
        return [
            'title' => $arr[$this->activity->log_name],
            'description' => $this->activity->description,
            'admin_id' => $this->activity->causer_id,
            'name' => $staff->name,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if ($this->activity->causer_type == 'App\Models\Customer') {
            $staff = Customer::find($this->activity->causer_id);
        } else {
            $staff = Staff::where('admin_id', $this->activity->causer_id)->first();
        }
        $arr = [1 => '面试审核', 2 => '签约审核', 3 => '设计评审', 4 => '设计验收', 5 => '前端评审', 6 => '前端验收', 7 => '新增需求审核', 8 => '整体验收'];
        return (new MailMessage)
            ->subject('消息通知')
            ->greeting($arr[$this->activity->log_name])
            ->line($staff->name . '' . $this->activity->description)
            ->action(config('app.name'), url(config('app.url')));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}

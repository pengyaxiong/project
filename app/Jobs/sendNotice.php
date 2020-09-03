<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendNotice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $notifications;
    private $staff;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($staff, $notifications, $delay)
    {
        $this->notifications = $notifications;
        $this->staff = $staff;
        // 设置延迟的时间，delay() 方法的参数代表多少秒之后执行
        $this->delay($delay);
    }

    /**
     *  Execute the job.
     * 定义这个任务类具体的执行逻辑
     * 当队列处理器从队列中取出任务时，会调用 handle() 方法
     * @return void
     */
    public function handle()
    {
        //通知每个用户的 系统消息
        foreach ($this->staff as $staff) {
            $staff->notify($this->notifications);
        }

    }
}

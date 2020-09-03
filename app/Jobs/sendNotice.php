<?php

namespace App\Jobs;

use App\Models\Staff;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\Activitylog\Models\Activity;

class sendNotice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $activity;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Activity $activity, $delay)
    {
        $this->activity = $activity;
        // 设置延迟的时间，delay() 方法的参数代表多少秒之后执行
        $this->delay($delay);
    }

    /**
     *  Execute the job.
     * 定义这个任务类具体的执行逻辑
      当队列处理器从队列中取出任务时，会调用 handle() 方法
     * @return void
     */
    public function handle()
    {
        //通知每个用户的 系统消息
        $staffs = Staff::where('admin_id',1)->get();
        foreach ($staffs as $staff) {
            $staff->addActivity($this->activity);
        }

    }
}

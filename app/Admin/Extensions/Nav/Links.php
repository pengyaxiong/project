<?php

namespace App\Admin\Extensions\Nav;

use App\Models\Staff;

class Links
{
    public function __toString()
    {
        $admin_id=auth('admin')->user()->id;
        return <<<HTML
<audio style="display:none; height: 0" id="bg-music" preload="auto" src="/wangwang.mp3" loop="loop"></audio>
 
<li>
    <a href="/admin/notifications">
      <i class="fa fa-bell-o"></i>
      <span class="label label-warning" id="inventory"></span>
    </a>
</li>
 
<script>
 
    var getting = {
        url:'/api/notifications?admin_id=$admin_id',
        dataType:"json",
        success:function(res) {
          if(res.code == 200){
              toastr.options.timeOut=120000; // 保存2分钟
                toastr.warning(res.msg); // 提示文字
                toastr.options.onclick = function(){
                    location='/admin/notifications';  // 点击跳转页面
                };
               var audio = document.getElementById('bg-music');  // 启用音频通知
                audio.play();
                setTimeout(function(){
                    audio.load(); // 1.5秒后关闭音频通知
                },1500);
          }
          $('#inventory').html(res.re);
        },
        error: function (res) {
            console.log(res);
        }
    };
    window.setInterval(function() {
      $.ajax(getting)
    },5000);
</script>
HTML;
    }
}
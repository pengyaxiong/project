<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/3
 * Time: 19:34
 */

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SMSService
{
    /**
     * 发送验证码
     *if (!preg_match("/^1[0-9]{10}$/", $send_phone)) {
          return error_data('手机号格式错误');
        }
        $response = $this->send_sms->code($send_phone, $request->ip());
        return $response;
     *$response=$this->send_sms->check($request);
        if($response['status']==0){
        return error_data($response['msg'],$response['datas']);
        }
     * @param string $mobile 手机号
     * @param string $ip IP地址
     * @return \Illuminate\Http\Response
     */
    public function code($mobile, $ip)
    {
        $this->checkThrottle($mobile, $ip);
        if (!preg_match("/^1[0-9]{10}$/", $mobile)) {

            return ['code' => 500, 'msg' => '手机号格式错误'];
        }
        $code = mt_rand(111111, 999999);
        $content = "【项目管理】尊敬的客户，您的验证码为" . $code . "，请于2分钟内争取输入。如非本人操作，请忽略此短信。";//要发送的短信内容
        $result = $this->NewSms($mobile, $content);
        $result = explode('/', $result);
        if ($result[0] == '000') {
            $smsId = str_replace('sid:', '', $result[4]);
            Cache::put('sms.' . $smsId, compact('mobile', 'code'), 1800);
            self::throttle($mobile, $ip);

            return ['code' => 200, 'msg' => '发送成功', compact('mobile', 'smsId')];

        } else {
            return ['code' => 500, 'msg' => '网络错误'];
        }
    }

    /**
     * 检查验证码
     *
     * @param Request $request
     */
    public function check(Request $request)
    {
        $smsId = $request->input('sms_id');
        $code = $request->input('code');
        $mobile = $request->input('phone');
        $key = 'sms.' . $smsId;

        if (!Cache::has($key)) {
            return ['code' => 500, 'msg' => '验证码已失效'];
        }

        $data = Cache::get($key);

        if ($mobile != $data['mobile'] || $code != $data['code']) {

            return ['code' => 500, 'msg' => '验证码错误'];
        }

        return ['code' => 200, 'msg' => '验证成功'];

    }

    /**
     * 存储发送频率
     *
     * @param string $mobile 手机号
     * @param string $ip IP地址
     */
    private function throttle($mobile, $ip)
    {
        Cache::put('sms.throttle.' . $mobile, 1, 30);
        Cache::put('sms.throttle.' . $ip, 1, 10);
    }

    /**
     * 检查发送频率
     *
     * @param string $mobile 手机号
     * @param string $ip IP地址
     */
    private function checkThrottle($mobile, $ip)
    {
        if (Cache::has('sms.throttle.' . $mobile)) {
            return ['code' => 500, 'msg' => '不能重复发送验证码'];
        }

        if (Cache::get('sms.throttle.' . $ip)) {
            return ['code' => 500, 'msg' => '不能重复发送验证码'];
        }
    }

    public function NewSms($mobile, $content)
    {
        $url = "http://service.winic.org:8009/sys_port/gateway/index.asp?";
        $data = "id=%s&pwd=%s&to=%s&Content=%s&time=";
        $id = urlencode(iconv("utf-8", "gb2312", "grubby"));
        $pwd = 'a84909844';
        $to = $mobile;
        $content = urlencode(iconv("UTF-8", "GB2312", $content));
        $rdata = sprintf($data, $id, $pwd, $to, $content);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $rdata);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
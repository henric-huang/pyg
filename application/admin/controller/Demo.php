<?php

namespace app\Admin\controller;

use think\cache\driver\Redis;
use think\Db;
use think\Cache;
use custom\Mailer;

class Demo extends Base
{

    public function index()
    {
        //测试发送邮件
        $mail = new Mailer();

        $HTML = <<<HTML
            <table style = 'margin-left:280px;width:600px;height:300px;border-collapse:collapse;font-size: 12px;line-height: 24px;color: #333;font-family: Microsoft YaHei;' >
            <tr >
                <td style = 'height:30px;line-height:30px;border-collapse:collapse;' >
                    <img style = 'vertical-align:middle;' src = 'https://hwid1.vmall.com/CAS/up/logos/logoForUP.png' >
                </td >
            </tr >

            <tr >
                <td style = 'height:25px;line-height:25px;' >
                1459543371 , 您好!
                </td >
            </tr >

            <tr >
                <td style = 'height:25px;line-height:25px;' >
                为确保是您本人操作，请在邮件验证码输入框输入下方验证码：<br />
                    725087
                </td >
            </tr >

            <tr >
                <td style = 'height:25px;line-height:25px;' >
                请勿向任何人泄露您收到的验证码。
                </td >
            </tr >

            <tr >
                <td style = 'height:25px;line-height:25px;' >
                此致<br />
                    华为
                </td >
            </tr >
        </table >
        HTML;

        /*                        $HTML = <<<EOF
                                你好, <b>朋友</b>! <br/>这是一封来自<h1>快乐星球的邮件</h1>
                EOF;*/

        //        $ok = $mail->sendMail('981488996@qq.com', 'mingc', 'thinkphp5邮件标题', $HTML, './uploads/goods/20200312/thumb_big_c81744bd1fd81d7f10775bf104b97cc6.png');
        $ok = $mail->sendMail('981488996@qq.com', 'mingc', 'thinkphp5邮件标题', $HTML);

        echo "OK";


    }
}

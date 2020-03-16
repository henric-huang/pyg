<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

if (!function_exists('encrypt_password')) {
    // 定义加盐加密函数
    function encrypt_password($password)
    {
        // 盐值
        $salt = 'dsafgdfsfsd';
        return md5(md5(trim($password) . $salt));
    }
};

if (!function_exists('get_cate_list')) {
    //递归函数 实现无限级分类列表
    function get_cate_list($list, $pid = 0, $level = 0)
    {
        static $tree = array();
        foreach ($list as $row) {
            if ($row['pid'] == $pid) {
                $row['level'] = $level;
                $tree[]       = $row;
                get_cate_list($list, $row['id'], $level + 1);
            }
        }
        return $tree;
    }
}

if (!function_exists('get_tree_list')) {
    //引用方式实现 父子级树状结构
    function get_tree_list($list)
    {
        //将每条数据中的id值作为其下标
        $temp = [];
        foreach ($list as $v) {
            $v['son']       = [];
            $temp[$v['id']] = $v;
        }
        //获取分类树
        foreach ($temp as $k => $v) {
            $temp[$v['pid']]['son'][] = &$temp[$v['id']];
        }
        return isset($temp[0]['son']) ? $temp[0]['son'] : [];
    }
}

if (!function_exists('remove_xss')) {
    //使用htmlpurifier防范xss攻击
    function remove_xss($string)
    {
        //composer安装的，不需要此步骤。相对index.php入口文件，引入HTMLPurifier.auto.php核心文件
//         require_once './plugins/htmlpurifier/HTMLPurifier.auto.php';
        // 生成配置对象
        $cfg = HTMLPurifier_Config::createDefault();
        // 以下就是配置：
        $cfg->set('Core.Encoding', 'UTF-8');
        // 设置允许使用的HTML标签
        $cfg->set('HTML.Allowed', 'div,b,strong,i,em,a[href|title],ul,ol,li,br,p[style],span[style],img[width|height|alt|src]');
        // 设置允许出现的CSS样式属性
        $cfg->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align');
        // 设置a标签上是否允许使用target="_blank"
        $cfg->set('HTML.TargetBlank', TRUE);
        // 使用配置生成过滤用的对象
        $obj = new HTMLPurifier($cfg);
        // 过滤字符串
        return $obj->purify($string);
    }

}

if (!function_exists('curl_request')) {
    //使用curl函数库发送请求
    function curl_request($url, $post = true, $params = [], $https = true)
    {
        //初始化请求 进入传入的网址$url
        $ch = curl_init($url);

        //默认是get请求。get请求不用设置请求方式和请求参数
        //如果是post请求 要特别设置请求方式和请求参数
        if ($post) {
            //表明是POST请求
            curl_setopt($ch, CURLOPT_POST, true);
            //传入POST请求参数
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        //默认是http协议。http协议不用设置什么
        //如果是https协议，要特别设置禁止从服务器验证本地证书
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        //发送请求，获取返回结果
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //执行这个curl会话资源
        $res = curl_exec($ch);
        /*if(!$res){
            $msg = curl_error($ch);
            dump($msg);die;
        }*/
        //关闭这个curl会话资源请求
        curl_close($ch);
        return $res;
    }
}

if (!function_exists('sendmsg')) {
    //使用curl_request函数调用短信接口发送短信
    function sendmsg($phone, $content)
    {
        //从配置中取出请求地址、appkey
        $gateway = config('msg.gateway');
        $appkey  = config('msg.appkey');
        //https://way.jd.com/chuangxin/dxjk?mobile=13568813957&content=【创信】你的验证码是：5873，3分钟内有效！&appkey=您申请的APPKEY
        $url = $gateway . '?appkey=' . $appkey;
        //get请求
        /*$url .= '&mobile=' . $phone . '&content=' . $content;
        $res = curl_request($url, false, [], true);*/
        //post请求
        $params = [
            'mobile'  => $phone,
            'content' => $content
        ];
        $res    = curl_request($url, true, $params, true);
        //处理结果
        if (!$res) {
            return '请求发送失败';
        }
        //解析结果
        $arr = json_encode($res, true);
        if (isset($arr['code']) && $arr['code'] == 10000) {
            //短信接口调用成功
            return true;
        } else {
            /*if(isset($arr['msg'])){
                return $arr['msg'];
            }*/
            return '短信发送失败';
        }
    }
}

if (!function_exists('encrypt_phone')) {
    //手机号加密  15312345678   =》  153****5678
    function encrypt_phone($phone)
    {
        return substr($phone, 0, 3) . '****' . substr($phone, 7);
    }
}

if (!function_exists('sendEmail')) {
    /**
     * 邮件发送
     * * 开始的时候，记得引用类
     * * use PHPMailer\PHPMailer\PHPMailer;
     * * 应用公共函数文件，函数不能定义为public类型
     */
    function sendEmail($content, $toemail, $title = "邮件标题")
    {
        // 实力化类
        $mail = new \PHPMailer\PHPMailer\PHPMailer();

        // 使用SMTP服务
        $mail->isSMTP();

        // 编码格式为utf8，不设置编码的话，中文会出现乱码
        // $mail->CharSet = "utf8";
        $mail->CharSet = config('mail.charset');

        // 发送方的SMTP服务器地址
        // $mail->Host = "smtp.163.com";
        $mail->Host = config('mail.host');

        // 是否使用身份验证
        // $mail->SMTPAuth = true;
        $mail->SMTPAuth = config('mail.smtp_auth');

        // 发送方的163邮箱用户名，就是你申请163的SMTP服务使用的163邮箱
        // $mail->Username = "henric_zg_huang@163.com";
        $mail->Username = config('mail.username');

        // 发送方的邮箱密码，注意用163邮箱这里填写的是“客户端授权密码”而不是邮箱的登录密码
        // $mail->Password = "Huang1993";
        $mail->Password = config('mail.password');

        // 使用ssl协议方式
        // $mail->SMTPSecure = "ssl";
        $mail->SMTPSecure = config('mail.smtp_secure');

        // 163邮箱的ssl协议方式端口号是465/994
        // $mail->Port = 994;
        $mail->Port = config('mail.port');

        // 设置发件人信息，如邮件格式说明中的发件人，这里会显示为"品优购商城(xxxx@163.com)"，"品优购商城"是当做名字显示
        // $mail->setFrom("henric_zg_huang@163.com", "品优购商城");
        $mail->setFrom(config('mail.from'), config('mail.from_name'));

        // 设置收件人信息，如邮件格式说明中的收件人，这里会显示为Liang(yyyy@163.com)
        $mail->addAddress($toemail, '');

        // 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址，Henric是当做名字显示
        // $mail->addReplyTo("henric_zg_huang@163.com", "Henric");
        $mail->addReplyTo(config('mail.reply_to'), config('mail.reply_to_name'));

        //支持html格式内容
        $mail->IsHTML(true);

        // 设置邮件抄送人，可以只写地址，上述的设置也可以只写地址(这个人也能收到邮件)
        //$mail->addCC("xxx@163.com");

        // 设置秘密抄送人(这个人也能收到邮件)
        //$mail->addBCC("xxx@163.com");

        // 添加附件
        $mail->addAttachment('d:/img.jpg', '图片.jpg');
        // $mail->addAttachment('./uploads/goods/20200312/img.jpg','美的冰箱.jpg');

        //设置邮件中的图片
        // $mail->AddEmbeddedImage('./uploads/goods/20200312/6f0264e241a2cbc02986336ee5e9afcc.jpg');

        // 邮件标题
        $mail->Subject = $title;

        // 邮件正文
        // $mail->Body = "邮件内容:" . $desc_content . "点击可以查看文章地址:" . $desc_url;
        $mail->Body = $content;

        // 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用
        //$mail->AltBody = "This is the plain text纯文本";

        if (!$mail->send()) { // 发送邮件
            return $mail->ErrorInfo;
            // echo "Message could not be sent.";
            // echo "Mailer Error: ".$mail->ErrorInfo;// 输出错误信息
        } else {
            return "发送成功";
        }
    }
};

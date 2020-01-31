<?php

namespace app\home\controller;

use app\common\model\User;
use think\Controller;

class Login extends Controller
{
    //
    public function login()
    {
        // 临时关闭当前模板的布局功能
        $this->view->engine->layout(false);
        return view();
    }

    public function register()
    {
        // 临时关闭当前模板的布局功能
        $this->view->engine->layout(false);
        return view();
    }

    public function phone()
    {
        //接收数据
        $params = input();
        //参数检测
        $validate = $this->validate($params, [
            'phone|手机号'   => 'require|regex:1[3-9]\d{9}|unique:user,phone',
            'code|验证码'    => 'require|integer|length:4',
            'password|密码' => 'require|length:6,20|confirm:repassword',
        ]);
        if ($validate !== true) {
            $this->error($validate);
        }
        //验证码验证
        $code = cache('register_code_' . $params['phone']);
        if ($params['code'] != $code) {
            $this->error('验证码错误');
        }
        //验证码成功一次后失效
        cache('register_code_' . $params['phone'], null);
        //注册用户
        //处理数据 密码加密
        $params['password'] = encrypt_password($params['password']);
        $params['username'] = $params['phone'];
        $params['nickname'] = encrypt_phone($params['phone']);
        User::create($params, true);
        //页面跳转
        $this->redirect('home/login/login');
    }

    public function sendcode()
    {
        //接收参数 phone
        $params = input();
        //参数检测
        $validate = $this->validate($params, [
            'phone|手机号' => 'require|regex:1[3-9]\d{9}|unique:user,phone',
        ]);
        if ($validate !== true) {
            //验证失败
            $res = [
                'code' => 400,
                'msg'  => $validate,
                //'msg'  => '参数错误',
            ];
            //return json_encode($res);     //语法错误
            //return json($res);      //语法正确
            echo json_encode($res);
            die();   //语法正确
        }
        //短信发送限制 同一个手机号 一分钟只能发一次  1531561231356
        $time = time() - cache('register_time_' . $params['phone']);
        if ($time < 60) {
            $res = [
                'code' => 500,
                'msg'  => '发送太频繁',
            ];
            echo json_encode($res);
            die();
        }
        //发送验证码（生成验证码、生成短信内容、发短信）
        $code    = mt_rand(1000, 9999);
        $content = "【创信】你的验证码是：{$code}，3分钟内有效！";
        //$result  = sendmsg($params['phone'], $content);
        //开发测试时，不用真正发短信
        $result = true;
        //返回结果
        if ($result == true) {
            //发送成功，将验证码存储到缓存，用于后续校验
            cache('register_code_' . $params['phone'], $code, 3 * 60);
            //记录发送时间，用于发送前检测发送频率
            cache('register_time_' . $params['phone'], time(), 3 * 60);
            $res = [
                'code' => 200,
                'msg'  => '短信发送成功',
                'code' => $code,    //开发测试时，不用真正发短信
            ];
            echo json_encode($res);
            die();
//            return json($res);
        } else {
            $res = [
                'code' => 400,
                'msg'  => $result,
            ];
            echo json_encode($res);
            die();
//            return json($res);
        }
    }

    /**
     * 登录表单提交
     */
    public function dologin()
    {
        //接收参数
        $params = input();
        //参数检测
        $validate = $this->validate($params, [
            'username|用户名' => 'require',
            'password|密码'  => 'require|length:6,20',
        ]);
        if ($validate !== true) {
            $this->error($validate);
        }
        //参数处理
        $password = encrypt_password($params['password']);
        //查询用户表
//        $info     = User::where(['phone' => $username, 'password' => $password])->find();
        //写法一用户名 密码一起查询
        //$info = \app\common\model\User::where('phone', $params['username'])->where('password', $password)->find();
        //$info = \app\common\model\User::where('email', $params['username'])->where('password', $password)->find();
        //SELECT * FROM `pyg_user` where (phone='15313139033' or email='15313139033') and password = '8447e0ed22a57a97a12a9f4e229b6517';
        //$info = \app\common\model\User::where('phone', $params['username'])->whereOr('email', $params['username'])->where('password', $password)->find();  //有问题
        //用户名密码一起查询，同时查询手机号和邮箱两个字段
        $info = User::where(function ($query) use ($params) {
            $query->where('phone', $params['username'])->whereOr('email', $params['username']);
        })->where('password', $password)->find();
        if ($info) {
            //设置登录标识
            session('user_info', $info->toArray());
            //页面跳转
            $this->redirect('home/index/index');
        } else {
            $this->error('用户名或密码错误');
        }

        //写法二  先根据用户名查询，再比对密码
        $info = \app\common\model\User::where('phone', $params['username'])->whereOr('email', $params['username'])->find();
        if ($info && $info['password'] == $password) {
            //设置登录标识
            session('user_info', $info->toArray());
            //页面跳转
            $this->redirect('home/index/index');
        } else {
            $this->error('用户名或密码错误');
        }
    }

    /**
     * 退出
     */
    public function logout()
    {
        //清空session
        session(null);
        //跳转
        $this->redirect('home/login/login');
    }

    /**
     * qq登录回调地址
     */
    public function qqcallback()
    {
        //参考oauth/callback.php
        require_once("./plugins/qq/API/qqConnectAPI.php");
        $qc = new \QC();
        //得到access_token 和 openid
        $access_token = $qc->qq_callback();
        $openid       = $qc->get_openid();
        //获取用户信息（昵称）
        $qc   = new \QC($access_token, $openid);
        $info = $qc->get_user_info();  //$info['nickname']
        //自动注册登录（将第三方账号和自己系统的用户进行绑定）
        $user = \app\common\model\User::where('open_type', 'qq')->where('openid', $openid)->find();
        if ($user) {
            //非第一次登录 同步昵称
            $user->nickname = $info['nickname'];
            $user->save();
        } else {
            //第一次登录 创建新用户
            \app\common\model\User::create(['open_type' => 'qq', 'openid' => $openid, 'nickname' => $info['nickname']]);
        }
        //设置登录标识
        $user = \app\common\model\User::where('open_type', 'qq')->where('openid', $openid)->find();
        session('user_info', $user->toArray());
        //页面跳转
        $this->redirect('home/index/index');
    }

}



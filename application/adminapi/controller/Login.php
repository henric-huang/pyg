<?php

namespace app\adminapi\controller;

use app\common\model\Admin;
use think\Controller;
use think\Request;
use tools\jwt\Token;

class Login extends BaseApi
{
    // 获取验证码图片地址
    public function captcha()
    {
        // 验证码标识
        $uniqid = uniqid(mt_rand(100000, 999999));
         //dump($uniqid);die();  // 1443585dff1a8151996

        //返回数据 验证码图片路径、验证码标识
        $data = [
            'src'    => captcha_src($uniqid),
            'uniqid' => $uniqid,
        ];

        $this->ok($data);

    }

    // 登录接口
    public function login(Request $request)
    {
        /*return encrypt_password('123456');
        die();*/

        //获取输入变量
        $params   = $request->param();
        $validate = $this->validate($params, [
            'username|用户名' => 'require',
            'password|密码'  => 'require',
            'code|验证码'     => 'require',
            //'code|验证码' => 'require|captcha:'.$params['uniqid'], //验证码自动校验
            'uniqid|验证码标识' => 'require'
        ]);
        if ($validate !== true) {
            $this->fail($validate, 401);
        }

        //根据验证码标识，从缓存取出session_id 并重新设置session_id
        $session_id = cache('session_id_' . $params['uniqid']);
        if ($session_id) {
            $session_id($session_id);
        }
        //进行验证码校验 使用手动验证方法
        if (!captcha_check($params['code'], $params['uniqid'])) {
            //验证码错误
            //$this->fail('验证码错误', 402);
        }

        //根据用户名和密码（加密后的密码），查询管理员用户表
        $where = [
            'username' => 'admin',
            'password' => encrypt_password('123456'),
            /*'username' => $params['username'],
            'password' => encrypt_password($params['password']),*/
        ];

        $info = Admin::where($where)->find();
        if (!$info) {
            //用户名或者密码错误
            $this->fail('用户名或密码不正确！');
        }
        $data['token']    = Token::getToken($info->id);
        $data['user_id']  = $info->id;
        $data['username'] = $info->username;
        $data['nickname'] = $info->nickname;
        $data['email']    = $info->email;
        //登录成功
        $this->ok($data);

    }

    /**
     * 后台退出接口
     */
    public function logout()
    {
        //清空token  将需清空的token存入缓存，再次使用时，会读取缓存进行判断
        $token          = Token::getRequestToken();
        $delete_token   = cache('delete_token') ?: [];
        $delete_token[] = $token;
        cache('delete_token', $delete_token, 86400);
        $this->ok();
    }
}

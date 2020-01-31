<?php

namespace app\adminapi\controller;

use app\adminapi\logic\AuthLogic;
use think\Controller;
use think\Exception;
use tools\jwt\Token;

class BaseApi extends Controller
{
    //无需登录的请求数组
    protected $no_login = ['login/login', 'login/captcha'];

    //控制器的初始化方法
    public function _initialize()
    {
        parent::_initialize();
        //允许的源域名
        header("Access-Control-Allow-Origin: *");
        //允许的请求头信息
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
        //允许的请求类型
        header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH');

        try {
            //登录检测
            //获取当前请求的控制器方法名称
            $path = strtolower($this->request->controller() . '/' . $this->request->action());
            if (!in_array($path, $this->no_login)) {
                //需要做登录检测
                //$user_id = \tools\jwt\Token::getUserId();
                //为了方便测试，写死用户id
                $user_id = 1;
                if (empty($user_id)) {
                    $this->fail('token验证失败', 403);
                }
                //权限检测
                $auth_check = AuthLogic::check();
                if (!$auth_check) {
                    $this->fail('没有访问权限', 402);
                }

                //将得到的用户id 放到请求信息中去  方便后续使用
                $this->request->get('user_id', $user_id);
                $this->request->post('user_id', $user_id);
            }
        } catch (Exception $e) {
            $this->fail('token解析失败', 404);
        }
    }


    /**
     * 通用响应
     * @param int $code 错误码
     * @param string $msg 错误描述
     * @param array $data 返回数据
     */
    public function response($code = 200, $msg = 'success', $data = [])
    {
        $res = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        ];
        //echo json_encode($res, true);
        json($res)->send();die();
    }

    /**
     * 失败时响应
     * @param string $msg 错误描述
     * @param int $code 错误码
     */
    public function fail($msg = 'fail', $code = 500, $data = [])
    {
        return $this->response($code, $msg, $data);
    }

    /**
     * 成功时响应
     * @param array $data 返回数据
     * @param int $code 错误码
     * @param string $msg 错误描述
     */
    public function ok($data = [], $code = 200, $msg = 'success')
    {
        return $this->response($code, $msg, $data);
    }


}

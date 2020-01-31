<?php

namespace app\adminapi\controller;

use app\common\model\Profile;
use think\Db;
use tools\jwt\Token;

class Index extends BaseApi
{
    public function index()
    {
        //测试数据库配置
        /*$goods = Db::table('pyg_goods')->find();
        dump($goods);*/

        // $this->ok(['action' => 'index']);

        // 测试jwt
        // 生成token
        /*$token = Token::getToken(100);
        dump($token);
        // 从token获取用户id
        $user_id = Token::getUserId($token);
        dump($user_id);
        die();*/

        //测试 关联模型
        $info = \app\common\model\Admin::find(1);
//        dump($info);
//        dump($info->profile->idnum);
//        $this->ok($info);
//        return json_encode($info);
        /*$info = \app\common\model\Admin::with('profile')->find(1);
        $this->ok($info);*/
        /*$data = \app\common\model\Admin::with('profile')->select();
        $this->ok($data);*/

        //以档案为主：档案到管理员
        /*$info = \app\common\model\Profile::find(1);
        dump($info->admin->username);*/
        /*$info = \app\common\model\Profile::with('admin')->find(1);
        $this->ok($info);*/
        /*$data = \app\common\model\Profile::with('admin')->select();
        $this->ok($data);*/

        //一对多关联
        //以分类表为主
        /*$info = \app\common\model\Category::find(72);
        $info = \app\common\model\Category::with('brand')->find(72);
        $this->ok($info);*/
        /*$info = \app\common\model\Category::with('brand')->select();
        $this->ok($info);*/

        //以品牌表为主
        $info = \app\common\model\Brand::with('category')->find(1);
        $this->ok($info);
    }
}

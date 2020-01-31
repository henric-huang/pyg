<?php

namespace app\home\controller;

use think\Controller;
use think\Request;

class Cart extends Base
{
    //加入购物车  表单提交
    public function addcart(Request $request)
    {
        if ($request->isGet()) {
            //如果是get请求 跳转到首页
            $this->redirect('home/index/index');
        }
        //接收数据
        $params = $request->param();
        //$params = input();
        //dump($params);die;
        //参数检测
        $validate = $this->validate($params, [
            'goods_id'      => 'require|integer|gt:0',
            'spec_goods_id' => 'integer|gt:0',
            'number'        => 'require|integer|gt:0',
        ]);
        if ($validate != true) {
            $this->error($validate);
        }
        //处理数据 调用封装好的方法
        \app\home\logic\CartLoginc::addCart($params['goods_id'], $params['spec_goods_id'], $params['number']);
    }
}

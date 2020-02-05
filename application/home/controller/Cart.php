<?php

namespace app\home\controller;

use app\home\logic\CartLogic;
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
        \app\home\logic\CartLogic::addCart($params['goods_id'], $params['spec_goods_id'], $params['number']);
        //结果页面展示
        //查询商品相关信息以及SKU信息
        $goods = \app\common\model\Goods::getGoodsWithSpec($params['spec_goods_id'], $params['goods_id']);
        //dump($goods);die;
        return view('addcart', ['goods' => $goods, 'number' => $params['number']]);
    }

    //购物车列表
    public function index()
    {
        //查询所有购物车数据
        $list = CartLogic::getAllCart();
        //对每一条购物记录 查询商品相关信息（商品信息和SKU信息）
//        dump($list);die();
        foreach ($list as &$v) {
            $v['goods'] = \app\common\model\Goods::getGoodsWithSpec($v['spec_goods_id'], $v['goods_id'])->toArray();
        }
        unset($v);
//        dump($list);die();
        return view('index', ['list' => $list]);
    }

    /**
     * ajax修改购买数量
     */
    public function changenum()
    {
        //接收参数  id  number
        $params = input();
        //参数检测
        $validate = $this->validate($params, [
            'id'     => 'require',
            'number' => 'require|integer|gt:0',
        ]);
        if ($validate !== true) {
            $res = ['code' => 400, 'mag' => '参数错误'];
            echo json_encode($res);
            die;
        }
        //处理数据
        CartLogic::changeNum($params['id'], $params['number']);
        //返回数据
        $res = ['code' => 200, 'msg' => 'success'];
        echo json_encode($res);
        die();
    }

    /**
     * ajax删除购物记录
     */
    public function delcart()
    {
        //接收参数
        $params = input();
        //参数检测
        /*$validate = $this->validate($params,[
           'id'=>'require',
        ]);
        if ($validate !== true){
            $res = ['code'=>400,'msg'=>'参数错误'];
            echo json_encode($res);die();
        }*/
        if (!isset($params['id']) || empty($params['id'])) {
            $res = ['code' => 400, 'msg' => '参数错误'];
            echo json_encode($res);
            die();
        }
        //处理数据
        CartLogic::delCart($params['id']);
        //返回数据
        $res = ['code' => 200, 'msg' => 'success'];
        echo json_encode($res);
        die();
    }

    //用于测试加入购物车功能 cookie的情况
    public function test()
    {
        //获取cookie中所有的购物车数据，判断添加操作是否成功
        $data = cookie('cart');
//        dump($data);die;
        //如果cookie中的购物车数据有问题，则全部删除，再重新添加
        cookie('cart', null);
    }
}

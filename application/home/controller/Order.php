<?php

namespace app\home\controller;

use app\common\model\Address;
use app\common\model\OrderGoods;
use app\home\logic\OrderLogic;
use think\Collection;
use think\Controller;
use think\Db;
use think\Exception;
use think\Request;

class Order extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
    }

    /**
     * 显示创建资源表单页.
     * 显示结算页
     * @return \think\Response
     */
    public function create()
    {
        //登录检测
        if (!session('?user_info')) {
            //没有登录 跳转到登录页面
            //设置登录成功后的跳转地址
            //session('back_url', 'home/order/create');
            session('back_url', 'home/cart/index');
            $this->redirect('home/login/login');
        }
        //获取收货地址信息
        //获取用户id
        $user_id = session('user_info.id');
        $address = Address::where(['user_id' => $user_id])->select();
        //查询选中的购物记录以及商品信息和SKU规格商品信息
        /*$cart_data = \app\common\model\Cart::with('goods,spec_goods')->where('is_selected', 1)->where('user_id', $user_id)->select();
        $cart_data = (new Collection($cart_data))->toArray();
//        dump($cart_data);die;
        $total_price  = 0;
        $total_number = 0;
        foreach ($cart_data as &$v) {
            //使用sku的价格，覆盖商品价格
            if (isset($v['price']) && $v['price'] > 0) {
                $v['goods_price'] = $v['price'];
            }
            if (isset($v['cost_price2']) && $v['cost_price2'] > 0) {
                $v['cost_price'] = $v['cost_price2'];
            }
            //库存处理
            if (isset($v['store_count']) && $v['store_count'] > 0) {
                $v['goods_number'] = $v['store_count'];
            }
            if (isset($v['store_frozen']) && $v['store_frozen'] > 0) {
                $v['frozen_number'] = $v['store_frozen'];
            }
            //累加总数量和总价格
            $total_number += $v['number'];
            $total_price += $v['number'] * $v['goods_price'];
        }
        unset($v);*/
        $res          = OrderLogic::getCartDataWithGoods();
        $cart_data    = $res['cart_data'];
        $total_price  = $res['total_price'];
        $total_number = $res['total_number'];

        //return view('create', ['address' => $address,'cart_data'=>$cart_data,'total_price'=>$total_price,'total_number'=>$total_number]);
        return view('create', compact('address', 'cart_data', 'total_price', 'total_number'));
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //接收参数
        $params = input();
        //参数检测
        $validate = $this->validate($params, [
            'address_id' => 'require|integer|gt:0',
        ]);
        if ($validate != true) {
            $this->error($validate);
        };
        //组装订单表数据 添加一条
        //查询收货地址
        $address = Address::find($params['address_id']);
        if (!$address) {
            $this->error('请重新选择收货地址');
        }
        //订单编号
        $order_sn = time() . mt_rand(100000, 999999);
        $user_id  = session('user_info.id');
        //查询结算的商品（选中的购物记录以及商品和SKU信息）
        $res = OrderLogic::getCartDataWithGoods();
        //$res['cart_data']  $res['total_number'] $res['total_price']
        $order_data = [
            'user_id'        => $user_id,
            'order_sn'       => $order_sn,
            'consignee'      => $address['consignee'],
            'address'        => $address['area'] . $address['address'],
            'phone'          => $address['phone'],
            'goods_price'    => $res['total_price'],
            'shipping_price' => 0,
            'coupon_price'   => 0,
            'order_amount'   => $res['total_price'],
            'total_amount'   => $res['total_price'],
        ];
        //开启事务
        Db::startTrans();
        try {
            //添加单条数据用create()方法
            $order = \app\common\model\Order::create($order_data);
            //向订单商品表添加多条数据
            $order_goods_data = [];
            foreach ($res['cart_data'] as $v) {
                $order_goods_data[] = [
                    'order_id'         => $order['id'],
                    'goods_id'         => $v['goods_id'],
                    'spec_goods_id'    => $v['spec_goods_id'],
                    'number'           => $v['number'],
                    'goods_name'       => $v['goods_name'],
                    'goods_logo'       => $v['goods_logo'],
                    'goods_price'      => $v['goods_price'],
                    'spec_value_names' => $v['value_names'],
                ];
            }
            //批量添加
            $model = new OrderGoods();
            $model->saveAll($order_goods_data);
//            dump($model);die();
            //从购物车表删除对应数据
            \app\common\model\Cart::where(['user_id' => $user_id, 'is_selected' => 1])->delete();
            //\app\common\model\Cart::where('user_id',$user_id)->where('is_selected',1)->delete();
            //提交事务
            Db::commit();
            //展示选择支付方式页面 。。。
            $pay_type = config('pay_type');
            return view('pay', ['order_sn' => $order_sn, 'pay_type' => $pay_type, 'total_price' => $res['total_price']]);
        } catch (Exception $e) {
            //回滚事务
            Db::rollback();
            $this->error('创建订单失败，请重试');
        }
    }

    /**
     * 显示指定的资源
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {

    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param \think\Request $request
     * @param int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}

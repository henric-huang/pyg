<?php


namespace app\home\logic;


use app\common\model\Cart;
use think\Collection;

class OrderLogic
{
    private static $arr;

    public static function getCartDataWithGoods()
    {
        $user_id      = session('user_info.id');
        $cart_data    = Cart::with('goods,spec_goods')->where('is_selected', 1)->where('user_id', $user_id)->select();
        $cart_data    = (new Collection($cart_data))->toArray();
        $total_number = 0;
        $total_price  = 0;
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
            $total_price  += $v['number'] * $v['goods_price'];
        }
        unset($v);
        self::$arr = [
            'cart_data'    => $cart_data,
            'total_number' => $total_number,
            'total_price'  => $total_price,
        ];
        return self::$arr;
    }
}
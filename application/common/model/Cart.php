<?php

namespace app\common\model;

use think\Model;
use traits\model\SoftDelete;

class Cart extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    //设置购物车-商品关联  一条购物车记录 属于 一个商品
    public function goods()
    {
        return $this->belongsTo('Goods', 'goods_id', 'id')->bind('goods_logo,goods_name,goods_price,goods_number,cost_price,frozen_number');
    }

    //设置购物车-规格商品SKU关联  一条购物车记录 属于 一个规格商品SKU
    public function specGoods()
    {
        //在Cart模型中cost_price字段已经在上边绑定过，重复了，所以需要取别名
        //return $this->belongsTo('SpecGoods', 'spec_goods_id', 'id')->bind('value_ids,value_names,price,cost_price,store_count,store_frozen');
        return $this->belongsTo('SpecGoods', 'spec_goods_id', 'id')->bind(['value_ids', 'value_names', 'price', 'cost_price2' => 'cost_price', 'store_count', 'store_frozen']);
    }
}

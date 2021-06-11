<?php

namespace app\homeapi\model;

use think\Model;

class Order extends Model
{
    //定义与订单商品的关联 一个订单有多个订单商品
    public function orderGoods()
    {
        return $this->hasMany('OrderGoods','order_id','id');
    }
}

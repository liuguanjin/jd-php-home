<?php

namespace app\homeapi\model;

use think\Model;

class OrderGoods extends Model
{
    //定义与商品关联 一个订单商品有一个商品
    public function goods()
    {
        return $this->belongsTo('Goods','goods_id','id');
    }

}

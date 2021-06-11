<?php

namespace app\homeapi\controller;

use think\Controller;

class Shop extends BaseApi
{
    //
    public function detail($id = "")
    {
        $params = input();
        $where = [];
        if (!empty($params['keyword'])){
            $where['shop_name'] = ['like',"%{$params['keyword']}%"];
        }
        $detail = \app\adminapi\model\Shop::with('goods')->where($where)->find($id);
        if (empty($detail)){
            $this->fail('服务器异常,当前店铺下无商品');
        }
        $this->ok($detail);
    }
}

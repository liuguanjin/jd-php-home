<?php

namespace app\adminapi\controller;

use think\Controller;

class MyShop extends BaseApi
{
    public function soldGoods()
    {
        $params = input();
        $admin_id = $params['admin_id'];
        $where = [];
        if (!empty($params['keyword'])){
            $where['goods_name'] = ['like',"%{$params['keyword']}%"];
        }
        $shop = \app\adminapi\model\Shop::where('admin_id',$admin_id)->find();
        if (empty($shop)){
            $this->fail('您暂无店铺！');
        }
        $goods = \app\homeapi\model\OrderGoods::where($where)->where('shop_id',$shop['id'])->select();
        $this->ok($goods);
    }
    public function myGoods()
    {
        //获取店铺下的商品列表
        $params = input();
        $admin_id = $params['admin_id'];
        $where = [];
        if (!empty($params['keyword'])){
            $where['goods_name'] = ['like',"%{$params['keyword']}%"];
        }
        $shop = \app\adminapi\model\Shop::where('admin_id',$admin_id)->find();
        if (empty($shop)){
            $this->fail('您暂无店铺！');
        }
        $list = \app\adminapi\model\Goods::with('category,brand,type,shop_row')
            ->where($where)
            ->where('shop_id',$shop['id'])
            ->order('id asc')
            ->paginate(10);
        $this->ok($list);
    }
    public function nosend()
    {
        $params = input();
        $admin_id = $params['admin_id'];
        $shop = \app\adminapi\model\Shop::where('admin_id',$admin_id)->find();
        if (empty($shop)){
            $this->fail('您暂无店铺！');
        }
        $data = [];
        $order_goods = \app\homeapi\model\OrderGoods::where('shop_id',$shop['id'])->select();
        $order_goods = (new \think\Collection($order_goods))->toArray();
        foreach ($order_goods as $k=>$v){
            if ($v['status'] === 1){
                $data[] = $v;
            }
        }
        $this->ok($data);
    }
    public function sendgoods($id="")
    {
        $params = input();
        $validate = $this->validate($params,[
            'shipping_code|物流编号' => 'require',
            'shipping_name|物流名称' => 'require',
            'shipping_sn|物流单号' => 'require'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        $order_goods = \app\homeapi\model\OrderGoods::find($id);
        $data['shipping_code'] = $params['shipping_code'];
        $data['shipping_name'] = $params['shipping_name'];
        $data['shipping_sn'] = $params['shipping_sn'];
        $data['status'] = 2;
        $data['goods_id'] = $order_goods['goods_id'];
        $data['goods_logo'] = $order_goods['goods_logo'];
        $data['goods_name'] = $order_goods['goods_name'];
        $data['goods_price'] = $order_goods['goods_price'];
        $data['is_comment'] = $order_goods['is_comment'];
        $data['order_id'] = $order_goods['order_id'];
        $data['shop_id'] = $order_goods['shop_id'];
        $data['shop_name'] = $order_goods['shop_name'];
        $data['spec_goods_id'] = $order_goods['spec_goods_id'];
        $data['spec_price'] = $order_goods['spec_price'];
        $data['spec_value_names'] = $order_goods['spec_value_names'];
        \app\homeapi\model\OrderGoods::update($data,['id'=>$id],true);
        $res = \app\homeapi\model\OrderGoods::find($id);
        $this->ok($res);
    }
    public function myEvaluate()
    {
        $current_time = time();
        dump($current_time);
    }
}

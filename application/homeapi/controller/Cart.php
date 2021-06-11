<?php

namespace app\homeapi\controller;

use think\Controller;

class Cart extends BaseApi
{
    public function read($id = "")
    {
        if (empty($id)){
            $this->fail('未登录或用户信息错误');
        }
        $cart = \app\adminapi\model\Cart::where('user_id',$id)->select();
        if (empty($cart)){
            $this->fail('该用户购物车暂无数据');
        }
        foreach ($cart as $v){
            unset($v['create_time']);
            unset($v['update_time']);
            unset($v['delete_time']);
            unset($v['id']);
        }
        $this->ok($cart);
    }
    public function index()
    {
        $params = input();
        unset($params['/cart']);
        foreach ($params as $k=>$v){
            $goods = \app\adminapi\model\Goods::with('shop')->find($v['goods_id']);
            $goods['goods_is_selected'] = $params[$k]['goods_is_selected'];
            $params[$k]['goods'] = $goods;
            $spec_goods = \app\adminapi\model\SpecGoods::find($v['spec_goods_id']);
            $params[$k]['spec_goods'] = $spec_goods;
        }
        $this->ok($params);
    }
    public function update($id="")
    {
        $params = input();
        unset($params["/cart/{$id}"]);
        unset($params['id']);
        \think\Db::startTrans();
        try {
            \app\adminapi\model\Cart::destroy(['user_id'=>$id]);
            $cart_model = new \app\adminapi\model\Cart;
            $cart_model->allowField(true)->saveAll($params);
            \think\Db::commit();
            $this->ok();
        }catch (\Exception $e){
            \think\Db::rollback();
            $this->fail($e->getMessage());
        }
    }
}

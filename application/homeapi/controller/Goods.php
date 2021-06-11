<?php

namespace app\homeapi\controller;

use think\Controller;

class Goods extends BaseApi
{
    public function index()
    {
        $params = input();
        $where = [];
        if (!empty($params['keyword'])){
            $where['goods_name'] = ['like',"%{$params['keyword']}%"];
        }
        if (empty($params['page'])){
            $params['page'] = 1;
        }
        $goods = \app\adminapi\model\Goods::with('shop')->where($where)->limit(10*($params['page']-1),10)->select();
        if (empty($goods)){
            $this->fail('服务器异常，获取商品列表失败');
        }
        $this->ok($goods);
    }
    public function detail($id = "")
    {
        $goods = \app\adminapi\model\Goods::with('goods_images,spec_goods,brand_row,shop')->find($id);
        if (empty($goods)){
            $this->fail('服务器异常，商品已不存在');
        }
        $hot = $goods['hot'];
        $hot += 1;
        \app\adminapi\model\Goods::where('id',$id)->setField('hot',$hot);
        $goods['brand'] = $goods['brand_row'];
        unset($goods['brand_row']);
        $type = \app\adminapi\model\Type::with('specs,specs.spec_values,attrs')->find($goods['type_id']);
        $goods['type'] = $type;
        $this->ok($goods);
    }
    public function balanceGoods()
    {
        $params = input();
        unset($params['/balancegoods']);
        $goods = [];
        foreach ($params as $k=>$v){
            $goods[$k]['goods'] = \app\adminapi\model\Goods::with('shop')->find($v['goods_id']);
            $goods[$k]['spec_goods'] = \app\adminapi\model\SpecGoods::find($v['spec_goods_id']);
        }
        $this->ok($goods);
    }
    public function recommend()
    {
        $id=input('id');
        $params = input();
        $where = [];
        if (!empty($params['keyword'])){
            $where['goods_name'] = ['like',"%{$params['keyword']}%"];
        }
        if (empty($params['page'])){
            $params['page'] = 1;
        }
        $goods = \app\adminapi\model\Goods::with('shop')->where($where)->order('hot','desc')->limit(10*($params['page']-1),10)->select();
        //如果未登录或者传入登录id为0 直接推荐首页商品
        if ($id == 0){
            if (empty($goods)){
                $this->fail('服务器异常，获取商品列表失败');
            }
            $this->ok($goods);
        }else{
            //如果已登录
            $footprint_goods_ids = [];
            $footprint = \app\adminapi\model\Footprint::where('user_id',$id)->select();
            //已登录但无足迹 直接推荐首页商品
            if (empty($footprint)){
                if (empty($goods)){
                    $this->fail('服务器异常，获取商品列表失败');
                }
                $this->ok($goods);
            }else{
                //已登录且有足迹 根据足迹推荐商品
                foreach ($footprint as $v){
                    //足迹中的商品id数组 每个值中多个商品id用 _ 连接
                    $footprint_goods_ids[] = $v['goods_ids'];
                }
                //将数组中的所有值用 _ 连接
                $goods_ids = implode('_',$footprint_goods_ids);
                //将数组用 _ 分开 得到每个商品id
                $footprint_goods_ids = explode('_',$goods_ids);
                //数组去重
                $footprint_goods_ids = array_unique($footprint_goods_ids);
                //获取商品的关键词
                $keywords = [];
                foreach ($footprint_goods_ids as $v){
                    $goods = \app\adminapi\model\Goods::find($v);
                    $keywords[] = $goods['keywords'];
                }
                //将商品的关键词用 , 连接
                $keywords = implode(',',$keywords);
                //将商品的关键词用 _ 分开 得到每个商品的关键词
                $keywords = explode(',',$keywords);
                //商品的关键词去重
                $keywords = array_unique($keywords);
                dump($keywords);die();
            }
        }
    }
}

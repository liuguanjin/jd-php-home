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
                //已推荐的商品id集合（包括足迹中的商品） 使得以后推荐的不重复
                $recommend_goods_ids = [];
                //已登录且有足迹 根据足迹推荐商品
                foreach ($footprint as $v){
                    //足迹中的商品id数组 每个值中多个商品id用 _ 连接
                    $footprint_goods_ids[] = $v['goods_ids'];
                }
                //将数组中的所有值用 _ 连接（因为足迹中每日商品id用_连接）
                $goods_ids = implode('_',$footprint_goods_ids);
                //将数组用 _ 分开 得到每个商品id
                $footprint_goods_ids = explode('_',$goods_ids);
                //数组去重
                $footprint_goods_ids = array_unique($footprint_goods_ids);
                $recommend_goods_ids = $footprint_goods_ids;
                //取前6个足迹商品
                if (sizeof($footprint_goods_ids) > 6){
                    $footprint_goods_ids = array_splice($footprint_goods_ids,0,5);
                }
                //获取足迹商品所属的模型id
                $recommend_goods_typeids = [];
                foreach ($footprint_goods_ids as $v){
                    $recommend_goods_one = \app\adminapi\model\Goods::find($v);
                    $recommend_goods_typeids[] = $recommend_goods_one['type_id'];
                }
                //模型id数组去重
                $recommend_goods_typeids = array_unique($recommend_goods_typeids);
                //根据模型id去查询推荐的商品 并且推荐的不在足迹商品中
                $recommend_goods = [];
                foreach ($recommend_goods_typeids as $v){
                    $recommend_goods = \app\adminapi\model\Goods::with('shop')->whereNotIn('id',$recommend_goods_ids)->where('type_id','=',$v)->select();
                }
                foreach ($recommend_goods as $v){
                    $v->toArray();
                    $recommend_goods_ids[] = $v['id'];
                }
                if (sizeof($recommend_goods) < 10){
                    $recommend_goods =  array_merge($recommend_goods,\app\adminapi\model\Goods::with('shop')->whereNotIn('id',$recommend_goods_ids)->limit(0,10-sizeof($recommend_goods))->select());
                }
                $this->ok($recommend_goods);
                /*
                //获取关键词再进行查询->会有重复查询 需要再进行处理 较为不便
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
                //根据商品关键词获取推荐商品 二维数组
                $recommend_goods_array = [];
                foreach ($keywords as $k=>$v){
                    $where['keywords'] = ['like',"%$v%"];
                    $recommend_goods_array[] = \app\adminapi\model\Goods::with('shop')->where($where)->limit(0,2)->select();
                }
                //根据二维数组得到推荐商品的一维数组
                $recommend_goods = [];
                foreach($recommend_goods_array as $k=>$v){
                    foreach ($v as $x){
                        $recommend_goods[] = $x;
                    }
                }
                $this->ok($recommend_goods);
                */
            }
        }
    }
}

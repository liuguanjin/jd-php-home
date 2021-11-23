<?php

namespace app\homeapi\controller;

use think\Controller;

class Footprint extends BaseApi
{
    public function index()
    {
        $params = input();
        unset($params['/footprint']);
        foreach ($params as $k=>$v){
            foreach ($v['detail'] as $i=>$u){
                $goods = \app\adminapi\model\Goods::find($u['id']);
                $params[$k]['detail'][$i] = $goods;
            }
        }
        if (empty($params)){
            $this->fail('暂无足迹数据');
        }
        $this->ok($params);
    }
    public function read($id="")
    {
        if (empty($id)){
            $this->fail('未登录或用户信息错误');
        }
        $footprint = \app\adminapi\model\Footprint::where('user_id',$id)->order('date','desc')->select();
        foreach ($footprint as $k=>$v){
            $id_array = explode('_',$v['goods_ids']);
            foreach ($id_array as $i=>$u){
                $id_array[$i] = ['id' => (int)$u];
            }
            $footprint[$k]['detail'] = $id_array;
        }
        if (empty($footprint)){
            $this->fail('该用户暂无足迹数据');
        }
        $this->ok($footprint);
    }
    public function update($id="")
    {
        $params = input();
        unset($params["/footprint/{$id}"]);
        unset($params['id']);
        if (empty($params)){
            $this->fail();
        }
        \think\Db::startTrans();
        try {
            foreach ($params as $k=>$v){
                unset($params[$k]['create_time']);
                unset($params[$k]['update_time']);
                unset($params[$k]['delete_time']);
                unset($params[$k]['id']);
                $goods_ids = [];
                foreach ($v['detail'] as $i){
                    $goods_ids[] = $i['id'];
                }
                $params[$k]['goods_ids'] = implode('_',$goods_ids);
                $params[$k]['user_id'] = $id;
                unset($params[$k]['detail']);
            }
            \app\adminapi\model\Footprint::destroy(['user_id'=>$id]);
            $footprint_model = new \app\adminapi\model\Footprint();
            $footprint_model->allowField(true)->saveAll($params);
            \think\Db::commit();
            $this->ok();
        }catch (\Exception $e){
            \think\Db::rollback();
            $this->fail($e->getMessage());
        }
    }
}

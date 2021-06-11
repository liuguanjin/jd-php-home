<?php

namespace app\homeapi\controller;

use think\Controller;

class Collect extends BaseApi
{
    public function index()
    {
        $params = input();
        unset($params['/collect']);
        $goods = \app\adminapi\model\Goods::where('id', 'in', $params)->select();
        if (empty($goods)) {
            $this->fail('暂无收藏夹数据');
        }
        $this->ok($goods);
    }

    public function read($id = "")
    {
        if (empty($id)){
            $this->fail('未登录或用户信息错误');
        }
        $collect = \app\adminapi\model\Collect::where('user_id',$id)->select();
        if (empty($collect)){
            $this->fail('该用户暂无收藏夹数据');
        }
        $goods_id = [];
        foreach ($collect as $v){
            $goods_id[] = $v['goods_id'];
        }
        $this->ok($goods_id);
    }
    public function update($id="")
    {
        $params = input();
        unset($params["/collect/{$id}"]);
        unset($params['id']);
        $array = [];
        foreach ($params as $k=>$v){
            $array[$k]['goods_id'] = $v;
            $array[$k]['user_id'] = $id;
        }
        \think\Db::startTrans();
        try {
            \app\adminapi\model\Collect::destroy(['user_id'=>$id]);
            $collect_model = new \app\adminapi\model\Collect();
            $collect_model->allowField(true)->saveAll($array);
            \think\Db::commit();
            $this->ok();
        }catch (\Exception $e){
            \think\Db::rollback();
            $this->fail($e->getLine());
        }
    }
}

<?php

namespace app\homeapi\controller;

use think\Controller;

class CollectShop extends BaseApi
{
    public function index()
    {
        $user_id = input('user_id');
        $data = \app\homeapi\model\ShopCollect::where('user_id', $user_id)->find();
        if ($data['shop_ids'] == ''){
            $data['shop_ids'] = [];
        }else{
            $data['shop_ids'] = explode('_', $data['shop_ids']);
        }
        $this->ok($data);
    }

    public function update($id = "")
    {
        $params = input();
        unset($params['create_time']);
        unset($params['update_time']);
        unset($params['delete_time']);
        \think\Db::startTrans();
        try {
            \app\adminapi\model\Shop::update($params, ['id' => $params['id']], true);
            unset($params['id']);
            if (\app\homeapi\model\ShopCollect::where('user_id','=',$params['user_id'])->count() == 0){
                $params['shop_ids'] = $params['shop_ids'][0];
                $data = [
                  'user_id' => $params['user_id'],
                    'shop_ids' =>$params['shop_ids']
                ];
                \app\homeapi\model\ShopCollect::create($data);
            }else{
                $params['shop_ids'] = implode('_', $params['shop_ids']);
                \app\homeapi\model\ShopCollect::update($params, ['user_id' => $params['user_id']], true);
            }
            \think\Db::commit();
            $this->ok();
        } catch (\Exception $e) {
            \think\Db::rollback();
            $this->fail($e->getMessage());
        }
    }

    public function collectShopDetail()
    {
        $params = input();
        unset($params['/collect-shop-detail']);
        $data = [];
        foreach ($params as $k=>$v){
            $data[$k] = \app\adminapi\model\Shop::with('goods')->find($v);
        }
        if (empty($data)){
            $this->fail('暂无收藏店铺');
        }
        $this->ok($data);
    }
}

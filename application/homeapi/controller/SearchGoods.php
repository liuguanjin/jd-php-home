<?php

namespace app\homeapi\controller;

use think\Controller;
use think\Request;

class SearchGoods extends BaseApi
{
    public function history($id="")
    {
        $params = input();
        if ($params['id'] == 0){
            $data = [];
            $this->ok($data);
        }else{
            $data = \app\homeapi\model\SearchGoods::where('user_id','=',$params['id'])->distinct(true)->select();
            $this->ok($data);
        }
    }
    public function often()
    {
        $data = \app\homeapi\model\SearchGoods::field('search_words')->orderRaw('COUNT(search_words) desc')->group('search_words')->limit(0,10)->select();
        $this->ok($data);
    }
    public function delete($id="")
    {
        $params = input();
        if ($params['id'] == 0){
            $this->fail('您未登录');
        }else{
            $data = \app\homeapi\model\SearchGoods::where('user_id','=',$params['id'])->select();
            if (empty($data)){
                $this->fail('您暂无搜索历史');
            }else{
                \app\homeapi\model\SearchGoods::where('user_id','=',$params['id'])->delete();
                $this->ok();
            }
        }
    }
    public function recommend()
    {
        $params = input();
        $where = [];
        if (!empty($params['search_input'])){
            $where['search_words'] = ['like',"{$params['search_input']}%"];
        }
        $data = \app\homeapi\model\SearchGoods::where($where)->distinct(true)->field('search_words')->limit(0,10)->select();
        $this->ok($data);
    }
    public function searchResult()
    {
        $params = input();
        $where = [];
        if (!empty($params['search_input'])){
            $data = \app\adminapi\model\Goods::with('shop')->where('goods_name','like',"%{$params['search_input']}%")->whereOr('keywords','like',"%{$params['search_input']}%")->select();
            if (empty($data)){
                $this->fail('搜索失败,请浏览推荐商品');
            }
            $this->ok($data);
        }else{
            $this->fail('未传递搜索词');
        }
    }
    public function save(Request $request)
    {
        $params = input();
        $validate = $this->validate($params,[
            'search_words|搜索词' => 'require',
            'user_id|用户id' => 'require|integer',
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        if ($params['user_id'] == 0){
            $this->fail('您未登录');
        }
        $data = \app\homeapi\model\SearchGoods::where('search_words','=',$params['search_words'])->where('user_id','=',$params['user_id'])->select();
        if (empty($data)){
            $info = \app\homeapi\model\SearchGoods::create($params,true);
            if (empty($info)){
                $this->fail();
            }
        }
        $this->ok();
    }
}

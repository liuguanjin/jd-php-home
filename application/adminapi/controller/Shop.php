<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;

class Shop extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //获取店铺列表
        $params = input();
        $where = [];
        if (!empty($params['keyword'])){
            $where['shop_name'] = ['like',"%{$params['keyword']}%"];
        }
        $shops = \app\adminapi\model\Shop::with('admin')->where($where)->select();
        if (empty($shops)){
            $this->fail('服务器异常,获取店铺列表失败');
        }
        $this->ok($shops);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //添加商铺
        $params = input();
        $validate = $this->validate($params,[
            'shop_name|店铺名称' => 'require',
            'sort|店铺排序' => 'require',
            'admin_id|所属管理员' => 'require'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        \app\adminapi\model\Shop::create($params,true);
        $this->ok();
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //店铺详情
        $shop = \app\adminapi\model\Shop::find($id);
        if (empty($shop)){
            $this->fail('服务器异常,店铺已不存在');
        }
        unset($shop['create_time']);
        unset($shop['delete_time']);
        unset($shop['update_time']);
        $this->ok($shop);
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //修改商铺
        $params = input();
        $validate = $this->validate($params,[
            'shop_name|店铺名称' => 'require',
            'sort|店铺排序' => 'require',
            'admin_id|所属管理员' => 'require'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        \app\adminapi\model\Shop::update($params,['id'=>$id],true);
        $this->ok();
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //删除店铺
        $goods_total = \app\adminapi\model\Goods::where('shop_id',$id)->count();
        if ($goods_total > 0){
            $this->fail('删除失败,该店铺下尚有商品');
        }
        \app\adminapi\model\Shop::destroy($id);
        $this->ok();
    }
}

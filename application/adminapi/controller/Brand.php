<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;

class Brand extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //品牌查询
        $params = input();
        $where = [];
        if (!empty($params['keyword'])){
            $where['name'] = ['like',"%{$params['keyword']}%"];
        }
        $list = \app\adminapi\model\Brand::where($where)->paginate(10);
        if (empty($list)){
            $this->fail('查询品牌列表失败');
        }
        $this->ok($list);
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
        //新增品牌
        $params = input();
        $validate = $this->validate($params,[
            'name|品牌名称' => 'require',
            'cate_id|所属分类' => 'require',
            'is_hot|是否热门' => 'require',
            'sort|排序' => 'require',
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        if (isset($params['logo']) && !empty($params['logo']) && is_file($params['logo'])){
            \think\Image::open($params['logo'])->thumb(50,50)->save('.'.$params['logo']);
        }
        $brand = \app\adminapi\model\Brand::create($params,true);
        $info = \app\adminapi\model\Brand::find($brand['id']);
        $this->ok($info);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
        $list = \app\adminapi\model\Brand::field('id,name,logo,desc,sort,is_hot,cate_id,url')->find($id);
        if (empty($list)){
            $this->fail('该品牌已不存在，请刷新');
        }
        $this->ok($list);
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
        //修改品牌
        $params = input();
        $validate = $this->validate($params,[
           'name|品牌名称' => 'require',
            'cate_id|所属分类' => 'require',
            'is_hot|是否热门' => 'require',
            'sort|排序' => 'require',
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        \app\adminapi\model\Brand::update($params,['id' => $id],true);
        $brand = \app\adminapi\model\Brand::find($id);
        $this->ok($brand);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //删除品牌
        $total = \app\adminapi\model\Goods::where('brand_id',$id)->count();
        if ($total > 0){
            $this->fail('删除失败，该品牌下还有商品');
        }
        \app\adminapi\model\Brand::destroy($id);
        $this->ok();
    }
    public function getAllBrand()
    {
        $brands = \app\adminapi\model\Brand::field('id,name')->select();
        $this->ok($brands);
    }
}

<?php

namespace app\homeapi\controller;

use think\Controller;

class Category extends BaseApi
{
    //获取一级 二级 三级分类
    public function read()
    {
        $category = \app\adminapi\model\Category::select();
        $category = (new \think\Collection($category))->toArray();
        $category = get_tree_list($category);
        $this->ok($category);
    }

    public function categoryDetail($id="")
    {
        $data = \app\adminapi\model\Goods::with('shop')->where('cate_id',$id)->select();
        $this->ok($data);
    }
}

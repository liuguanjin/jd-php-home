<?php

namespace app\adminapi\controller;

use think\Collection;
use think\Controller;
use think\Request;

class Category extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //获取分类列表
        $params = input();
        $where = [];
        if (!empty($params['keyword'])){
            $where['cate_name'] = ['like',"%{$params['keyword']}%"];
        }elseif (isset($params['pid'])){
            $where['pid'] = $params['pid'];
        }
        $list = \app\adminapi\model\Category::where($where)->select();
        $list = (new Collection($list))->toArray();
        if (empty($params['type']) || $params['type'] != 'list'){
            $list = get_tree_list($list);
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
        //新增分类
        $params = input();
        $validate = $this->validate($params,[
           'cate_name|分类名称' => 'require',
            'pid|父级分类' => 'require',
            'is_show|是否展示' => 'require',
            'is_hot|是否热门' => 'require',
            'sort|排序' => 'require',
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        if ($params['pid'] == 0){
            $params['level'] = 0;
            $params['pid_path_name'] = '';
            $params['pid_path'] = 0;
        }else{
            $pid = \app\adminapi\model\Category::find($params['pid']);
            if (empty($pid)){
                $this->fail('数据异常，请稍后再试');
            }
            $params['pid_path'] = $pid['pid_path'].'_'.$pid['id'];
            $params['level'] = $pid['level'] + 1;
            $params['pid_path_name'] = $pid['pid_path_name'].'_'.$pid['cate_name'];
        }
        $params['image_url'] = isset($params['image_url']) ? $params['image_url'] : '';
        if (isset($params['image_url']) && !empty($params['image_url']) && is_file($params['image_url'])){
            \think\Image::open($params['image_url'])->thumb(50,50)->save('.'.$params['image_url']);
        }
        $category = \app\adminapi\model\Category::create($params,true);
        $info = \app\adminapi\model\Category::find($category['id']);
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
        //分类详情
        $category = \app\adminapi\model\Category::field('id,cate_name,pid,pid_path_name,level,sort,is_show,is_hot,image_url')->find($id);
        if (empty($category)){
            $this->fail('分类已不存在，请刷新');
        }
        $this->ok($category);
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
        //修改分类
        $params = input();
        $validate = $this->validate($params,[
            'cate_name|分类名称' => 'require',
            'pid|父级分类' => 'require',
            'is_show|是否展示' => 'require',
            'is_hot|是否热门' => 'require',
            'sort|排序' => 'require',
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        if ($params['pid'] == 0){
            $params['level'] = 0;
            $params['pid_path_name'] = '';
            $params['pid_path'] = 0;
        }else{
            $pid = \app\adminapi\model\Category::find($params['pid']);
            if (empty($pid)){
                $this->fail('数据异常，请稍后再试');
            }
            $params['pid_path'] = $pid['pid_path'].'_'.$pid['id'];
            $params['level'] = $pid['level'] + 1;
            $params['pid_path_name'] = $pid['pid_path_name'].'_'.$pid['cate_name'];
        }
        if (empty($params['image_url'])){
            unset($params['image_url']);
        }
        $category = \app\adminapi\model\Category::update($params,['id'=>$id],true);
        $info = \app\adminapi\model\Category::find($id);
        $this->ok($info);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //删除分类
        $cate = \app\adminapi\model\Category::find($id);
        if (empty($cate)){
            $this->fail('该分类已不存在,请重新刷新');
        }
        $total = \app\adminapi\model\Category::where('pid',$id)->count();
        if ($total > 0){
            $this->fail('删除失败，该分类下有子分类');
        }
        \app\adminapi\model\Category::destroy($id);
        $this->ok();
    }
}

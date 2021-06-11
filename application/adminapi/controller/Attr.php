<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;

class Attr extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //获取属性列表
        $params = input();
        $where = [];
        if (!empty($params['keyword'])){
            $where['attr_name'] = ['like',"%{$params['keyword']}%"];
        }
        $attr = \app\adminapi\model\Attribute::with('type')->where($where)->select();
        $this->ok($attr);
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
        //添加属性
        $params = input();
        $validate = $this->validate($params,[
            'attr_name|属性名称' => 'require',
            'attr_values|属性值' => 'require',
            'sort|排序' => 'require',
            'type_id|所属模型' => 'require|number',
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        \app\adminapi\model\Attribute::create($params,true);
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
        //属性详情
        $attr = \app\adminapi\model\Attribute::find($id);
        $this->ok($attr);
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
        //更新属性
        $params = input();
        $validate = $this->validate($params,[
            'attr_name|属性名称' => 'require',
            'attr_values|属性值' => 'require',
            'sort|排序' => 'require',
            'type_id|所属模型' => 'require|number',
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        unset($params['create_time']);
        unset($params['delete_time']);
        unset($params['update_time']);
        \app\adminapi\model\Attribute::update($params,['id'=>$id],true);
        $attr = \app\adminapi\model\Attribute::find($id);
        $this->ok($attr);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //删除属性
        \app\adminapi\model\Attribute::destroy($id);
        $this->ok();
    }
}

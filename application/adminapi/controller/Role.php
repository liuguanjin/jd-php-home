<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;

class Role extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //获取角色列表
        $params = input();
        $where = [];
        if (!empty($params['keyword'])){
            $where['role_name'] = ['like',"%{$params['keyword']}%"];
        }
        $list = \app\adminapi\model\Role::where($where)->paginate(4);
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
        //保存新增角色
        $params = input();
        $validate = $this->validate($params,[
           'role_name|角色名称' => 'require',
            'role_auth_ids|拥有权限' => 'require'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        $role = \app\adminapi\model\Role::create($params,true);
        if (empty($role)){
            $this->fail('添加角色失败');
        }
        $info = \app\adminapi\model\Role::find($role['id']);
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
        //获取角色详情
        if ($id == 1){
            $this->fail('超级管理员不允许修改');
        }
        $role = \app\adminapi\model\Role::field('id,role_name,desc,role_auth_ids')->find($id);
        $this->ok($role);
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
        //修改角色
        if ($id == 1){
            $this->fail('超级管理员不允许修改');
        }
        $params = input();
        $validate = $this->validate($params,[
           'role_name|角色名称' => 'require',
           'role_auth_ids|拥有权限' => 'require'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        \app\adminapi\model\Role::update($params,['id'=>$id],true);
        $info = \app\adminapi\model\Role::find($id);
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
        //删除角色
        if ($id == 1){
            $this->fail('超级管理员，无法删除');
        }
        $total = \app\adminapi\model\Admin::where('role_id',$id)->count();
        if ($total > 0){
            $this->fail('该角色尚有管理员在使用，无法删除');
        }
        \app\adminapi\model\Role::destroy($id);
        $this->ok();
    }
    public function getAllRole()
    {
        $list = \app\adminapi\model\Role::select();
        $this->ok($list);
    }
}

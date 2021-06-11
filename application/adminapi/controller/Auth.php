<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;

class auth extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //权限列表页
        $params = input();
        $where = [];
        if (!empty($params['keyword'])){
            $where ['auth_name'] = ['like',"%{$params['keyword']}%"];
        }
        $list = \app\adminapi\model\Auth::where($where)->select();
        //转为标准的二维数组
        $list = (new \think\Collection($list))->toArray();
        if (!empty($params['type']) && $params['type'] == 'tree'){
            $list = get_tree_list($list);
        }else{
            $list = get_cate_list($list);
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
        //添加权限
        $params = input();
        $validate = $this->validate($params,[
            'auth_name|权限名称' => 'require',
            'is_nav|是否为菜单' => 'require|in:0,1',
            'pid|上级权限' => 'require'
        ]);
        if ($validate !== true){
            $this->fail($validate,401);
        }
        if ($params['pid'] == 0){
            $params['level'] = 0;
            $params['pid_path'] = 0;
        }else{
            $pid_info = \app\adminapi\model\Auth::find($params['pid']);
            if (empty($pid_info)){
                $this->fail('数据异常');
            }
            $params['level'] = $pid_info['level'] + 1;
            $params['pid_path'] = $pid_info['pid_path'].'_'.$params['pid'];
        }
        $auth = \app\adminapi\model\Auth::create($params,true);
        $info = \app\adminapi\model\Auth::find($auth['id']);
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
        //权限详情
        $auth = \app\adminapi\model\Auth::field('id,auth_name,pid,pid_path,auth_c,auth_a,is_nav,level')->find($id);
        $this->ok($auth);
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
        //修改权限
        $params = input();
        $validate = $this->validate($params,[
            'auth_name|权限名称' => 'require',
            'is_nav|是否是菜单项' => 'require',
            'pid|父级权限' => 'require'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        if ($params['pid'] == 0){
            $params['level'] = 0;
            $params['pid_path'] = 0;
        }else{
            if ($id == $params['pid']){
                $this->fail('不能修改为自己的权限');
            }
            $pid_auth = \app\adminapi\model\Auth::find($params['pid']);
            $params['level'] = $pid_auth['level'] + 1;
            $params['pid_path'] = $pid_auth['pid_path'] . '_' . $pid_auth['id'];
        }
        \app\adminapi\model\Auth::update($params,['id'=>$id],true);
        $info = \app\adminapi\model\Auth::find($id);
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
        //删除权限
        $total = \app\adminapi\model\Auth::where('pid',$id)->count();
        if ($total > 0){
            $this->fail('删除失败,该权限尚有子级权限');
        }
        \app\adminapi\model\Auth::destroy($id);
        $this->ok('删除成功');
    }
    public function nav()
    {
        $user_id = input('user_id');
        $admin = \app\adminapi\model\Admin::find($user_id);
        $role_id = $admin['role_id'];
        if ($role_id == 1){
            $list = \app\adminapi\model\Auth::where('is_nav',1)->select();
        }else{
            $role = \app\adminapi\model\role::find($role_id);
            $role_auth_ids = $role['role_auth_ids'];
            $list = \app\adminapi\model\Auth::where('is_nav',1)->where('id','in',$role_auth_ids)->select();
        }
        $data = (new \think\Collection($list))->toArray();
        $data = get_tree_list($data);
        $this->ok($data);
    }
}

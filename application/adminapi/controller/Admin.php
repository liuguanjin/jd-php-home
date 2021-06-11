<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;

class Admin extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //获取管理员列表
        $params = input();
        $where = [];
        if (!empty($params['keyword'])){
            $where['username'] = ['like',"%{$params['keyword']}%"];
        }
        $list = \app\adminapi\model\Admin::alias('t1')
                ->join('jd_role t2','t1.role_id=t2.id','left')
                ->field('t1.*,t2.role_name')
                ->where($where)
                ->paginate(4);
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
        //新增管理员
        $params = input();
        $validate = $this->validate($params,[
            'username|管理员名称' => 'require|unique:admin',
            'email|邮箱' => 'require|email',
            'role_id' => 'require|integer|gt:0',
            'password|密码' => 'length:6,20|^[a-zA-Z]\w{5,17}$'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        if (empty($params['password'])){
            $params['password'] = 'lgj123456';
        }
        $params['password'] = encrypt_password($params['password']);
        if (empty($params['nickname'])){
            $params['nickname'] = $params['username'];
        }
        $info = \app\adminapi\model\Admin::create($params,true);
        $data = \app\adminapi\model\Admin::find($info['id']);
        $this->ok($data);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //管理员详情
        $admin = \app\adminapi\model\Admin::field('id,username,email,nickname,status,last_login_time,role_id')->find($id);
        $this->ok($admin);
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
        //修改管理员
        if ($id == 1){
            $this->fail('超级管理员，不允许修改');
        }
        $params = input();
        $validate = $this->validate($params,[
            'email|邮箱' => 'require|email',
            'role_id' => 'require|integer|gt:0',
            'password|密码' => 'length:6,20|^[a-zA-Z]\w{5,17}$'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        if (empty($params['password'])){
            $params['password'] = 'lgj123456';
        }
        //用户名不允许修改
        unset($params['username']);
        $params['password'] = encrypt_password($params['password']);
        \app\adminapi\model\Admin::update($params,['id'=>$id],true);
        $info = \app\adminapi\model\Admin::field('id,username,email,nickname,last_login_time,role_id,status')->find($id);
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
        //删除管理员
        if ($id == 1){
            $this->fail('超级管理员，不允许删除');
        }
        if ($id == input('user_id')){
            $this->fail('不能删除自己!!!');
        }
        \app\adminapi\model\Admin::destroy($id);
        $this->ok();
    }
    public function allAdmin()
    {
        $admin = \app\adminapi\model\Admin::select();
        $this->ok($admin);
    }
}

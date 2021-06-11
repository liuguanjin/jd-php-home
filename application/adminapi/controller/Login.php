<?php

namespace app\adminapi\controller;

use think\Controller;

class Login extends BaseApi
{
    public function homeRegister()
    {
        //接收参数
        $params = input();
        //参数检测
        $validate = $this->validate($params,[
            'username|用户名' => 'require',
            'password|密码' => 'require',
            'code|验证码' => 'require',
            'uniqid' => 'require'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        session_id(cache('session_id_'.$params['uniqid']));
        if (!captcha_check($params['code'],$params['uniqid'])){
            $this->fail('验证码错误');
        }
        $where = [
            'username' => $params['username'],
        ];
        $info = \app\adminapi\model\User::where($where)->find();
        if (!empty($info)){
            $this->fail('用户名已存在');
        }
        $params['password'] = encrypt_password($params['password']);
        $user = \app\adminapi\model\User::create($params,true);
        $data = \app\adminapi\model\User::find($user['id']);
        $this->ok($data);
    }
    public function adminRegister()
    {
        //接收参数
        $params = input();
        //参数检测
        $validate = $this->validate($params,[
            'username|用户名' => 'require',
            'password|密码' => 'require',
            'code|验证码' => 'require',
            'uniqid' => 'require'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        session_id(cache('session_id_'.$params['uniqid']));
        if (!captcha_check($params['code'],$params['uniqid'])){
            $this->fail('验证码错误');
        }
        $where = [
            'username' => $params['username'],
        ];
        $info = \app\adminapi\model\Admin::where($where)->find();
        if (!empty($info)){
            $this->fail('用户名已存在');
        }
        $params['password'] = encrypt_password($params['password']);
        $user = \app\adminapi\model\Admin::create($params,true);
        $data = \app\adminapi\model\Admin::find($user['id']);
        $this->ok($data);
    }
    public function homeLogin()
    {
        //接收参数
        $params = input();
        //参数检测
        $validate = $this->validate($params,[
            'username|用户名' => 'require',
            'password|密码' => 'require|^[a-zA-Z]\w{5,17}$',
            'code|验证码' => 'require',
            'uniqid' => 'require'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        session_id(cache('session_id_'.$params['uniqid']));
        if (!captcha_check($params['code'],$params['uniqid'])){
            $this->fail('验证码错误');
        }
        $where = [
            'username' => $params['username'],
            'password' => encrypt_password($params['password'])
        ];
        $info = \app\adminapi\model\User::where($where)->find();
        if (!$info){
            $this->fail('用户名或密码错误');
        }
        $data['token'] = \tools\jwt\Token::getToken($info->id);
        $data['user_id'] = $info->id;
        $data['username'] = $info->username;
        $data['nickname'] = $info->nickname;
        $data['email'] = $info->email;
        $this->ok($data);
    }
    public function homeLogout()
    {
        $token = \tools\jwt\Token::getRequestToken();
        $delete_token = cache('delete_token') ?: [];
        $delete_token[] = $token;
        cache('delete_token',$delete_token,86400);
        $this->ok();
    }
    public function adminLogin()
    {
        //接收参数
        $params = input();
        //参数检测
        $validate = $this->validate($params,[
            'username|用户名' => 'require',
            'password|密码' => 'require|^[a-zA-Z]\w{5,17}$',
            'code|验证码' => 'require',
            'uniqid' => 'require'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        session_id(cache('session_id_'.$params['uniqid']));
        if (!captcha_check($params['code'],$params['uniqid'])){
            $this->fail('验证码错误');
        }
        $where = [
            'username' => $params['username'],
            'password' => encrypt_password($params['password'])
        ];
        $info = \app\adminapi\model\Admin::where($where)->find();
        if (!$info){
            $this->fail('用户名或密码错误');
        }
        //生成token令牌
        $token = \tools\jwt\Token::getToken($info['id']);
        $data = [
            'token' => $token,
            'admin_id' => $info['id'],
            'username' => $info['username'],
            'nickname' => $info['nickname'],
            'email' => $info['email'],
        ];
        $this->ok($data);
    }
    public function adminLogout()
    {
        $token = \tools\jwt\Token::getRequestToken();
        $delete_token = cache('delete_token') ?: [];
        $delete_token[] = $token;
        cache('delete_token',$delete_token,86400);
        $this->ok();
    }
    public function captcha()
    {
        $uniqid = uniqid(mt_rand(100000,999999));
        $data = [
            'src' => captcha_src($uniqid),
            'uniqid' => $uniqid
        ];
        $this->ok($data);
    }
}

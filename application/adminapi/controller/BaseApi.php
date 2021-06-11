<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Exception;

class BaseApi extends Controller
{
    protected $no_login = ['login/adminlogin','login/adminregister','login/adminlogout','login/homelogout','login/homelogin','login/homeregister','login/captcha'];
    public function _initialize()
    {
        parent::_initialize();
        //处理跨域请求
        //允许的源域名
        header("Access-Control-Allow-Origin: *");
        //允许的请求头信息
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
        //允许的请求类型
        header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH');
        try {
            $path = strtolower($this->request->controller()).'/'.$this->request->action();
            if (!in_array($path,$this->no_login)){
                $token = \tools\jwt\Token::getRequestToken();
                $user_id = \tools\jwt\Token::getUserId();
                if (empty($user_id)){
                    $this->fail('未登录或token无效',403);
                }
                $this->request->get(['user_id'=>$user_id]);
                $this->request->post(['user_id'=>$user_id]);
                $auth_check = \app\adminapi\logic\AuthLogic::check();
                if (!$auth_check){
                    $this->fail('没有权限访问');
                }
            }
        }catch (\Exception $e){
            $this->fail($e->getMessage(),403);
        }
    }
    public function response($code=200,$msg='success',$data=[])
    {
        $res = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ];
        json($res)->send();die;
    }
    public function fail($msg='fail',$code=500)
    {
        return $this->response($code,$msg);
    }
    public function ok($data=[],$code=200,$msg='success')
    {
        return $this->response($code,$msg,$data);
    }
}

<?php

namespace app\homeapi\controller;

use think\Controller;

class BaseApi extends Controller
{
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
    public function fail($msg='fail',$code=500,$data=[])
    {
        return $this->response($code,$msg,$data);
    }
    public function ok($data=[],$code=200,$msg='success')
    {
        return $this->response($code,$msg,$data);
    }
}

<?php

namespace app\homeapi\controller;

use think\Controller;

class Sign extends BaseApi
{
    public function index()
    {
        $id = input('id');
        $userinfo = \app\adminapi\model\User::find($id);
        $sign = $userinfo['sign'];
        $this->ok($sign);
    }
    public function save()
    {
        $id = input('id');
        $sign = input('sign');
        \app\adminapi\model\User::where('id',$id)->setField('sign',$sign);
        $this->ok();
    }
}

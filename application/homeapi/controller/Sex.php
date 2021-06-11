<?php

namespace app\homeapi\controller;

use think\Controller;

class Sex extends BaseApi
{
    public function save()
    {
        $id = input('id');
        $sex = input('sex');
        \app\adminapi\model\User::where('id',$id)->setField('sex',$sex);
        $this->ok();
    }
}

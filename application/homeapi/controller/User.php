<?php

namespace app\homeapi\controller;

use think\Controller;

class User extends BaseApi
{
    public function userDetail($id="")
    {
        $user = \app\adminapi\model\User::find($id);
        $this->ok($user);
    }
    public function avatar($id="")
    {
        $params = input();
        unset($params['/avatar/2']);
        unset($params['id']);
        \app\adminapi\model\User::where('id',$id)->setField('avatar',$params['avatar_url']);
        $this->ok();
    }
}

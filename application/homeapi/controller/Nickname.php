<?php

namespace app\homeapi\controller;

use think\Controller;

class Nickname extends BaseApi
{
    //获取昵称
    public function index(){
        $id = input('id');
        $userinfo = \app\adminapi\model\User::find($id);
        $nickname = $userinfo['nickname'];
        $recommend_keyword = ['123','666','2021','响当当','加油呀','198','棒棒哒','最帅','努力','1','6','22','66'];
        $nickname_recommend=[];
        foreach ($recommend_keyword as $v){
            $nickname_recommend[] = $nickname.$v;
        }
        foreach ($nickname_recommend as $k=>$v){
            if (strlen($v)>21){
                unset($nickname_recommend[$k]);
                continue;
            }
            $user = \app\adminapi\model\User::where('username',$v)->select();
            if ($user){
                unset($nickname_recommend[$k]);
                continue;
            }
        }
        $nickname_recommend = array_splice($nickname_recommend,0,6);
        $data['nickname'] = $nickname;
        $data['nickname_recommend'] = $nickname_recommend;
        $this->ok($data);
    }
    public function checkNickname()
    {
        $params = input();
        $nickname = $params['nickname'];
        $nickname_recommend = [];
        if (strlen($nickname)>21){
            $nickname_recommend[] = mb_substr($nickname,0,1,'UTF-8');
            $nickname_recommend[] = mb_substr($nickname,0,2,'UTF-8');
            $nickname_recommend[] = mb_substr($nickname,0,3,'UTF-8');
            $nickname_recommend[] = mb_substr($nickname,0,4,'UTF-8');
            $nickname_recommend[] = mb_substr($nickname,0,5,'UTF-8');
            $nickname_recommend[] = mb_substr($nickname,0,6,'UTF-8');
            $nickname_recommend[] = mb_substr($nickname,0,7,'UTF-8');
            foreach ($nickname_recommend as $k=>$v){
                $user_info = \app\adminapi\model\User::where('nickname',$v)->select();
                if ($user_info){
                    unset($nickname_recommend[$k]);
                }
            }
            $this->fail('昵称违规,限7个中文,14个字符以内',400,$nickname_recommend);
        }
        $nickname_in_user = \app\adminapi\model\User::where('nickname',$nickname)->select();
        $recommend_keyword = ['123','666','2021','响当当','加油呀','198','棒棒哒','最帅','努力','1','6','22','66'];
        if ($nickname_in_user){
            foreach ($recommend_keyword as $v){
                $nickname_recommend[] = $nickname.$v;
            }
            foreach ($nickname_recommend as $k=>$v){
                if (strlen($v)>21){
                    unset($nickname_recommend[$k]);
                    continue;
                }
                $user = \app\adminapi\model\User::where('username',$v)->select();
                if ($user){
                    unset($nickname_recommend[$k]);
                    continue;
                }
            }
            $this->fail('该昵称已存在',400,$nickname_recommend);
        }
        foreach ($recommend_keyword as $v){
            $nickname_recommend[] = $nickname.$v;
        }
        foreach ($nickname_recommend as $k=>$v){
            if (strlen($v)>21){
                unset($nickname_recommend[$k]);
                continue;
            }
            $user = \app\adminapi\model\User::where('username',$v)->select();
            if ($user){
                unset($nickname_recommend[$k]);
                continue;
            }
        }
        $nickname_recommend = array_splice($nickname_recommend,0,6);
        $this->ok($nickname_recommend);
    }
    public function save()
    {
        $id = input('id');

        $nickname = input('nickname');
        if (strlen($nickname)>21){
            $this->fail('昵称违规,请重新设置昵称');
        }
        $user_info = \app\adminapi\model\User::where('nickname',$nickname)->select();
        if ($user_info){
            $this->fail('昵称已存在,请重新设置昵称');
        }
        \app\adminapi\model\User::where('id',$id)->setField('nickname',$nickname);
        $this->ok();
    }
}

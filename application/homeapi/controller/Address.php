<?php

namespace app\homeapi\controller;

use think\Controller;
use think\Request;

class Address extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //获取该用户下所有收货地址
        $user_id = input('user_id');
        if (empty($user_id)){
            $this->fail('用户id违法');
        }
        $page = input('page');
        if (!empty($page) && $page == 'balance'){
            $address_all = \app\homeapi\model\Address::where('user_id',$user_id)->order('last_use','desc')->select();
            $address_all = (new \think\Collection($address_all))->toArray();
            $address = [];
            foreach ($address_all as $k=>$v){
                if ($v['is_default'] == 1){
                    $address = $v;
                    break;
                }else{
                    if ($v['last_use'] == 1){
                        $address = $v;
                    }
                }
            }
        }else{
            $address = \app\homeapi\model\Address::where('user_id',$user_id)->order('is_default','desc')->select();
        }
        if (empty($address)){
            $this->fail('用户暂无收货地址');
        }
        $this->ok($address);
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
        //添加收货地址
        $params = input();
        $validate = $this->validate($params,[
            'user_id|用户id' => 'require|integer',
            'consignee|收货人' => 'require',
            'phone|手机号' => 'require|^1[0-9]{10}$',
            'province|省份编码' => 'require|integer',
            'city|城市编码' => 'require|integer',
            'county|县区编码' => 'require|integer',
            'town|街道编码' => 'require|integer',
            'village|社区编码' => 'require|integer',
            'area|省市县街道' => 'require',
            'address|详细地址' => 'require',
            'is_default|是否默认' => 'require',
            'sign|地址所属标签' => 'require'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        \think\Db::startTrans();
        try {
            if ($params['is_default'] == 1){
                $allAddress = \app\homeapi\model\Address::where('user_id',$params['user_id'])->select();
                foreach ($allAddress as $k=>$v){
                    $allAddress[$k]['is_default'] = 0;
                    unset($allAddress[$k]['id']);
                    unset($allAddress[$k]['update_time']);
                    unset($allAddress[$k]['create_time']);
                    unset($allAddress[$k]['delete_time']);
                }
                \app\homeapi\model\Address::destroy(['user_id'=>$params['user_id']]);
                $allAddress = (new \think\Collection($allAddress))->toArray();
                $address_model = new \app\homeapi\model\Address();
                $address_model->allowField(true)->saveAll($allAddress);
            }
            $info = \app\homeapi\model\Address::create($params,true);
            $data = \app\homeapi\model\Address::find($info['id']);
            \think\Db::commit();
            $this->ok($data);

        }catch (\Exception $e){
            \think\Db::rollback();
            $this->fail('操作失败');
        }
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //地址详情 编辑地址时使用
        $address = \app\homeapi\model\Address::find($id);
        if (empty($address)){
            $this->fail('地址信息不存在');
        }
        $this->ok($address);
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
        //修改收货地址
        $params = input();
        $validate = $this->validate($params,[
            'user_id|用户id' => 'require|integer',
            'consignee|收货人' => 'require',
            'phone|手机号' => 'require|^1[0-9]{10}$',
            'province|省份编码' => 'require|integer',
            'city|城市编码' => 'require|integer',
            'county|县区编码' => 'require|integer',
            'town|街道编码' => 'require|integer',
            'village|社区编码' => 'require|integer',
            'area|省市县街道' => 'require',
            'address|详细地址' => 'require',
            'is_default|是否默认' => 'require',
            'sign|地址所属标签' => 'require'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        unset($params['update_time']);
        unset($params['create_time']);
        unset($params['delete_time']);
        $info = \app\homeapi\model\Address::update($params,['id'=>$id],true);
        if ($params['is_default'] == 1){
            $allAddress = \app\homeapi\model\Address::where('user_id',$params['user_id'])->select();
            $allAddress = (new \think\Collection($allAddress))->toArray();
            foreach ($allAddress as $k=>$v){
                if ($v['id'] == $id){
                    continue;
                }
                $allAddress[$k]['is_default'] = 0;
                unset($allAddress[$k]['update_time']);
                unset($allAddress[$k]['create_time']);
                unset($allAddress[$k]['delete_time']);
                \app\homeapi\model\Address::update($allAddress[$k],['id'=>$allAddress[$k]['id']],true);
            }
        }
        $this->ok();
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //删除收货地址
        \app\homeapi\model\Address::destroy($id);
        $this->ok();
    }
}

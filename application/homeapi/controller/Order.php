<?php

namespace app\homeapi\controller;

use app\homeapi\model\OrderGoods;
use think\Controller;
use think\Model;
use think\Request;

class Order extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //获取所有订单
        $user_id = input('user_id');
        $order = \app\homeapi\model\Order::with('order_goods')->where('user_id',$user_id)->order('create_time desc')->select();
        $this->ok($order);
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
        //提交订单
        $params = input();
        $validate = $this->validate($params,[
            'order_sn|订单编号' => 'require',
            'user_id|用户id' => 'require|integer',
            'order_status|订单状态' => 'require',
            'consignee|收货人' => 'require',
            'address|收货地址' => 'require',
            'phone|联系人电话' => 'require',
            'goods_price|商品总价' => 'require',
            'order_amount|应付金额' => 'require',
            'total_amount|订单总价' => 'require',
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        \think\Db::startTrans();
        try {
            $info = \app\homeapi\model\Order::create($params,true);
            foreach ($params['goods_ids'] as $k=>$v){
                $order_goods = [];
                $order_goods[$k]['goods_id'] = $v['goods_id'];
                $goods = \app\adminapi\model\Goods::find($v['goods_id']);
                $order_goods[$k]['goods_name'] = $goods['goods_name'];
                $order_goods[$k]['shop_id'] = $goods['shop_id'];
                $order_goods[$k]['goods_logo'] = $goods['goods_logo'];
                $order_goods[$k]['goods_price'] = $goods['goods_price'];
                $order_goods[$k]['user_id'] = $v['user_id'];
                $order_goods[$k]['number'] = $v['number'];
                $order_goods[$k]['spec_goods_id'] = $v['spec_goods_id'];
                $order_goods[$k]['order_id'] = $info['id'];
                $order_goods[$k]['status'] = 0;
                $order_goods[$k]['is_comment'] = 0;
                $spec_goods = \app\adminapi\model\SpecGoods::find($v['spec_goods_id']);
                $order_goods[$k]['spec_value_names'] = $spec_goods['value_names'];
                $order_goods[$k]['spec_price'] = $spec_goods['price'];
                $shop = \app\adminapi\model\Shop::find($goods['shop_id']);
                $order_goods[$k]['shop_name'] = $shop['shop_name'];
                $order_goods_model = new \app\homeapi\model\OrderGoods();
                $order_goods_model->allowField(true)->saveAll($order_goods);
                \app\adminapi\model\Goods::where('id','=',$v['goods_id'])->setField('sales',$goods['sales']+$spec_goods['price']*$v['number']);
                $where = [];
                $where['spec_goods_id'] = ['=',$v['spec_goods_id']];
                $where['user_id'] = ['=',$v['user_id']];
                \app\adminapi\model\Cart::where($where)->delete();
                \app\adminapi\model\Goods::where('id','=',$v['goods_id'])->setInc('sales_num');
                \app\adminapi\model\Shop::where('id','=',$goods['shop_id'])->setInc('sales_num');
            }
            $data = \app\homeapi\model\Order::find($info['id']);
            \think\Db::commit();
            $this->ok($data);
        }catch (\Exception $e){
            \think\Db::rollback();
            $this->fail($e->getMessage());
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
        //
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
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
    public function notify()
    {
        $params = input();
        dump($params);die();
    }
    public function callback()
    {
        $params = input();
        require_once 'plugins/apply/config.php';
        require_once 'plugins/apply/pagepay/service/AlipayTradeService.php';
        $alipayService = new \AlipayTradeService($config);
        $result = $alipayService->check($params);
        if ($result){
            $order_sn = $params['out_trade_no'];
            \app\homeapi\model\Order::where('order_sn',$order_sn)->setField('order_status',1);
            $order = \app\homeapi\model\Order::where('order_sn',$order_sn)->find();
            \app\homeapi\model\OrderGoods::where('order_id',$order['id'])->setField('status',1);
            $this->ok('订单已支付');
        }else{
            $this->fail('订单支付失败');
        }
    }
    public function orderGoods($id="")
    {
        $order_goods = \app\homeapi\model\OrderGoods::with('goods')->find($id);
        if (empty($order_goods)){
            $this->fail('数据错误,请返回');
        }
        $this->ok($order_goods);
    }
    //收货逻辑
    public function acceptGoods($id="")
    {
        \app\homeapi\model\OrderGoods::where('id',$id)->setField('status',3);
        $this->ok();
    }
    //用户提醒逻辑 将提醒状态置为1
    public function remindGoods($id="")
    {
        \app\homeapi\model\OrderGoods::where('id',$id)->setField('is_remind',1);
        $this->ok();
    }
}

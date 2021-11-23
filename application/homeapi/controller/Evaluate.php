<?php

namespace app\homeapi\controller;

use think\Controller;

class Evaluate extends BaseApi
{
    /*
     * 用于获取某条评论
     * @param $id 评论的id
     */
    public function index($id)
    {

    }
    /**
     * 保存评论内容
     * @param  $id 订单商品ordergoods的id
     */
    public function save($id)
    {
        $params = input();
        $validate = $this->validate($params,[
            'goods_id|商品id'=> 'require',
            'shop_id|商铺id'=> 'require',
            'user_id|用户id' => 'require',
            'content|评论内容' => 'require',
            'evaluate_images|评论图片' => 'array'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        \think\Db::startTrans();
        try {
            //新增评论表的一条数据
            $evaluate_data['goods_id'] = $params['goods_id'];
            $evaluate_data['shop_id'] = $params['shop_id'];
            $evaluate_data['user_id'] = $params['user_id'];
            $evaluate_data['content'] = $params['content'];
            $evaluate_data['evaluate_grade'] = $params['evaluate_grade'];
            $evaluate_data['evaluate_describe_score'] = $params['evaluate_describe_score'];
            $evaluate_data['evaluate_logistics_score'] = $params['evaluate_logistics_score'];
            $evaluate_data['evaluate_server_score'] = $params['evaluate_server_score'];
            $evaluate_data['is_choice'] = $params['is_choice'];
            $evaluate = \app\homeapi\model\Evaluate::create($evaluate_data,true);
            //将评论的描述分数 物流分数 服务分数添加更新到店铺表中
            $shop = \app\adminapi\model\Shop::find($params['shop_id']);
            $describe_score = $shop['describe_score'] + $params['evaluate_describe_score'];
            $logistics_score = $shop['logistics_score'] + $params['evaluate_logistics_score'];
            $server_score = $shop['server_score'] + $params['evaluate_server_score'];
            $score = $shop['score'] + round(($params['evaluate_describe_score']+$params['evaluate_logistics_score']+$params['evaluate_server_score'])/3,1);
            $score_people = $shop['score_people'] + 1;
            $update_array = [
                'describe_score' => $describe_score,
                'logistics_score' => $logistics_score,
                'server_score' => $server_score,
                'score' => $score,
                'score_people' => $score_people
            ];
            \app\adminapi\model\Shop::where('id',$params['shop_id'])->update($update_array);
            //将上传的评论图片保存到评论图片表中
            $evaluate_images = [];
            foreach ($params['evaluate_images'] as $image){
                if (is_file('.'.$image)){
                    $pics_big = dirname($image).DS.'thumb_800_'.basename($image);
                    $pics_sma = dirname($image).DS.'thumb_400_'.basename($image);
                    $image_obj = \think\Image::open('.'.$image);
                    $image_obj->thumb(800,800)->save('.'.$pics_big);
                    $image_obj->thumb(400,400)->save('.'.$pics_sma);
                    $row = [
                        'evaluate_id' => $evaluate['id'],
                        'pics_big' => $pics_big,
                        'pics_sma' => $pics_sma
                    ];
                    $evaluate_images [] = $row;
                }
            }
            $evaluate_images_model = new \app\homeapi\model\EvaluateImages();
            $evaluate_images_model->allowField(true)->saveAll($evaluate_images);
            //更新订单商品的状态
            \app\homeapi\model\OrderGoods::where('id',$id)->setField('status',4);
            \think\Db::commit();
            $this->ok();
        }catch (\Exception $e){
            \think\Db::rollback();
            $this->fail($e->getMessage());
        }
    }
    /*
     * 用于获取某商品下的评论集合
     * @param $id int 商品的id
     */
    public function goodsEvaluate($id)
    {
        if (empty($id)){
            $this->fail('商品id不合法');
        }
        $evaluate = \app\homeapi\model\Evaluate::alias('t1')
        ->join('jd_user t2','t1.user_id=t2.id','left')
        ->field('t1.*,t2.nickname,t2.avatar')
        ->where('goods_id',$id)
        ->with('evaluate_images')
        ->order('is_choice','desc')
        ->paginate(10);
        $this->ok($evaluate);
    }
}

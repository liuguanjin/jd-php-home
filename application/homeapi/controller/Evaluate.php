<?php

namespace app\homeapi\controller;

use think\Controller;

class Evaluate extends BaseApi
{
    public function save($id="")
    {
        $params = input();
        $validate = $this->validate($params,[
            'goods_id|商品id'=> 'require',
            'user_id|用户id' => 'require',
            'content|评论内容' => 'require',
            'evaluate_images|评论图片' => 'array'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        \think\Db::startTrans();
        try {
            $evaluate_time = time();
            $evaluate_data['goods_id'] = $params['goods_id'];
            $evaluate_data['user_id'] = $params['user_id'];
            $evaluate_data['content'] = $params['content'];
            $evaluate_data['evaluate_time'] = $evaluate_time;
            $evaluate = \app\homeapi\model\Evaluate::create($evaluate_data,true);
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
            \app\homeapi\model\OrderGoods::where('id',$id)->setField('status',4);
            \think\Db::commit();
            $this->ok();
        }catch (\Exception $e){
            \think\Db::rollback();
            $this->fail($e->getMessage());
        }
    }
}

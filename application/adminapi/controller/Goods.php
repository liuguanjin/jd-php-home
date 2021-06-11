<?php

namespace app\adminapi\controller;

use app\adminapi\model\GoodsImages;
use think\Controller;
use think\Request;

class Goods extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //获取商品列表
        $params = input();
        $where = [];
        if (!empty($params['keyword'])){
            $where['goods_name'] = ['like',"%{$params['keyword']}%"];
        }
        $list = \app\adminapi\model\Goods::with('category,brand,type,shop_row')
            ->where($where)
            ->order('id asc')
            ->paginate(10);
        $this->ok($list);
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
        //添加商品
        $params = input();
        $validate = $this->validate($params,[
            'goods_name|商品名称' => 'require',
            'goods_price|商品价格' => 'require',
            'goods_logo|商品logo' => 'require',
            'market_price|市场价' => 'require',
            'cost_price|成本价' => 'require',
            'goods_number|商品库存' => 'require',
            'cate_id|所属分类' => 'require|number',
            'brand_id|所属品牌' => 'require|number',
            'type_id|所属模型' => 'require|number',
            'item|规格商品数组' => 'require|array',
            'goods_images|商品相册' => 'require|array',
            'attr|属性值数组' => 'require|array'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        \think\Db::startTrans();
        try {
            if (is_file('.'.$params['goods_logo'])){
                $goods_logo = dirname($params['goods_logo']).DS.'thumb'.basename($params['goods_logo']);
                \think\Image::open('.'.$params['goods_logo'])->thumb(210,240)->save('.'.$goods_logo);
                $params['goods_logo'] = $goods_logo;
            }
            $params['goods_attr'] = json_encode($params['attr'],JSON_UNESCAPED_UNICODE);
            $goods = \app\adminapi\model\Goods::create($params,true);
            $goods_images = [];
            foreach ($params['goods_images'] as $image){
                if (is_file('.'.$image)){
                    $pics_big = dirname($image).DS.'thumb_800_'.basename($image);
                    $pics_sma = dirname($image).DS.'thumb_400_'.basename($image);
                    $image_obj = \think\Image::open('.'.$image);
                    $image_obj->thumb(800,800)->save('.'.$pics_big);
                    $image_obj->thumb(400,400)->save('.'.$pics_sma);
                    $row = [
                        'goods_id' => $goods['id'],
                        'pics_big' => $pics_big,
                        'pics_sma' => $pics_sma
                    ];
                    $goods_images [] = $row;
                }
            }
            $goods_images_model = new \app\adminapi\model\GoodsImages();
            $goods_images_model->allowField(true)->saveAll($goods_images);
            $spec_goods = [];
            foreach ($params['item'] as $v){
                $v['goods_id'] = $goods['id'];
                $spec_goods[] = $v;
            }
            $spec_goods_model = new \app\adminapi\model\SpecGoods();
            $spec_goods_model->allowField(true)->saveAll($spec_goods);
            \think\Db::commit();
            $info = \app\adminapi\model\Goods::with('category,brand,type')->find($goods['id']);
            $this->ok($info);
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
        //商品详情
        $goods = \app\adminapi\model\Goods::with('category_row,brand_row,goods_images,spec_goods,shop_row')->find($id);
        $goods['category'] = $goods['category_row'];
        unset($goods['category_row']);
        $goods['brand'] = $goods['brand_row'];
        unset($goods['brand_row']);
        $type = \app\adminapi\model\Type::with('specs,specs.spec_values,attrs')->find($goods['type_id']);
        $goods['type'] = $type;
        $this->ok($goods);
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
        //修改商品
        $params = input();
        $validate = $this->validate($params,[
            'goods_name|商品名称' => 'require',
            'goods_price|商品价格' => 'require',
            'goods_logo|商品logo' => 'require',
            'market_price|市场价' => 'require',
            'cost_price|成本价' => 'require',
            'goods_number|商品库存' => 'require',
            'cate_id|所属分类' => 'require|number',
            'brand_id|所属品牌' => 'require|number',
            'type_id|所属模型' => 'require|number',
            'item|规格商品数组' => 'require|array',
            'goods_images|商品相册' => 'require|array',
            'attr|属性值数组' => 'require|array'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        \think\Db::startTrans();
        try {
            unset($params['create_time']);
            unset($params['delete_time']);
            unset($params['update_time']);
            $old_goods = \app\adminapi\model\Goods::find($id);
            $old_goods_logo = $old_goods['goods_logo'];
            if (is_file('.'.$old_goods_logo)){
                unlink('.'.$old_goods_logo);
            }
            if (is_file('.'.$params['goods_logo'])){
                $goods_logo = dirname($params['goods_logo']).DS.'thumb'.basename($params['goods_logo']);
                \think\Image::open('.'.$params['goods_logo'])->thumb(210,240)->save('.'.$goods_logo);
                $params['goods_logo'] = $goods_logo;
            }
            $params['goods_attr'] = json_encode($params['attr'],JSON_UNESCAPED_UNICODE);
            $goods = \app\adminapi\model\Goods::update($params,['id'=>$id],true);
            $old_goods_images = \app\adminapi\model\GoodsImages::select(['goods_id'=>$id]);
            foreach ($old_goods_images as $old_goods_image){
                if (is_file($old_goods_image['pics_big'])){
                    unlink('.'.$old_goods_image['pics_big']);
                }
                if (is_file($old_goods_image['pics_sma'])){
                    unlink('.'.$old_goods_image['pics_sma']);
                }
            }
            \app\adminapi\model\GoodsImages::destroy(['goods_id'=>$id]);
            $goods_images = [];
            if (!is_array($params['goods_images'])){
                $this->fail('商品相册未上传');
            }
            foreach ($params['goods_images'] as $image){
                if (is_file('.'.$image)){
                    $pics_big = dirname($image).DS.'thumb_800_'.basename($image);
                    $pics_sma = dirname($image).DS.'thumb_400_'.basename($image);
                    $image_obj = \think\Image::open('.'.$image);
                    $image_obj->thumb(800,800)->save('.'.$pics_big);
                    $image_obj->thumb(400,400)->save('.'.$pics_sma);
                    $row = [
                        'goods_id' => $goods['id'],
                        'pics_big' => $pics_big,
                        'pics_sma' => $pics_sma
                    ];
                    $goods_images [] = $row;
                }
            }
            $goods_images_model = new \app\adminapi\model\GoodsImages();
            $goods_images_model->allowField(true)->saveAll($goods_images);
            \app\adminapi\model\SpecGoods::destroy(['goods_id'=>$id]);
            $spec_goods = [];
            foreach ($params['item'] as $v){
                $v['goods_id'] = $goods['id'];
                $spec_goods[] = $v;
            }
            $spec_goods_model = new \app\adminapi\model\SpecGoods();
            $spec_goods_model->allowField(true)->saveAll($spec_goods);
            \think\Db::commit();
            $goods = \app\adminapi\model\Goods::with('category_row,brand_row,goods_images,spec_goods')->find($id);
            $goods['category'] = $goods['category_row'];
            unset($goods['category_row']);
            $goods['brand'] = $goods['brand_row'];
            unset($goods['brand_row']);
            $type = \app\adminapi\model\Type::with('specs,specs.spec_values,attrs')->find($goods['type_id']);
            $goods['type'] = $type;
            $this->ok($goods);
        }catch (\Exception $e){
            \think\Db::rollback();
            $this->fail($e->getLine());
        }
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //删除商品
        $goods = \app\adminapi\model\Goods::find($id);
        if (empty($goods)){
            $this->fail('商品已不存在，数据异常');
        }
        if ($goods['is_on_sale']){
            $this->fail('上架中，无法删除');
        }
        \think\Db::startTrans();
        try {
            $goods->delete();
            $good_images = \app\adminapi\model\GoodsImages::select(['goods_id'=>$id]);
            foreach ($good_images as $image){
                if (is_file($image['pics_big'])){
                    unlink('.'.$image['pics_big']);
                }
                if (is_file($image['pics_sma'])){
                    unlink('.'.$image['pics_sma']);
                }
            }
            \app\adminapi\model\GoodsImages::destroy(['goods_id'=>$id]);
            \app\adminapi\model\SpecGoods::destroy(['goods_id'=>$id]);
            \think\Db::commit();
            $this->ok();
        }catch (\Exception $e){
            \think\Db::rollback();
            $this->fail($e->getMessage());
        }
    }
}

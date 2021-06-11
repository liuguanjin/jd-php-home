<?php

namespace app\adminapi\controller;

use app\adminapi\model\Attribute;
use app\adminapi\model\SpecValue;
use think\Controller;
use think\Exception;
use think\Request;

class Type extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //获取模型列表
        $params = input();
        $where = [];
        if (!empty($params['keyword'])){
            $where['type_name'] = ['like',"%{$params['keyword']}%"];
        }
        $list = \app\adminapi\model\Type::where($where)->select();
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
        //添加模型
        $params = input();
        $validate = $this->validate($params,[
            'type_name|模型名称' => 'require',
            'specs|规格数组' => 'require|array',
            'attrs|属性数组' => 'require|array',
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        \think\Db::startTrans();
        try {
            $type = \app\adminapi\model\Type::create(['type_name'=>$params['type_name']],true);
            foreach ($params['specs'] as $i=>$v){
                if (trim($v['spec_name']) == ''){
                    unset($params['specs'][$i]);
                    continue;
                }else{
                    foreach ($v['spec_values'] as $k=>$value){
                        if (trim($value['spec_value']) == ''){
                            unset($params['specs'][$i]['spec_values'][$k]);
                        }
                    }
                    if (empty($params['specs'][$i]['spec_values'])){
                        unset($params['specs'][$i]);
                    }
                }
            }
            if (empty($params['specs'])){
                $this->fail('添加的规格值不符合要求');
            }
            $specs = [];
            foreach ($params['specs'] as $i=>$spec){
                $row = [
                    'spec_name' => $spec['spec_name'],
                    'sort' => $spec['sort'],
                    'type_id' => $type['id']
                ];
                $specs[] = $row;
            }
            $spec_model =new \app\adminapi\model\Spec();
            $spec_data = $spec_model->allowField(true)->saveAll($specs);
            $spec_values = [];
            foreach ($params['specs'] as $i=>$v){
                foreach ($v['spec_values'] as $value){
                    $row = [
                        'spec_id' => $spec_data[$i]['id'],
                        'spec_value' => $value['spec_value'],
                        'type_id' => $type['id']
                    ];
                    $spec_values[] = $row;
                }
            }
            $spec_value_model = new \app\adminapi\model\SpecValue();
            $spec_value_model->allowField(true)->saveAll($spec_values);
            foreach ($params['attrs'] as $i=>$attr){
                if (trim($attr['attr_name']) == ''){
                    unset($params['attrs'][$i]);
                    continue;
                }else{
                    foreach ($attr['attr_values'] as $k=>$value){
                        if (trim($value) == ''){
                            unset($params['attrs'][$i]['attr_values'][$k]);
                        }
                    }
                }
            }
            if (empty($params['attrs'])){
                $this->fail('添加的属性值不符合要求');
            }
            $attrs = [];
            foreach ($params['attrs'] as $attr){
                $row = [
                    'type_id' => $type['id'],
                    'attr_name' => $attr['attr_name'],
                    'attr_values' => implode(',',$attr['attr_values']),
                    'sort' => $attr['sort']
                ];
                $attrs[] = $row;
            }
            $attr_model = new \app\adminapi\model\Attribute();
            $attr_model->allowField(true)->saveAll($attrs);
            \think\Db::commit();
            $type = \app\adminapi\model\Type::find($type['id']);
            $this->ok($type);
        }catch (\Exception $e){
            $this->fail($e->getMessage());
            \think\Db::rollback();
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
        //模型详情
        $list = \app\adminapi\model\Type::with('specs,specs.spec_values,attrs')->find($id);
        $this->ok($list);
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
        //修改模型
        $params = input();
        $validate = $this->validate($params,[
            'type_name|模型名称' => 'require',
            'specs|规格数组' => 'require|array',
            'attrs|属性数组' => 'require|array',
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        \think\Db::startTrans();
        try {
            \app\adminapi\model\Type::update(['type_name'=>$params['type_name']],['id'=>$id],true);
            foreach ($params['specs'] as $i=>$v){
                if (trim($v['spec_name']) == ''){
                    unset($params['specs'][$i]);
                    continue;
                }else{
                    foreach ($v['spec_values'] as $k=>$value){
                        if (trim($value['spec_value']) == ''){
                            unset($params['specs'][$i]['spec_values'][$k]);
                        }
                    }
                    if (empty($params['specs'][$i]['spec_values'])){
                        unset($params['specs'][$i]);
                    }
                }
            }
            if (empty($params['specs'])){
                $this->fail('添加的规格值不符合要求');
            }
            \app\adminapi\model\Spec::destroy(['type_id'=>$id]);
            $specs = [];
            foreach ($params['specs'] as $i=>$spec){
                $row = [
                    'spec_name' => $spec['spec_name'],
                    'sort' => $spec['sort'],
                    'type_id' => $id
                ];
                $specs[] = $row;
            }
            $spec_model =new \app\adminapi\model\Spec();
            $spec_data = $spec_model->allowField(true)->saveAll($specs);
            \app\adminapi\model\SpecValue::destroy(['type_id'=>$id]);
            $spec_values = [];
            foreach ($params['specs'] as $i=>$v){
                foreach ($v['spec_values'] as $value){
                    $row = [
                        'spec_id' => $spec_data[$i]['id'],
                        'spec_value' => $value['spec_value'],
                        'type_id' => $id
                    ];
                    $spec_values[] = $row;
                }
            }
            $spec_value_model = new \app\adminapi\model\SpecValue();
            $spec_value_model->allowField(true)->saveAll($spec_values);
            foreach ($params['attrs'] as $i=>$attr){
                if (trim($attr['attr_name']) == ''){
                    unset($params['attrs'][$i]);
                    continue;
                }else{
                    foreach ($attr['attr_values'] as $k=>$value){
                        if (trim($value) == ''){
                            unset($params['attrs'][$i]['attr_values'][$k]);
                        }
                    }
                }
            }
            if (empty($params['attrs'])){
                $this->fail('添加的属性值不符合要求');
            }
            \app\adminapi\model\Attribute::destroy(['type_id'=>$id]);
            $attrs = [];
            foreach ($params['attrs'] as $attr){
                $row = [
                    'type_id' => $id,
                    'attr_name' => $attr['attr_name'],
                    'attr_values' => implode(',',$attr['attr_values']),
                    'sort' => $attr['sort']
                ];
                $attrs[] = $row;
            }
            $attr_model = new \app\adminapi\model\Attribute();
            $attr_model->allowField(true)->saveAll($attrs);
            \think\Db::commit();
            $type = \app\adminapi\model\Type::find($id);
            $this->ok($type);
        }catch (\Exception $e){
            $this->fail($e->getMessage());
            \think\Db::rollback();
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
        //删除模型
        $goods = \app\adminapi\model\Goods::where('type_id',$id)->find();
        if ($goods){
            $this->fail('有商品在使用该模型，删除失败');
        }
        \think\Db::startTrans();
        try {
            \app\adminapi\model\Type::destroy($id);
            \app\adminapi\model\Spec::destroy(['type_id',$id]);
            \app\adminapi\model\SpecValue::destroy(['type_id',$id]);
            \app\adminapi\model\Attribute::destroy(['type_id',$id]);
            \think\Db::commit();
            $this->ok();
        }catch (\Exception $e){
            \think\Db::rollback();
            $this->fail('操作失败');
        }
    }
    public function getSpec()
    {
        $type_id = input('type_id');
        if (empty($type_id)){
            $this->fail('还未获取模型');
        }
        $spec = \app\adminapi\model\Spec::where('type_id',$type_id)->select();
        if (empty($spec)){
            $this->fail('该模型下暂无规格');
        }
        $this->ok($spec);
    }
    public function getSpecvalue()
    {
        $spec_id = input('spec_id');
        if (empty($spec_id)){
            $this->fail('还未获取规格');
        }
        $spec_value = \app\adminapi\model\SpecValue::where('spec_id',$spec_id)->select();
        if (empty($spec_value)){
            $this->fail('该规格下暂无规格值');
        }
        $this->ok($spec_value);
    }
    public function getAttr()
    {
        $type_id = input('type_id');
        if (empty($type_id)){
            $this->fail('还未获取模型');
        }
        $attr = \app\adminapi\model\Attribute::where('type_id',$type_id)->select();
        if (empty($attr)){
            $this->fail('该模型下暂无属性');
        }
        $this->ok($attr);
    }
}

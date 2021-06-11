<?php

namespace app\adminapi\model;

use think\Model;

class Type extends Model
{
    //定义与Spec的关联 一个模型有多个规格
    public function specs()
    {
        return $this->hasMany('Spec','type_id');
    }
    //定义与Attribute的关联 一个模型有多个属性
    public function attrs()
    {
        return $this->hasMany('Attribute','type_id');
    }
}

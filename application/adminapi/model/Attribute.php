<?php

namespace app\adminapi\model;

use think\Model;

class Attribute extends Model
{
    //定义与模型的关联 一个属性属于一个模型
    public function type()
    {
        return $this->belongsTo('Type','type_id','id')->bind('type_name');
    }
}

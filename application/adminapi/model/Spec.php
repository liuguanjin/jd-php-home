<?php

namespace app\adminapi\model;

use think\Model;

class Spec extends Model
{
    //定义与SpecValue的关联 一个规格有多个规格值
    public function specValues()
    {
        return $this->hasMany('SpecValue','spec_id');
    }
    public function type()
    {
        return $this->belongsTo('Type','type_id','id')->bind('type_name');
    }
}

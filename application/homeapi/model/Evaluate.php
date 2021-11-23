<?php

namespace app\homeapi\model;

use think\Model;

class Evaluate extends Model
{
    public function evaluateImages()
    {
        return $this->hasMany('EvaluateImages','evaluate_id','id');
    }
}

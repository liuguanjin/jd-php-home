<?php

namespace app\adminapi\model;

use think\Model;

class Shop extends Model
{
    //
    public function goods()
    {
        return $this->hasMany('Goods','shop_id','id');
    }
    public function admin()
    {
        return $this->belongsTo('Admin','admin_id','id')->bind(['admin_name'=>'username']);
    }
}

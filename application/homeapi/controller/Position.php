<?php

namespace app\homeapi\controller;

use think\Controller;

class Position extends BaseApi
{
    public function province()
    {
        $data = \app\homeapi\model\PositionProvince::select();
        if (empty($data)){
            $this->fail('获取省份失败');
        }
        $this->ok($data);
    }
    public function city()
    {
        $province_id = input('province_id');
        $city = \app\homeapi\model\PositionCity::where('province_id',$province_id)->select();
        if (empty($city)){
            $this->fail('获取城市失败');
        }
        $this->ok($city);
    }
    public function county()
    {
        $city_id = input('city_id');
        $county = \app\homeapi\model\PositionCounty::where('city_id',$city_id)->select();
        if (empty($county)){
            $this->fail('获取县区失败');
        }
        $this->ok($county);
    }
    public function town()
    {
        $county_id = input('county_id');
        $town = \app\homeapi\model\PositionTown::where('county_id',$county_id)->select();
        if (empty($town)){
            $this->fail('获取街道失败');
        }
        $this->ok($town);
    }
    public function village()
    {
        $town_id = input('town_id');
        $village_id = input('village_id');
        if (!empty($town_id)){
            $data = \app\homeapi\model\PositionVillage::where('town_id',$town_id)->select();
            if (empty($data)){
                $this->fail('获取社区失败');
            }
        }
        if (!empty($village_id)){
            $data = \app\homeapi\model\PositionVillage::where('village_id',$village_id)->select();
            if (empty($data)){
                $this->fail('获取社区失败');
            }
        }
        $this->ok($data);
    }
}

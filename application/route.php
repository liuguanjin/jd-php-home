<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use \think\Route;
//前台接口
//获取商品 分页接口
Route::get('goods','homeapi/goods/index');
//商品详情接口
Route::get('goodsdetail/:id','homeapi/goods/detail');
//获取推荐商品接口
Route::get('recommend-goods/:id','homeapi/goods/recommend');
//店铺详情接口
Route::get('shopdetail/:id','homeapi/shop/detail');
//获取购物车数据接口
Route::get('cart/:id','homeapi/cart/read');
//获取收藏夹数据接口
Route::get('collect/:id','homeapi/collect/read');
//获取足迹数据接口
Route::get('footprint/:id','homeapi/footprint/read');
//购物车详情接口 获取购物车中的商品信息
Route::post('cart','homeapi/cart/index');
//收藏夹详情接口 获取收藏夹中的商品信息
Route::post('collect','homeapi/collect/index');
//足迹详情接口 获取足迹中的商品信息
Route::post('footprint','homeapi/footprint/index');
//更改购物车接口
Route::put('cart/:id','homeapi/cart/update');
//更改收藏夹接口
Route::put('collect/:id','homeapi/collect/update');
//更改足迹接口
Route::put('footprint/:id','homeapi/footprint/update');
//获取分类接口
Route::get('category','homeapi/category/read');
//获取省份接口
Route::get('province','homeapi/position/province');
//获取城市接口
Route::get('city','homeapi/position/city');
//获取县区接口
Route::get('county','homeapi/position/county');
//获取城镇接口
Route::get('town','homeapi/position/town');
//获取社区接口
Route::get('village','homeapi/position/village');
//收货地址增删改查接口
Route::resource('address','homeapi/address',[],['id' => '\d+']);
//结算界面中的商品数据
Route::post('balancegoods','homeapi/goods/balanceGoods');
//订单增删改查接口
Route::resource('order','homeapi/order',[],['id' => '\d+']);
//获取分类性情接口
Route::get('category-detail/:id','homeapi/category/categoryDetail');
//获取收藏商铺接口 商铺的id集合
Route::get('collect-shop','homeapi/CollectShop/index');
//获取收藏商铺数据接口 详细商品信息
Route::post('collect-shop-detail','homeapi/CollectShop/collectShopDetail');
//更新收藏商铺数据接口
Route::put('collect-shop/:id','homeapi/CollectShop/update');
//获取评论的商品详情接口
Route::get('evaluate-goods/:id','homeapi/Order/orderGoods');
//前台的多图片上传接口
Route::post('images','homeapi/upload/images');
//前台的单图片上传接口
Route::post('logo','homeapi/upload/logo');
//评论接口
Route::post('evaluate/:id','homeapi/evaluate/save');
//支付宝同步接口 一般用来做支付成功界面跳转
Route::get('order/callback','homeapi/order/callback');
//支付宝异步接口 本地测试不生效
Route::get('order/notify','homeapi/order/notify');
//收货接口
Route::get('accept-goods/:id','homeapi/order/acceptGoods');
//用户提醒发货接口
Route::get('remind-goods/:id','homeapi/order/remindGoods');
//用户信息接口
Route::get('user/:id','homeapi/user/userDetail');
//获取用户昵称接口
Route::get('nickname/:id','homeapi/nickname/index');
//用户昵称检测接口
Route::post('nickname','homeapi/nickname/checkNickname');
//用户昵称修改接口
Route::put('nickname/:id','homeapi/nickname/save');
//用户性别修改接口
Route::put('sex/:id','homeapi/sex/save');
//获取用户个性签名接口
Route::get('sign/:id','homeapi/sign/index');
//修改用户个性签名接口
Route::put('sign/:id','homeapi/sign/save');
//用户上传头像接口
Route::post('avatar/:id','homeapi/user/avatar');
//用户搜索商品历史接口
Route::get('search-history/:id','homeapi/SearchGoods/history');
//用户搜索商品频词接口
Route::get('search-often','homeapi/SearchGoods/often');
//用户删除搜索商品历史接口
Route::delete('delete-search-history/:id','homeapi/SearchGoods/delete');
//用户搜索商品推荐接口
Route::get('search-recommend','homeapi/SearchGoods/recommend');
//用户搜索商品结果接口
Route::get('search-result','homeapi/SearchGoods/searchResult');
//用户添加搜索历史接口
Route::post('search-save','homeapi/SearchGoods/save');
//用户获取某商品下的评论
Route::get('goods-evaluate/:id','homeapi/Evaluate/goodsEvaluate');

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

use think\Route;

//后台接口域名路由 adminapi
//这样设置之后，只有域名为"adminapi"(如"http://adminapi.pyg.com")打头的网址，才能进入到我们定义好的后台接口路由，
//域名为"www"打头的网址就不能进入。
Route::domain('adminapi', function () {
    //adminapi模块首页路由
    Route::get('index', 'adminapi/Index/index');
    //获取验证码接口
    Route::get('captcha/:id', "\\think\\captcha\\CaptchaController@index");//访问图片需要
    Route::get('captcha', 'adminapi/login/captcha');
    // 登录接口
    Route::post('login', 'adminapi/login/login');
    // 退出接口
    Route::get('logout', 'adminapi/login/logout');
    //权限接口
    Route::resource('auths', 'adminapi/auth', [], ['id' => '\d+']);
    //查询菜单权限的接口
    Route::get('nav', 'adminapi/auth/nav');
    //角色接口
    Route::resource('roles', 'adminapi/role', [], ['id' => '\d+']);
    //管理员接口
    Route::resource('admins', 'adminapi/admin', [], ['id' => '\d+']);
    //商品分类接口
    Route::resource('categorys', 'adminapi/category', [], ['id' => '\d+']);
    //单图片上传接口
    Route::post('logo', 'adminapi/upload/logo');
    //多图片上传接口
    Route::post('images', 'adminapi/upload/images');
    //商品品牌接口
    Route::resource('brands', 'adminapi/brand', [], ['id' => '\d+']);
    //商品模型（类型）接口
    Route::resource('types', 'adminapi/type', [], ['id' => '\d+']);
    //商品接口
    Route::resource('goods', 'adminapi/goods', [], ['id' => '\d+']);
    //删除相册照片接口
    Route::delete('delpics/:id', 'adminapi/goods/delpics', [], ['id' => '\d+']);

});

//测试资源路由
Route::resource('blog', 'adminapi/blog', [], ['id' => '\d+']);

// 使用[GET、POST、PUT、DELETE]，三级目录要写全："模块名/控制器名/方法名"
// http://www.pyg.com/admin
//Route::get('admin', 'admin/Admin/index', [], ['id' => '\d+']);

//路由分组
/*Route::group('admin', function () {
    // http://www.pyg.com/admin/admin 就固定访问到'admin/Admin/index'控制器
    Route::get('admin', 'admin/Admin/index', [], ['id' => '\d+']);
});*/


// 如果使用资源路由，html中默认是get提交，一是要注意请求类型要匹配（GET、POST、PUT、DELETE），
//二是要注意路由名称，要严格按照生成路由规则书写
//Route::resource('admin', 'admin/Admin', [], ['id' => '\d+']);







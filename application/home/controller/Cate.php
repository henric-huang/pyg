<?php

namespace app\home\controller;

use app\common\model\Auth;
use app\common\model\Category;
use think\Collection;
use think\Controller;

class Cate extends Base
{
    //
    public function index()
    {
        $data = Category::select();
//        $data = \app\common\model\Cate::select();

        $data = (new Collection($data))->toArray();
        //无限级分类
//        $data = get_cate_list($data);
        //父子级分类
        $data = get_tree_list($data);
        dump($data);


    }
}

<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Image;
use think\Request;

class Brand extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //接收参数  cate_id ;  keyword  page
        $params  = input();
        $keyword = '';
        if (isset($params['cate_id']) && !empty($params['cate_id'])) {
            //分类下的品牌列表
            $cate_id = $params['cate_id'];
            //查询数据
            $info = \app\common\model\Brand::where('cate_id', $cate_id)->field('id', 'name')->select();
        } else {
            //分页+搜索列表
            if (isset($params['keyword']) && !empty($params['keyword'])) {
                $keyword = $params['keyword'];
            }
            $page = isset($params['page']) ?: 10;
//            $info = \app\admin\model\Brand::with('category')->where('pyg_brand.name', 'like', "%$keyword%")->paginate($page);
            $info = \app\common\model\Brand::alias('t1')
                ->join('pyg_category t2', 't1.cate_id = t2.id', 'left')
                ->field('t1.*,t2.cate_name')
                ->where('t1.name', 'like', "%$keyword%")
                ->paginate($page);
        }
        //返回数据
        $this->ok($info);
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
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //接收数据
        $params = input();
        //参数检测
        $validate = $this->validate($params, [
            'name'    => 'require',
            'cate_id' => 'require|integer|gt:0',
            'is_hot'  => 'require|in:0,1',
            'sort'    => 'require|between:0,99999',
        ]);
        if ($validate != true) {
            $this->fail($validate, 401);
        }
        //生成缩略图  /uploads/brand/20190716/1232.jpg
        if (isset($params['logo']) && !empty($params['logo']) && is_file('.' . $params['logo'])) {
            Image::open('.' . $params['logo'])->thumb(200, 100)->save('.' . $params['logo']);
        }
        //添加数据
        $info = \app\common\model\Brand::create($params, true);
        $data = \app\common\model\Brand::find($info['id']);
        //返回数据
        $this->ok($data);
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        //查询数据
//        $info = \app\common\model\Brand::find($id);
        $info = \app\common\model\Brand::with('category')->find($id);
        //如果查询分类名称
        /*$info = \app\common\model\Brand::alias('t1')
            ->join('pyg_category t2', 't1.cate_id=t2.id', 'left')
            ->field('t1.*, t2.cate_name')
            ->where('t1.id', $id)
            ->find();*/

        //返回数据
        $this->ok($info);
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param \think\Request $request
     * @param int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //接收参数
        $params = input();
        //参数检测
        $validate = $this->validate($params, [
            'name'    => 'require',
            'cate_id' => 'require|integer|gt:0',
            'is_hot'  => 'require|in:0,1',
            'sort'    => 'require|between:0,9999'
        ]);
        if ($validate !== true) {
            $this->fail($validate);
        }
        //修改数据（logo图片 缩略图）
        if (isset($params['logo']) && !empty($params['logo']) && is_file('.' . $params['logo'])) {
            //生成缩略图
            //$params['logo']
            \think\Image::open('.' . $params['logo'])->thumb(200, 100)->save('.' . $params['logo']);
        }
        \app\common\model\Brand::update($params, ['id' => $id], true);
        $info = \app\common\model\Brand::find($id);
        //返回数据
        $this->ok($info);
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //判断 品牌下是否有商品
        $total = \app\common\model\Goods::where('brand_id', $id)->count();
        if ($total > 0) {
            $this->fail('品牌下有商品，不能删除');
        }
        //删除数据
        $info = \app\common\model\Brand::destroy($id);
        //返回数据
        $this->ok();
    }
}

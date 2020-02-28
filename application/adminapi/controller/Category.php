<?php

namespace app\adminapi\controller;

use think\Collection;
use think\Controller;
use think\Image;
use think\Request;

class Category extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //接收pid参数  影响查询的数据
        //$pid = input('pid', '');
        //if($pid === ''){}
        $params = input();
//        $where  = [];
//        if (isset($params['pid'])) {
//            $where['pid'] = $params['pid'];
//        }
        //使用三目运算符优化判断
        $where = isset($params['pid']) ?: '';
        //接收type参数  影响返回的数据
        //查询数据
        $list = \app\common\model\Category::where($where)->select();
        //转化为标准二维数组结构
        $list = (new Collection($list))->toArray();
        /*if(isset($params['type']) && $params['type'] == 'list'){

        }else{
            $list = get_cate_list($list);
        }*/

        if (!isset($params['type']) || $params['type'] != 'list') {
            //转化为无限级分类列表
            $list = get_cate_list($list);
        }
        //返回数据
        $this->ok($list);


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
        //接收参数
        $params = input();
        //参数检测
        $validate = $this->validate($params, [
            'cate_name|分类名' => 'require|length:2,20',
            'pid|父级id'      => 'require|integer|egt:0',
            'is_show|是否显示'  => 'require|in:0,1',
            'is_hot|是否热门'   => 'require|in:0,1',
            'sort|排序'       => 'require|between:0,9999',
        ]);
        if ($validate != true) {
            $this->fail($validate, 401);
        }
        //添加数据(处理pid_path  pid_path_name  level)
        if ($params['pid'] == 0) {
            $params['level']         = 0;
            $params['pid_path']      = 0;
            $params['pid_path_name'] = '';
        } else {
            //不是顶级分类，查询其上级分类
            $p_info = \app\common\model\Category::where('id', $params['pid'])->find();
            if (empty($p_info)) {
                //没查到父级
                $this->fail('数据异常,请稍后再试');
            }
            $params['level']         = $p_info['level'] + 1;
            $params['pid_path']      = $p_info['pid_path'] . '_' . $p_info['id'];
            $params['pid_path_name'] = $p_info['pid_path_name'] . '/' . $p_info['cate_name'];
        }
        //logo图片处理
        /*if (isset($params['logo']) && empty($params['logo'])) {
            $params['image_url'] = $params['logo'];
        }*/
        $params['image_url'] = isset($params['logo']) ?: '';
        if (isset($params['image_url']) && !empty($params['image_url']) && is_file('.' . $params['image_url'])) {
            Image::open('.' . $params['image_url'])->thumb(200, 100)->save('.' . $params['image_url']);
        }
        //添加数据
        $info = \app\common\model\Category::create($params, true);
        $data = \app\common\model\Category::find($info['id']);
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
        //
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
            'cate_name|分类名' => 'require|length:2,20',
            'pid|父级id'      => 'require|integer|egt:0',
            'is_show|是否显示'  => 'require|in:0,1',
            'is_hot|是否热门'   => 'require|in:0,1',
            'sort|排序'       => 'require|between:0,9999',
        ]);
        if ($validate != true) {
            $this->fail($validate, 401);
        }
        //修改数据(处理pid_path  pid_path_name  level)
        if ($params['pid'] == 0) {
            $params['level']         = 0;
            $params['pid_path']      = 0;
            $params['pid_path_name'] = '';
        } else {
            //不是顶级分类，查询其上级分类
            $p_info = \app\common\model\Category::where('id', $params['pid'])->find();
            if (empty($p_info)) {
                //没查到父级
                $this->fail('数据异常,请稍后再试');
            }
            $params['level']         = $p_info['level'] + 1;
            $params['pid_path']      = $p_info['pid_path'] . '_' . $p_info['id'];
            $params['pid_path_name'] = $p_info['pid_path_name'] . '/' . $p_info['cate_name'];
        }
        //logo图片处理
        if (isset($params['logo']) && empty($params['logo'])) {
            $params['image_url'] = $params['logo'];
        }

        //修改数据
        \app\common\model\Category::update($params, ['id', $id], true);
        $data = \app\common\model\Category::find($id);
        //返回数据
        $this->ok($data);
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //删除数据
        //判断分类下是否有子分类
        $total = \app\common\model\Category::where('pid', $id)->count();
        if ($total > 0) {
            $this->fail('分类下有子分类,无法删除', 401);
        } else {
            \app\common\model\Category::destroy($id);
        }
        //返回数据
        $this->ok();
    }
}

<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Request;

class Type extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //查询商品类型数据
        $where   = [];
        $keyword = input('keyword', '');
        if (!empty($keyword)) {
            $where['type_name'] = ['like', "%$keyword%"];
        }
        $list = \app\admin\model\Type::select();
//        return json($list);
        return view('product-type', ['list' => $list]);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        return view('product-type-add');
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //接收输入参数
        $params = $request->param();
//        dump($params);die;
        //参数检测
        /*if (empty($params['type_name'])) {
            $this->error('类型名称不能为空');
        }*/
        $validate = $this->validate($params, [
            'type_name|模型名称' => 'require|max:20',
            'spec|规格'        => 'require|array',
            'attr|属性'        => 'require|array',
        ]);
        if ($validate !== true) {
            $this->error($validate);
        }
        \think\Db::startTrans();
        try {
            //参数数组参考：
            /*$params = [
                'type_name' => '手机',
                'spec'      => [
                    ['name' => '颜色', 'sort' => 50, 'value' => ['黑色', '白色', '金色']],
                    ['name' => '内存', 'sort' => 50, 'value' => ['64G', '128G', '256G']],
                ],
                'attr'      => [
                    ['name' => '毛重', 'sort' => 50, 'value' => []],
                    ['name' => '产地', 'sort' => 50, 'value' => ['进口', '国产']],
                ]
            ]*/
            //将数据添加到商品类型表
            $type = \app\admin\model\Type::create($params, true);
            if (isset($params['spec']) && !empty($params['spec'])) {
                foreach ($params['spec'] as $k => $v) {
                    foreach ($v['value'] as $k1 => $value) {
//                        if (empty($value)) {
                        if (trim($value) == '') {
                            unset($v['value'][$k1]);
                        }
                    }
//                    if (empty($v['value'])) {
                    if (trim($v['value']) == '') {
                        unset($params['spec'][$k]);
                    }
                }
                //添加规格名称
                $spec_data = [];
                foreach ($params['spec'] as $k => $v) {
                    $spec_data[] = [
                        'type_id'   => $type->id,
                        'spec_name' => $v['name'],
                        'sort'      => $v['sort']
                    ];
                }
                $spec_model = new \app\admin\model\Spec();
                $spec       = $spec_model->saveAll($spec_data);
                //添加规格值
                $spec_ids    = array_column($spec, 'id');
                $spec_values = [];
                foreach ($params['spec'] as $k => $v) {
                    foreach ($v['value'] as $value) {
                        $spec_values[] = [
                            'spec_id'    => $spec_ids[$k],
                            'spec_value' => $value,
                            'type_id'    => $type->id
                        ];
                    }
                }
                $spec_value_model = new \app\admin\model\SpecValue();
                $spec_value_model->saveAll($spec_values);
            }
            if (isset($params['attr']) && !empty($params['attr'])) {
                //添加属性
                $attr_data = [];
                foreach ($params['attr'] as $k => $v) {
                    if (!empty($v['value'])) {
                        $attr_data[] = [
                            'type_id'     => $type->id,
                            'attr_name'   => $v['name'],
                            'sort'        => $v['sort'],
                            'attr_values' => implode(',', $v['value']),
                        ];
                    }
                }
                $attr_model = new \app\admin\model\Attribute();
                $attr_model->saveAll($attr_data);
            }
            \think\Db::commit();
        } catch (\Exception $e) {
            \think\Db::rollback();
            $msg = $e->getMessage();
            return json(['code' => 200, 'msg' => $msg]);
        }
        return json(['code' => 200, 'msg' => '操作成功']);
        /*$info = \app\admin\model\Type::with('specs,specs.spec_values,attrs')->find($type['id']);
        $data = [
            'code' => 200,
            'msg'  => '成功',
            'data' => $info,
        ];
        return json($data);*/
    }

//    public function save(Request $request)
//    {
//        //接收数据
//        $params = input();
//        //参数检测
//        $validate = $this->validate($params, [
//            'type_name|模型名称' => 'require|max:20',
//            'spec|规格'        => 'require|array',
//            'attr|属性'        => 'require|array',
//        ]);
//        if ($validate !== true) {
//            $this->error($validate);
//        }
//        //开启事务
//        Db::startTrans();
//        try {
//            //4+2  添加类型、批量添加规格名、批量添加规格值、批量添加属性； 去除空的规格，去除空的属性
//            //添加商品类型 $type['id']  后续要使用
//            $type = \app\admin\model\Type::create($params, true);
//            //$type = \app\admin\model\Type::create(['type_name'=>$params['type_name']]);
//            //添加商品规格名
//            //去除空的规格值  去除没有值的规格名
//            //参数数组参考：
//            /*$params = [
//                'type_name' => '手机',
//                'spec' => [
//                    ['name' => '颜色', 'sort' => 50, 'value'=>['黑色', '白色', '金色']],
//                    //['name' => '颜色1', 'sort' => 50, 'value'=>['', '']],
//                    ['name' => '内存', 'sort' => 50, 'value'=>['64G', '128G', '256G']],
//                ],
//                'attr' => [
//                    ['name' => '毛重', 'sort'=>50, 'value' => []],
//                    ['name' => '产地', 'sort'=>50, 'value' => ['进口', '国产','']],
//                ]
//            ]*/
//            //外层遍历规格名
//            foreach ($params['spec'] as $i => &$spec) {
//                //判断规格名是否为空
//                if (trim($spec['name']) == '') {
//                    unset($params['spec'][$i]);
//                } else {
//                    //内存遍历规格值
//                    foreach ($spec['value'] as $k => &$value) {
//                        // $value就是一个规格值，去除空的值
//                        if (trim($value) == '') {
//                            //unset($spec['value'][$k]);
//                            unset($params['spec'][$i]['value'][$k]);
//                        }
//                    }
//                    //内层foreach结束，判断当前的规格名的规则值是不是空数组
//                    if (empty($params['spec'][$i]['value'])) {
//                        unset($params['spec'][$i]);
//                    }
//                }
//            }
//            unset($spec);
//            //遍历组装 将要新增到添加进Spec数据表的数据一条条组装起来
//            $specs = [];
//            foreach ($params['spec'] as $spec) {
//                $specs [] = [
//                    'type_id'   => $type['id'],
//                    'spec_name' => $spec['name'],
//                    'sort'      => $spec['sort'],
//                ];
//            }
//            //批量添加 规格名称
////            \app\admin\model\Spec::create($data, true);
//            $spec_model = new \app\admin\model\Spec();
//            //saveAll 如果要过滤非数据表字段，需要调用allowField方法
//            $spec_data = $spec_model->allowField(true)->saveAll($specs);
//            /*$spec_data = [
//                ['id' => 10, 'spec_name' => '颜色', 'sort' => 50], //实际上是模型对象
//                ['id' => 20, 'spec_name' => '内存', 'sort' => 50],
//            ];*/
//            //$spec_ids = [10, 20]; //扩展代码
//            //$spec_ids = array_column($spec_data, 'id');//扩展代码
//            //添加商品规格值
//            $spec_values = [];
//            /*$spec_values = [
//                ['spec_id' => 10, 'spec_value' => '黑色', 'type_id' => 30],
//                ['spec_id' => 10, 'spec_value' => '白色', 'type_id' => 30],
//                ['spec_id' => 10, 'spec_value' => '金色', 'type_id' => 30],
//                ['spec_id' => 20, 'spec_value' => '32G', 'type_id' => 30],
//                ['spec_id' => 20, 'spec_value' => '64G', 'type_id' => 30],
//                ['spec_id' => 20, 'spec_value' => '128G', 'type_id' => 30],
//            ];*/
//            //外层遍历规格名称
//            foreach ($params['spec'] as $i => $spec) {
//                //$i  0 1 2  $spec 接收到的规格名称数组  $spec['value']数组 ['黑色','白色']
//                //内层遍历规格值
//                foreach ($spec['value'] as $value) {
//                    $spec_values [] = [
//                        //'spec_id' => $spec_ids[$i],   //扩展代码$params['spec'] 和 $spec_ids 下标对应
//                        'spec_id'    => $spec_data[$i]['id'],   //$params['spec'] 和 $spec_data 下标对应
//                        'spec_value' => $value,
//                        'type_id'    => $type['id'],
//                    ];
//                }
//            }
//            //批量添加规格值
////            \app\admin\model\SpecValue::create($spec_values, true);
//            $spec_value_model = new \app\admin\model\SpecValue();
//            $spec_value_model->allowField(true)->saveAll($spec_values);
//            //添加商品属性
//            //去除空的属性名和空的属性值
//            //外层遍历属性名
//            foreach ($params['attr'] as $i => &$attr) {
//                if (trim($attr['name']) == '') {
//                    unset($params['attr'][$i]);
//                    //continue;
//                } else {
//                    //内层遍历属性值
//                    foreach ($attr['value'] as $k => $value) {
//                        if (trim($value) == '') {
//                            //unset($attr['value'][$k]); //对应$attr加引用&的情况
//                            unset($params['attr'][$i]['value'][$k]);
//                        }
//                    }
//                    /*//内层foreach结束，判断当前的规格名的规则值是不是空数组
//                    if (empty($params['attr'][$i]['value'])) {
//                        unset($params['attr'][$i]);
//                    }*/
//                }
//            }
//            unset($attr);
//            //批量添加属性名称属性值
//            $attrs = [];
//            foreach ($params['attr'] as $attr) {
//                $attrs [] = [
//                    'attr_name'  => $attr['name'],
//                    // 将$attr['value']这个数组里的值，以逗号","隔开，组成一个字符串（进口,国产,其他）
//                    'attr_values' => implode(',', $attr['value']),
//                    'sort'       => $attr['sort'],
//                    'type_id'    => $type['id'],
//                ];
//            }
//            //批量添加
//            $attr_model = new \app\admin\model\Attribute();
//            $attr_model->allowField(true)->saveAll($attrs);
//            //提交事务
//            \think\Db::commit();
//            return json(['code' => 200, 'msg' => '操作成功']);
//        } catch (Exception $e) {
//            //回滚事务
//            \think\Db::rollback();
//            //$msg = $e->getMessage();
//            //$this->fail($msg);
//            //返回数据
//            $this->error('添加失败');
//        }
//
//    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
        $info = \app\admin\model\Type::with('specs,specs.spec_values,attrs')->find($id);
        $data = [
            'code' => 200,
            'msg'  => '成功',
            'data' => $info,
        ];
        return json($data);
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $info = \app\admin\model\Type::with('specs,specs.spec_values,attrs')->find($id);
        $this->assign('info', $info);
        return view('product-type-edit');
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
        //接收数据
        $params = input();
//        dump($params);die();
        //参数检测
        $validate = $this->validate($params, [
            'type_name|模型名称' => 'require|max:20',
            'spec|规格'        => 'require|array',
            'attr|属性'        => 'require|array',
        ]);
        if ($validate !== true) {
            $this->error($validate);
        }
        //开启事务
        Db::startTrans();
        try {
            //修改模（类）型名称
            $type = \app\admin\model\Type::update(['type_name' => $params['type_name']], ['id' => $id], true);
            //\app\admin\model\Type::where('id', $id)->update(['type_name'=>$params['type_name']]);
            //去除空的规格名和规格值
            //参数数组参考：
            /*$params = [
                'type_name' => '手机',
                'spec' => [
                    ['name' => '颜色', 'sort' => 50, 'value'=>['黑色', '白色', '金色']],
                    //['name' => '颜色1', 'sort' => 50, 'value'=>['', '']],
                    ['name' => '内存', 'sort' => 50, 'value'=>['64G', '128G', '256G']],
                ],
                'attr' => [
                    ['name' => '毛重', 'sort'=>50, 'value' => []],
                    ['name' => '产地', 'sort'=>50, 'value' => ['进口', '国产','']],
                ]
            ]*/
            //外层遍历规格名
            foreach ($params['spec'] as $i => $spec) {
                //判断规格名是否为空
                if (trim($spec['name']) == '') {
                    unset($params['spec'][$i]);
                    continue;
                } else {
                    //内存遍历规格值
                    foreach ($spec['value'] as $k => $value) {
                        // $value就是一个规格值，去除空的值
                        if (trim($value) == '') {
                            //unset($spec['value'][$k]);
                            unset($params['spec'][$i]['value'][$k]);
                        }
                        unset($params['spec'][$i]['value']['id']);
                    }
                    //内层foreach结束，判断当前的规格名的规则值是不是空数组
                    if (empty($params['spec'][$i]['value'])) {
                        unset($params['spec'][$i]);
                    }
                }
            }
            //批量删除原来的规格名  删除条件 类型type_id
            \app\admin\model\Spec::destroy(['type_id' => $id]);
            //\app\admin\model\Spec::where('type_id', $id)->delete();
            //批量添加新的规格名
            $specs = [];
            foreach ($params['spec'] as $i => $spec) {
                $row     = [
                    'spec_name' => $spec['name'],
                    'sort'      => $spec['sort'],
                    'type_id'   => $id
                ];
                $specs[] = $row;
            }
            $spec_model = new \app\admin\model\Spec();
            $spec_data  = $spec_model->saveAll($specs);
            /*$spec_data = [
                ['id' => 10, 'spec_name' => '颜色', 'sort' => 50], //实际上是模型对象
                ['id' => 20, 'spec_name' => '内存', 'sort' => 50],
            ];*/
            //批量删除原来的规格值
            \app\admin\model\SpecValue::destroy(['type_id' => $id]);
            //批量添加新的规格值

            $spec_values = [];
            /*$spec_values = [
                ['spec_id' => 10, 'spec_value' => '黑色', 'type_id' => 30],
                ['spec_id' => 10, 'spec_value' => '白色', 'type_id' => 30],
                ['spec_id' => 10, 'spec_value' => '金色', 'type_id' => 30],
                ['spec_id' => 20, 'spec_value' => '32G', 'type_id' => 30],
                ['spec_id' => 20, 'spec_value' => '64G', 'type_id' => 30],
                ['spec_id' => 20, 'spec_value' => '128G', 'type_id' => 30],
            ];*/
            //外层遍历规格名称
            foreach ($params['spec'] as $i => $spec) {
                //$i  0 1 2  $spec 接收到的规格名称数组  $spec['value']数组 ['黑色','白色']
                //内层遍历规格值
                foreach ($spec['value'] as $value) {
                    $spec_values [] = [
                        'spec_id'    => $spec_data[$i]['id'],   //$params['spec'] 和 $spec_data 下标对应
                        'spec_value' => $value,
                        'type_id'    => $id,
                    ];
                }
            }
            //批量添加规格值
            $spec_value_model = new \app\admin\model\SpecValue();
            $spec_value_model->allowField(true)->saveAll($spec_values);
            //去除空的属性名和空的属性值
            //外层遍历属性名
            foreach ($params['attr'] as $i => $attr) {
                if (trim($attr['name']) == '') {
                    unset($params['attr'][$i]);
                    continue;
                } else {
                    //内层遍历属性值
                    foreach ($attr['value'] as $k => $value) {
                        if (trim($value) == '') {
                            //unset($attr['value'][$k]); //对应$attr加引用&的情况
                            unset($params['attr'][$i]['value'][$k]);
                        }
                    }
                    /*//内层foreach结束，判断当前的规格名的规则值是不是空数组
                    if (empty($params['attr'][$i]['value'])) {
                        unset($params['attr'][$i]);
                    }*/
                }
            }
            //批量删除原来的属性
            \app\admin\model\Attribute::destroy(['type_id' => $id]);
            //批量添加新的属性
            $attrs = [];
            foreach ($params['attr'] as $attr) {
                $attrs [] = [
                    'type_id'     => $id,
                    'attr_name'   => $attr['name'],
                    // 将$attr['value']这个数组里的值，以逗号","隔开，组成一个字符串（进口,国产,其他）
                    'attr_values' => implode(',', $attr['value']),
                    'sort'        => $attr['sort'],
                ];
            }
            //批量添加
            $attr_model = new \app\admin\model\Attribute();
            $attr_model->allowField(true)->saveAll($attrs);
            //提交事务
            \think\Db::commit();
            return json(['code' => 200, 'msg' => '操作成功']);
        } catch (Exception $e) {
            //回滚事务
            \think\Db::rollback();
            //$msg = $e->getMessage();
            //$this->fail($msg);
            ////返回数据
            $this->error('添加失败');
        }
    }

    /*public function update(Request $request, $id)
    {
        //接收输入参数
        $params = $request->param();
//        dump($params);die();
        //参数检测
        if (empty($params['type_name'])) {
            $this->error('类型名称不能为空');
        }
        \think\Db::startTrans();
        try {
            //将数据添加到商品类型表
            \app\admin\model\Type::update($params, ['id' => $id], true);
            if (isset($params['spec']) && !empty($params['spec'])) {
                foreach ($params['spec'] as $k => $v) {
                    foreach ($v['value'] as $k1 => $value) {
//                        if (empty($value)) {
                        if (trim($value) == '') {
                            unset($v['value'][$k1]);
                        }
                    }
//                    if (empty($v['value'])) {
                    if (trim($v['value']) == '') {
                        unset($params['spec'][$k]);
                    }
                }
                //修改规格名称
                $spec_data = [];
                foreach ($params['spec'] as $k => $v) {
                    $spec_data[] = [
                        'type_id'   => $id,
                        'spec_name' => $v['name'],
                        'sort'      => $v['sort']
                    ];
                }
                \app\admin\model\Spec::destroy(['type_id' => $id]);
                $spec_model = new \app\admin\model\Spec();
                $spec       = $spec_model->saveAll($spec_data);
                //添加规格值
                $spec_ids    = array_column($spec, 'id');
                $spec_values = [];
                foreach ($params['spec'] as $k => $v) {
                    foreach ($v['value'] as $value) {
                        $spec_values[] = [
                            'type_id'    => $id,
                            'spec_id'    => $spec_ids[$k],
                            'spec_value' => $value
                        ];
                    }
                }
                \app\admin\model\SpecValue::destroy(['type_id' => $id]);
                $spec_value_model = new \app\admin\model\SpecValue();
                $spec_value_model->saveAll($spec_values);
            }
            if (isset($params['attr']) && !empty($params['attr'])) {
//                dump($params['attr']);die;
                //修改属性
                $attr_data = [];
                foreach ($params['attr'] as $k => $v) {
                    if (!empty($v['value'])) {
                        $attr_data[] = [
                            'id'          => isset($v['id']) ? $v['id'] : null,
                            'attr_name'   => $v['name'],
                            'sort'        => $v['sort'],
                            'attr_values' => implode(',', $v['value']),
                            'type_id'     => $id,
                        ];
                    }
                }
//                dump($attr_data);die;
                \app\admin\model\Attribute::destroy(['type_id' => $id]);
                $attr_model = new \app\admin\model\Attribute();
                $attr_model->saveAll($attr_data);
            }
            \think\Db::commit();
        } catch (\Exception $e) {
            \think\Db::rollback();
            $msg = $e->getMessage();
            return json(['code' => 200, 'msg' => $msg]);
        }
        return json(['code' => 200, 'msg' => '操作成功']);
    }*/

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $ids     = explode(',', $id);
        $on_ids  = \app\admin\model\Goods::where('type_id', 'in', $ids)->column('type_id');
        $off_ids = array_diff($ids, $on_ids);
        if (empty($off_ids)) {
            return json(['code' => '500', 'msg' => '使用中，无法删除']);
        }
        \app\admin\model\SpecValue::where('type_id', 'in', $off_ids)->delete();
        \app\admin\model\Spec::where('type_id', 'in', $off_ids)->delete();
        \app\admin\model\Attribute::where('type_id', 'in', $off_ids)->delete();
        \app\admin\model\Type::destroy($off_ids);
        if (empty($on_ids)) {
            return json(['code' => '200', 'msg' => '操作成功']);
        } else {
            return json(['code' => '200', 'msg' => '部分操作成功；部分数据使用中，无法删除', 'data' => $off_ids]);
        }
    }

    public function getSpecAttr($type_id)
    {
        $type = \app\admin\model\Type::with('attrs,specs,specs.specValues')->find($type_id);
//        dump($type->toArray());die;
        $data['attrs'] = $type['attrs'];
        $data['specs'] = $type['specs'];
        return json(['code' => '200', 'msg' => '操作成功', 'data' => $data]);
    }
}

<?php

namespace app\adminapi\controller;

use app\common\model\GoodsImages;
use app\common\model\SpecGoods;
use think\Controller;
use think\Db;
use think\Exception;
use think\Image;
use think\Request;

class Goods extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //接收参数  keyword  page(自动处理)
        $params  = input();
        $keyword = '';
        $page    = !empty($params['page']) ?: 10;
        if (isset($params['keyword']) && !empty($params['keyword'])) {
            $keyword = $params['keyword'];
        }
        //查询数据（分页+搜索）（关联查询）
        $info = \app\common\model\Goods::with('category,brand,type')
            ->where('goods_name', 'like', "%$keyword%")
            ->order('id', 'desc')
            ->paginate($page);
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
        echo 'create';
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
        //对富文本编辑器字段  goods_desc 过滤方式防范xss攻击
        $params['goods_desc'] = input('goods_desc', '', 'remove_xss');
        //参数数组参考：(部分省略)
        /*$params = [
            'goods_name' => 'iphone X',
            'goods_price' => '8900',
            'goods_introduce' => 'iphone iphonex',
            'goods_logo' => '/uploads/goods/20190101/afdngrijskfsfa.jpg',
            'goods_images' => [
                '/uploads/goods/20190101/dfsssadsadsada.jpg',
                '/uploads/goods/20190101/adsafasdadsads.jpg',
                '/uploads/goods/20190101/dsafadsadsaasd.jpg',
            ],
            'cate_id' => '72',
            'brand_id' => '3',
            'type_id' => '16',
            'item' => [
                '18_21' => [
                    'value_ids'=>'18_21',
                    'value_names'=>'颜色：黑色；内存：64G',
                    'price'=>'8900.00',
                    'cost_price'=>'5000.00',
                    'store_count'=>100
                ],
                '18_22' => [
                    'value_ids'=>'18_22',
                    'value_names'=>'颜色：黑色；内存：128G',
                    'price'=>'9000.00',
                    'cost_price'=>'5000.00',
                    'store_count'=>50
                ]
            ],
            'attr' => [
                '7' => ['id'=>'7', 'attr_name'=>'毛重', 'attr_value'=>'150g'],
                '8' => ['id'=>'8', 'attr_name'=>'产地', 'attr_value'=>'国产'],
            ]
        ];*/
        //参数检测
        $validate = $this->validate($params, [
            'goods_name|商品名称'   => 'require',
            'goods_price|商品价格'  => 'require|float|gt:0',
            //省略无数字段检测
            'goods_logo|商品logo' => 'require',
            'goods_images|相册图片' => 'require|array',
            'attr|商品属性值'        => 'require|array',
            'item|规格商品SKU'      => 'require|array'
        ], [
            'goods_price.float' => '商品价格必须是小数或者整数'
        ]);
        if ($validate != true) {
            $this->fail($validate);
        }
        //开启事务
        Db::startTrans();
        try {
            //添加商品基本信息
            //商品logo图片 生成缩略图
            if (is_file('.' . $params['goods_logo'])) {
                //生成缩略图
                // /uploads/goods/20190701/jdsfdslafdsa.jpg
                //\think\Image::open('.' . $params['goods_logo'])->thumb(210, 240)->save('.' . $params['goods_logo']);
                //以下代码，是给缩略图重新取名字
                $goods_logo = dirname($params['goods_logo']) . DS . 'thumb_' . DS . basename($params['goods_logo']);
                Image::open('.' . $params['goods_logo'])->thumb(210, 240)->save('.' . $goods_logo);
                $params['goods_logo'] = $goods_logo;
            }
            //商品属性  生成json字符串
            $params['goods_attr'] = json_encode($params['attr'], JSON_UNESCAPED_UNICODE);
            $goods                = \app\common\model\Goods::create($params, true);
            //批量添加商品相册图片
            $goods_images = [];
            foreach ($params['goods_images'] as $image) {
                if (is_file('.' . $image)) {
                    $pics_big  = dirname($image) . DS . 'thumb_800_' . DS . basename($image);
                    $pics_sma  = dirname($image) . DS . 'thumb_400_' . DS . basename($image);
                    $image_obj = Image::open('.' . $image);
                    $image_obj->thumb(800, 800)->save('.' . $pics_big);
                    $image_obj->thumb(400, 400)->save('.' . $pics_sma);
                    $goods_images [] = [
                        'goods_id' => $goods['id'],
                        'pics_big' => $pics_big,
                        'pics_sma' => $pics_sma,
                    ];
                }
            }
            $goods_images_model = new GoodsImages();
            $goods_images_model->allowField(true)->saveAll($goods_images);
            //批量添加规格商品 SKU
            $spec_goods = [];
            foreach ($params['item'] as $v) {
                $v['goods_id'] = $goods['id'];
                $spec_goods [] = $v;
            }
            $spec_goods_model = new SpecGoods();
            $spec_goods_model->allowField(true)->saveAll($spec_goods);
            //提交事务
            Db::commit();
            //返回数据
            $info = \app\common\model\Goods::with('category,brand,type')->find($goods['id']);
            $this->ok($info);
        } catch (Exception $e) {
            //回滚事务
            Db::rollback();
            $this->fail('操作失败');
        }
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        //查询数据 多个嵌套关联，只有最后一个生效。比如with('type, type.attrs, type.specs') 生效的是type.specs
        $info = \app\common\model\Goods::with('category_row,brand_row,goodsImages,specGoods')->find($id);
        //按照接口要求，改属性名
        $info['category'] = $info['category_row'];
        unset($info['category_row']);
        $info['brand'] = $info['brand_row'];
        unset($info['brand_row']);
        //商品所属模型信息
        $type         = \app\common\model\Type::with('specs,specs.specValues,attrs')->find($info['type_id']);
        $info['type'] = $type;
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
        //查询商品基本信息（关联模型查询）
        //嵌套关联太多，相同的大模型的关联只能写一个，不同的大模型的关联可以写多个。
        //也就是说，category_row.brands和type_row.specs、type_row.specs.spec_values可以同时使用查询出数据，因为他们分别是不同的大模型（category_row和type_row）的关联
        //而type_row.attrs和type_row.specs，type_row.specs.spec_values不能同时使用，非要同时使用只能查出后一个关联的数据出来，因为它们同是type_row下面的关联

        /*$goods                      = \app\common\model\Goods::with('category_row,category_row.brand,brand_row,goods_images,spec_goods,type_row,type_row.specs,type_row.specs.spec_values')->find($id);
        $goods['type_row']['attrs'] = \app\common\model\Type::with('attrs')->find($goods['type_id']);
        $goods['category']          = $goods['category_row'];
        $goods['brand']             = $goods['brand_row'];
        $goods['type']              = $goods['type_row'];
        unset($goods['category_row']);
        unset($goods['brand_row']);
        unset($goods['type_row']);*/

        $goods             = \app\common\model\Goods::with('category_row,category_row.brand,brand_row,goods_images,spec_goods')->find($id);
        $goods['category'] = $goods['category_row'];
        $goods['brand']    = $goods['brand_row'];
        unset($goods['category_row']);
        unset($goods['brand_row']);
        //单独查询所属模型及规格属性等信息
        $goods['type'] = \app\common\model\Type::with('specs,specs.spec_values,attrs')->find($goods['type_id']);

        /*$goods = $goods->toArray();
        dump($goods);die;*/

        //查询分类信息（所有一级、所属一级的二级、所属二级的三级）
        $cate_one = \app\common\model\Category::where('pid', 0)->select();
        //从商品所属的三级分类的pid_path中，取出所属的二级id和一级id
        //查询所属一级的所有二级
        $cate_two = \app\common\model\Category::where('pid', $goods['category']['pid_path'][1])->select();
        //查询所属二级的所有三级
        $cate_three = \app\common\model\Category::where('pid', $goods['category']['pid_path'][2])->select();
        //查询所有的类型信息
        $type = \app\common\model\Type::select();
        //返回数据
        $date = [
            'goods'    => $goods,
            'category' => [
                'cate_one'   => $cate_one,
                'cate_two'   => $cate_two,
                'cate_three' => $cate_three,
            ],
            'type'     => $type,
        ];
        $this->ok($date);
//        return json($goods);


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
        /*$params = [
            'goods_name' => 'iphone X',
            'goods_price' => '8900',
            'goods_introduce' => 'iphone iphonex',
            'goods_logo' => '/uploads/goods/20190101/afdngrijskfsfa.jpg',
            'goods_images' => [
                '/uploads/goods/20190101/dfsssadsadsada.jpg',
                '/uploads/goods/20190101/adsafasdadsads.jpg',
                '/uploads/goods/20190101/dsafadsadsaasd.jpg',
            ],
            'cate_id' => '72',
            'brand_id' => '3',
            'type_id' => '16',
            'item' => [
                '18_21' => [
                    'value_ids'=>'18_21',
                    'value_names'=>'颜色：黑色；内存：64G',
                    'price'=>'8900.00',
                    'cost_price'=>'5000.00',
                    'store_count'=>100
                ],
                '18_22' => [
                    'value_ids'=>'18_22',
                    'value_names'=>'颜色：黑色；内存：128G',
                    'price'=>'9000.00',
                    'cost_price'=>'5000.00',
                    'store_count'=>50
                ]
            ],
            'attr' => [
                '7' => ['id'=>'7', 'attr_name'=>'毛重', 'attr_value'=>'150g'],
                '8' => ['id'=>'8', 'attr_name'=>'产地', 'attr_value'=>'国产'],
            ]
        ];*/

        //接收参数
        $params = input();
        //对富文本编辑器字段  goods_desc 过滤方式防范xss攻击
        $params['goods_desc'] = input('goods_desc', '', 'remove_xss');
        //参数检测
        $validate = $this->validate($params, [
            'goods_name|商品名称'   => 'require',
            'goods_price|商品价格'  => 'require|float|gt:0',
            //省略无数检测字段
            'goods_images|商品相册' => 'array',
            'item|规格值'          => 'require|array',
            'attr|属性值'          => 'require|array',
        ]);
        if ($validate != true) {
            $this->fail($validate);
        }
        //开启事务
        Db::startTrans();
        try {
            //商品logo图片
            if (isset($params['goods_logo']) && is_file($params['goods_logo'])) {
                //生成缩略图
                //\think\Image::open('.' . $params['goods_logo'])->thumb(210, 240)->save('.' . $params['goods_logo']);
                $goods_logo = dirname($params['goods_logo']) . DS . 'thumb_' . basename($params['goods_logo']);
                Image::open('.' . $params['goods_logo'])->thumb(210, 240)->save('.' . $goods_logo);
                $params['goods_logo'] = $goods_logo;
            }
            //商品属性值 json
            $params['goods_attr'] = json_encode($params['attr'], JSON_UNESCAPED_UNICODE);
            //修改商品数据
            \app\common\model\Goods::update($params, ['id' => $id], true);
            //相册图片 批量添加(继续上传新的相册图片)
            if (isset($params['goods_images'])) {
                $goods_images = [];
                foreach ($params['goods_images'] as $image) {
                    if (is_file('.' . $image)) {
                        //生成两种不同尺寸的缩略图  800*800  400*400
                        $pics_big  = dirname($image) . DS . 'thumb_800_' . basename($image);
                        $pics_sma  = dirname($image) . DS . 'thumb_400_' . basename($image);
                        $image_obj = Image::open('.' . $image);
                        $image_obj->thumb(800, 800)->save('.' . $pics_big);
                        $image_obj->thumb(400, 400)->save('.' . $pics_sma);
                        //组装数据批量添加
                        $goods_images [] = [
                            'goods_id' => $id,
                            'pics_big' => $pics_big,
                            'pics_sma' => $pics_sma,
                        ];
                    }
                }
                //批量添加
                $goods_images_model = new GoodsImages();
                $goods_images_model->saveAll($goods_images);
            }
            //删除原来的规格商品SKU
            \app\common\model\SpecGoods::destroy(['goods_id' => $id]);
            //添加新的规格商品SKU
            $spec_goods = [];
            foreach ($params['item'] as $k => $v) {
                $v['goods_id'] = $id;
                $spec_goods[]  = $v;
            }
            $spec_goods_model = new \app\common\model\SpecGoods();
            $spec_goods_model->allowField(true)->saveAll($spec_goods);
            //提交事务
            \think\Db::commit();
            //返回数据
            $info = \app\common\model\Goods::with('category,brand,type')->find($id);
            $this->ok($info);
        } catch (Exception $e) {
            //回滚事务
            Db::rollback();
            $this->fail('操作失败');
        }


    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //上架的商品必须先下架再删除
        $goods = \app\common\model\Goods::find($id);
        if (empty($goods)) {
            $this->fail('数据异常，商品已经不存在');
        }
        if ($goods['is_on_sale']) {
            //上架中，无法删除
            $this->fail('上架中，无法删除');
        }
        //删除
        $goods->delete();
        //\app\common\model\Goods::destroy($id);
        $this->ok();
    }

    /**
     * 删除相册照片
     */
    public function delpics($id)
    {
        /*//删除一张相册图片
        \app\common\model\GoodsImages::destroy($id);
        //返回数据
        $this->ok();*/
        //查询要删除的记录（获取图片路径）
        $info = GoodsImages::find($id);
        if (empty($info)) {
            $this->fail('数据异常，图片不存在');
        }
        //从数据表删除一张相册图片的记录
        $info->delete();
        //从磁盘中删除对应的两张图片
        unlink('.' . $info['pics_big']);
        unlink('.' . $info['pics_sma']);
        $this->ok();
    }
}

<?php

namespace app\admin\model;

use think\Model;

class Auth extends Model
{
    protected $hidden = ['create_time', 'update_time', 'delete_time'];

    //通过获取器，对is_nav字段值进行转化  方法名 get字段名Attr
    public function getIsNavAttr($value)
    {
        //is_nav字段  1 是， 0 否
        return $value ? '是' : '否';
    }

    public function roleAuth()
    {
        //参数： 关联表  中间表  中间表内的关联表id  中间表内的当前本表id
        return $this->belongsToMany('role', 'roleAuth', 'role_id', 'auth_id');
    }
}

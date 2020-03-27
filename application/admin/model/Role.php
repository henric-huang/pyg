<?php

namespace app\admin\model;

use think\Model;

class Role extends Model
{
    protected $hidden = ['create_time', 'update_time', 'delete_time'];

    public function users()
    {
        return $this->hasMany('Admin');
    }

    public function roleAuth()
    {
        //参数： 关联表  中间表  中间表内的关联表id  中间表内的当前本表id
        return $this->belongsToMany('Auth', 'RoleAuth', 'auth_id', 'role_id');
    }

}

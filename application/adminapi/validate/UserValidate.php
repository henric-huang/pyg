<?php

namespace app\adminapi\validate;

use think\Validate;

class UserValidate extends Validate
{
    protected $rule = [
        'auth_name|权限名称' => 'require',
        'pid|上级权限'       => 'require',
        'is_nav|菜单权限'    => 'require',
        //'auth_c|控制器名称'   => 'require',
        //'auth_a|方法名称'    => 'require',
    ];

    protected $message = [
        'auth_name.require' => '请填写权限名称',
        'pid.require'       => '请填写上级权限',
        'is_nav.require'    => '请填写菜单权限',
    ];

}
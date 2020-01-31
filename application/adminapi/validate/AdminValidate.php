<?php

namespace app\adminapi\validate;

use think\Validate;

class AdminValidate extends Validate
{
    protected $rule = [
        'username|用户名'   => 'require|unique:admin',
        'email|邮箱'       => 'require|email',
        'role_id|所属角色id' => 'require|integer|gt:0',
        'password|密码'    => 'length:6,20',
    ];

    /*protected $message = [
        'username.require' => '请填写用户名',
        'username.unique'  => '该用户名已被占用',
        'email.require'    => '请填写邮箱',
        'email.email'      => '请填写正确的邮箱格式',
        'role_id.require'  => '请填写所属角色id',
        'role_id.integer'  => '请填写整数',
        'role_id.gt'       => '该数应大于0',
        'password.require' => '密码长度应在6~20个字符',
    ];*/

}
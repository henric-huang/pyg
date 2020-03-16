<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Type extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $hidden = ['create_time', 'update_time', 'delete_time'];

    public function attrs()
    {
        return $this->hasMany('Attribute');
    }

    public function specs()
    {
        return $this->hasMany('Spec');
    }
}

<?php

namespace app\common\model;

use think\Model;

class SpecValue extends Model
{
    protected $resultSetType = 'collection';
    protected $hidden = ['create_time','update_time','delete_time'];

    //定义 规格值 - 规格名的关联  一个规格值属于一个规格名
    public function spec()
    {
        return $this->belongsTo('Spec', 'spec_id', 'id')->bind('spec_name');
    }
}




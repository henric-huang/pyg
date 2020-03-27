<?php

namespace app\admin\controller;

use think\Cache;
use think\cache\driver\Redis;
use think\Controller;

class Redistodb extends Controller
{
    //
    public function redistodb()
    {
//        Cache::store('redis')->set('myname', 'value');

        /*for ($i = 0; $i <= 10; $i++) {
            Cache::store('redis')->rpush('index', $i);
        }
        echo 'ok';*/

        // 实例化redis对象
        /*$redis = new \Redis();
        // 连接redis  5秒超时
        $redis->connect('127.0.0.1', 6379, 5);
        // 认证
        $redis->auth('beijing');

        $data = $redis->get('myname');
        dump($data);

        for ($i = 0; $i <= 10; $i++) {
            $redis->lPush('index', $i);
        }
        echo 'ok';*/

        /*for ($i = 0; $i <= 10; $i++) {
            $data = $redis->rpop('index');
            echo $data . '<br>';
        }*/
        return $this->redirect('home/index/index');

    }


}

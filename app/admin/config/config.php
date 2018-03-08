<?php
/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/7
 * Time: 13:45
 */
return [
    //默认路由
    'defaultRoute' => 'index/index',
    //数据库
    'DB' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=127.0.0.1;dbname=my_love',
        'username' => 'root',
        'password' => 'root',
        'charset' => 'utf8,'
    ],
    //redis
    'Redis' => [

    ],
    //文件缓存
    'Cache' => [

    ],
];
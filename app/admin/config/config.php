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
        'class' => 'wFrame\app\Connection',
        'dsn' => 'mysql:host=127.0.0.1;dbname=my_love',
        'username' => 'root',
        'password' => 'root',
        'charset' => 'utf8,'
    ],
    //redis
    'Redis' => [
        'class' => 'wFrame\app\Connection',
        'dsn' => 'mysql:host=127.0.0.1;dbname=my_love',
        'username' => 'root',
        'password' => 'root',
        'charset' => 'utf8,'
    ],
    //文件缓存
    'Cache' => [
        'class' => 'wFrame\app\Connection',
    ],
    //路由美化
    'PrettifyUrl' => [
        //美化开关
        'switch' => false,
        //规则映射
        'rule' => [
            '/aaa' => '/index/index'
        ],
    ],
    //特殊字符过滤正则
    'Filter' => [
        //参数过滤正则
        'params' => "/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/",
        //邮箱
        'mail' => '',
    ],
];
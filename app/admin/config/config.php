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
        'host' => '127.0.0.1',
        'port' => '6379',
        'password' => '',
    ],
    //文件缓存
    'Cache' => [
        //缓存路径
        'cachePath' => RUNTIME_PATH . '/cache',
    ],
    //模板配置
    'Layout' => [
        //模板开关
        'switch' => true,
        //默认使用模板
        'use' => 'wFrame',
        //模板文件注册(按加载顺序填写,当前视图使用关键字self)
        'rule' => [
            'wFrame' => [
                'head',
                'self',
                'foot',
            ],
        ],
    ],
    //路由美化
    'PrettifyUrl' => [
        //美化开关
        'switch' => true,
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
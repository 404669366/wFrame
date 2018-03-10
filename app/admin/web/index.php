<?php
/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/7
 * Time: 13:28
 */
//基础框架路径
defined('FRAME_PATH') or define('FRAME_PATH', __DIR__ . '/../../../wFrame');
//vendor路径
defined('VENDOR_PATH') or define('VENDOR_PATH', __DIR__ . '/../../../vendor');
//项目路径
defined('APP_PATH') or define('APP_PATH', __DIR__ . '/../');
//视图路径
defined('VIEW_PATH') or define('VIEW_PATH', __DIR__ . '/../view/');
//模板路径
defined('LAYOUT_PATH') or define('LAYOUT_PATH', __DIR__ . '/layout');
//缓存路径
defined('RUNTIME_PATH') or define('RUNTIME_PATH', __DIR__ . '/../../../runtime');

//导入配置
$config = require(__DIR__ . '/../config/config.php');
//定义配置常量
defined('CONFIG') or define('CONFIG', $config);

require(FRAME_PATH . '/wFrame.php');
wFrame::run();
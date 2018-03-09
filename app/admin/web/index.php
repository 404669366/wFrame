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
//缓存路径
defined('RUNTIME_PATH') or define('RUNTIME_PATH', __DIR__ . '/../../../runtime');
//配置
$config = require(__DIR__ . '/../config/config.php');
defined('CONFIG') or define('CONFIG', $config);

require(FRAME_PATH . '/FrameRun.php');
FrameRun::run();
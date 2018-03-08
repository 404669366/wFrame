<?php

/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/7
 * Time: 14:24
 */
class FrameRun
{
    /**
     * 根命名空间路径映射
     * @var array
     */
    public static $pathMap = [
        'vendor' => VENDOR_PATH,
        'wFrame' => FRAME_PATH,
        'app' => APP_PATH,
    ];

    /**
     * 框架入口
     */
    public static function run()
    {
        try {
            $urlInfo = new \wFrame\app\UrlTranslate();
            $controllerName = 'app\controller\\' . $urlInfo->controller;
            $actionName = $urlInfo->action;
            $controllerModel = new $controllerName();
            $controllerModel->beforeAction();
            $controllerModel->$actionName();
            $controllerModel->afterAction();
        } catch (Exception $e){
            echo $e->getMessage();
            exit();
        }
    }

    /**
     * 自动加载器
     * @param $class
     */
    public static function load($class)
    {
        $namespaceFirst = substr($class, 0, strpos($class, '\\')); // 顶级命名空间
        $dir = self::$pathMap[$namespaceFirst]; // 文件基目录
        $filePath = substr($class, strlen($namespaceFirst)) . '.php'; // 文件相对路径
        $file = strtr($dir . $filePath, '\\', '/');
        if (file_exists($file)) {
            include $file;
        }
    }
}

spl_autoload_register('FrameRun::load'); // 注册自动加载
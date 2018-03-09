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
            $object = new \wFrame\app\App();
            $object->run();
        } catch (Exception $e) {
            \wFrame\app\Error::showError($e);
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
        if (!file_exists($file)) {
            \wFrame\app\Error::addError('访问错误');
        }
        include $file;
    }
}

spl_autoload_register('FrameRun::load'); // 注册自动加载
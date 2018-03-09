<?php
/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/7
 * Time: 16:30
 */

namespace wFrame\app;

class App
{
    //当前访问路由
    public $url;
    //匹配的控制器路径
    public $realController;
    //当前访问控制器
    public $controller;
    //当前访问方法
    public $action;

    public function __construct()
    {
        $this->url = self::getUrl();
        $this->translate();
    }

    /**
     * 创建控制器模型,开始程序
     */
    public function controllerBegin()
    {
        $controllerName = $this->realController;
        $action = $this->action;
        $controller = new $controllerName();
        $controller->beforeAction();
        $controller->$action();
        $controller->afterAction();
    }

    /**
     * 获取处理后的URL
     * @return mixed|string
     */
    public static function getUrl()
    {
        $url = $_SERVER["REQUEST_URI"];
        $bad = strstr($url, '?');
        if ($bad) {
            $url = str_replace($bad, '', $url);
        }
        if ($url == '/') {
            $url .= CONFIG['defaultRoute'];
        }
        if (CONFIG['PrettifyUrl']['switch']) {
            if (isset(CONFIG['PrettifyUrl']['rule'][$url])) {
                return CONFIG['PrettifyUrl']['rule'][$url];
            }
        }
        return $url;
    }

    /**
     * 翻译路由
     */
    public function translate()
    {
        $arr = explode('/', $this->url);
        $count = count($arr);
        $dir = '';
        if ($count > 3) {
            $dir = array_slice($arr, 1, $count - 3);
            $dir = implode($dir, '\\') . '\\';
        }
        $this->controller = ucfirst($arr[$count - 2]) . 'Controller';
        $this->realController = 'app\controller\\' . $dir . ucfirst($arr[$count - 2]) . 'Controller';
        $this->action = 'action' . ucfirst($arr[$count - 1]);
    }
}
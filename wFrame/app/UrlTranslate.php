<?php
/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/7
 * Time: 16:30
 */

namespace wFrame\app;

class UrlTranslate
{
    //当前访问路由
    public $url;
    //匹配的控制器路径
    public $controllerPath;
    //当前访问控制器
    public $controller;
    //当前访问方法
    public $action;

    public function __construct()
    {
        $this->url = $_SERVER["REQUEST_URI"];
        if ($this->url == '/') {
            $this->url .= CONFIG['defaultRoute'];
        }
        $translate = self::translate($this->url);
        $this->controller = $translate['controller'];
        $this->action = $translate['action'];
    }

    /**
     * 翻译路由
     * @param $url
     * @return array
     */
    public static function translate($url)
    {
        $arr = explode('/', $url);
        $controller = ucfirst($arr[1]) . 'Controller';
        $default = explode('/', CONFIG['defaultRoute']);
        isset($arr[2]) ? $action = $arr[2] : $action = $default[1];
        $action = 'action' . ucfirst($action);
        return [
            'controller' => $controller,
            'action' => $action
        ];
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/8
 * Time: 11:43
 */

namespace wFrame\app;


class Render
{
    private $viewPath;

    public function __construct($view = '', $class)
    {
        if (strstr($view, '\\') || strstr($view, '/')) {
            $path = VIEW_PATH . $view . '.php';
        } else {
            $path = str_replace('app\\controller\\', '', $class);
            $path = str_replace('Controller', '', $path) . '/';
            $path = VIEW_PATH . strtolower($path) . $view . '.php';
        }
        if (!file_exists($path)) {
            throw new \Exception('视图文件不存在');
        }
        $this->viewPath = $path;
    }

    public function render($data)
    {
        if (!is_array($data)) {
            throw new \Exception('视图参数必须是数组');
        }
        ob_start();
        extract($data, EXTR_OVERWRITE);
        include $this->viewPath;
        $content = ob_get_contents();
        return $content;
    }
}
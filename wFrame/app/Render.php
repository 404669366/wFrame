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

    public function __construct($view = '')
    {
        $path = APP_PATH . 'view/' . $view . '.php';
        if (!file_exists($path)) {
            throw new \Exception('视图文件不存在');
        }
        $this->viewPath = $path;
    }

    public function render($data)
    {
        if(!is_array($data)){
            throw new \Exception('视图参数必须是数字');
        }
        ob_start();
        extract($data, EXTR_OVERWRITE);
        include $this->viewPath;
        $content = ob_get_contents();
        return $content;
    }

}
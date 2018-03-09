<?php
/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/8
 * Time: 11:06
 */

namespace wFrame\web;


use wFrame\app\Render;

class Controller
{
    public function beforeAction()
    {
    }

    public function afterAction()
    {
    }

    /**
     * 加载视图
     * @param string $view
     * @param array $data
     * @return Render
     */
    public function render($view = '', $data = [])
    {
        $render = new Render($view,static::class);
        return $render->render($data);
    }

}
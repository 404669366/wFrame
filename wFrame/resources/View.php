<?php
/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/9
 * Time: 13:11
 */

namespace wFrame\resources;


class View
{
    /**
     * 加载视图
     * @param string $view
     * @param array $data
     * @param string $firstPath
     * @return string
     */
    public static function loadView($view = '', $data = [], $firstPath = '')
    {
        if (!$firstPath) {
            $firstPath = __DIR__ . '/';
        }
        if (!is_array($data)) {
            throw new \Exception('视图参数必须是数组');
        }
        ob_start();
        extract($data, EXTR_OVERWRITE);
        include $firstPath . $view . '.php';
        $content = ob_get_contents();
        return $content;
    }
}
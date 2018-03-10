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
    /**
     * @var string 当前视图路径
     */
    private $viewPath;
    /**
     * @var string 当前使用的模板
     */
    private $layout;

    /**
     * @var bool 当前是否开启模板
     */
    private $layoutSwitch;

    /**
     * Render constructor.
     * @param string $view
     * @param $class
     * @param string $layout
     * @param bool $layoutSwitch
     */
    public function __construct($view = '', $class, $layout = '', $layoutSwitch = true)
    {
        if (strstr($view, '\\') || strstr($view, '/')) {
            $path = VIEW_PATH . $view . '.php';
        } else {
            $path = str_replace('app\\controller\\', '', $class);
            $path = str_replace('Controller', '', $path) . '/';
            $path = VIEW_PATH . strtolower($path) . $view . '.php';
        }
        if (!file_exists($path)) {
            Error::addError('视图文件不存在');
        }
        $this->viewPath = $path;
        $this->layout = $layout;
        $this->layoutSwitch = $layoutSwitch;
    }

    /**
     * 加载视图
     * @param $data
     * @return bool
     */
    public function render($data)
    {
        if (!is_array($data)) {
            Error::addError('视图参数必须是数组');
        }
        return $this->load($data);
    }

    /**
     * 加载视图逻辑
     * @param array $data
     * @return bool
     */
    private function load($data = [])
    {
        $config = CONFIG['Layout'];
        $result = false;
        if ($this->layoutSwitch == true && $config['switch'] == true) {
            $result = $this->loadViewByLayout($config, $data);
        }
        if ($this->layoutSwitch == true && $config['switch'] == false) {
            $result = $this->loadViewByLayout($config, $data);
        }
        if ($this->layoutSwitch == false && $config['switch'] == true) {
            $result = $this->loadViewBySelf($data);
        }
        if ($this->layoutSwitch == false && $config['switch'] == false) {
            $result = $this->loadViewBySelf($data);
        }
        return $result;
    }

    /**
     * 模板加载
     * @param $config
     * @param $data
     * @return bool
     */
    private function loadViewByLayout($config, $data)
    {
        $this->layout ? $use = $this->layout : $use = $config['use'];
        if (!$use) {
            Error::addError('未配置模板');
        }
        if (!isset($config['rule'][$use]) || !$config['rule'][$use]) {
            Error::addError('未配置模板加载规则');
        }
        ob_start();
        $rule = $config['rule'][$use];
        foreach ($rule as $name) {
            if ($name == 'self') {
                extract($data, EXTR_OVERWRITE);
                include $this->viewPath;
            } else {
                $path = LAYOUT_PATH . '/' . $use . '/' . $name . '.php';
                if (file_exists($path)) {
                    include $path;
                }
            }
            ob_flush();
        }
        flush();
        return true;
    }

    /**
     * 只加载当前
     * @param $data
     * @return bool
     */
    private function loadViewBySelf($data)
    {
        ob_start();
        extract($data, EXTR_OVERWRITE);
        include $this->viewPath;
        ob_flush();
        flush();
        return true;
    }
}
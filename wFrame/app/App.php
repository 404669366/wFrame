<?php
/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/7
 * Time: 16:30
 */

namespace wFrame\app;

/**
 * Class App
 * @package wFrame\app
 */
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
    //get参数
    public $get;
    //post参数
    public $post;
    //自生对象
    private $instance;

    public function __construct()
    {
        $this->instance = &$this;
        $this->get = $this->handle($_GET);
        $this->post = $this->handle($_POST);
        $this->url = self::getUrl();
        $this->translate();
    }

    /**
     * 创建控制器模型,开始程序
     */
    public function run()
    {
        $controllerName = $this->realController;
        $action = $this->action;
        $controller = new $controllerName();
        $controller->beforeAction();
        $controller->$action($this->instance);
        $controller->afterAction();
    }

    /**
     * 获取自定义响应头参数
     * @param string $key
     * @return string
     */
    public function headers($key = '')
    {
        if ($key) {
            $key = 'HTTP_' . strtoupper($key);
            if (isset($_SERVER[$key])) {
                $key = $this->handle($_SERVER[$key]);
            }
        }
        return $key;
    }

    /**
     * 是否是AJAX提交
     * @return bool
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ? true : false;
    }

    /**
     * 是否是GET提交
     * @return bool
     */
    public function isGet()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' ? true : false;
    }

    /**
     * 是否是POST提交
     * @return bool
     */
    public function isPost()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' ? true : false;
    }

    /**
     * 特殊字符过滤
     * @param $params
     * @param string $ruleName
     * @return mixed
     */
    public function handle($params, $ruleName = 'params')
    {
        if ($params) {
            $rule = '';
            if (isset(CONFIG['Filter'][$ruleName])) {
                $rule = CONFIG['Filter'][$ruleName];
            }
            if (is_array($params)) {
                foreach ($params as &$v) {
                    $v = preg_replace($rule, '', $v);
                }
            } else {
                $params = preg_replace($rule, '', $params);
            }
        }
        return $params;
    }

    /**
     * 翻译路由
     */
    private function translate()
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

    /**
     * 获取处理后的URL
     * @return mixed|string
     */
    private static function getUrl()
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
}
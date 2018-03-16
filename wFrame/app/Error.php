<?php
/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/9
 * Time: 16:59
 */

namespace wFrame\app;


class Error
{
    /**
     * 抛出异常
     * @param $msg
     */
    public static function addError($msg)
    {
        throw new \Exception($msg);
    }

    /**
     * 异常消息
     * @param $msg
     */
    public static function showError($msg)
    {
        $content = <<<HTML
                    <!DOCTYPE html>
                        <html>
                        <head>
                            <meta charset="utf-8">
                            <title>wFrame</title>
                            <meta name="renderer" content="webkit">
                            <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
                            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
                            <meta name="apple-mobile-web-app-status-bar-style" content="black">
                            <meta name="apple-mobile-web-app-capable" content="yes">
                            <meta name="format-detection" content="telephone=no">
                            <script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
                            <script type="text/javascript" src="http://apps.bdimg.com/libs/layer/2.1/layer.js"></script>
                        </head>
                        <body>
                            <div style="width: 80%;margin: 100px auto;font-size: 18px;color: red">
                            {$msg}
                            </div>
                            <script>
                                layer.msg("{$msg}");
                            </script>
                        </body>
                        </html>
HTML;
        echo $content;
        exit();
    }
}
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
        throw new \Exception('$msg');
    }

    /**
     * 显示异常
     * @param $e
     */
    public static function showError($e)
    {
        SendMsg::sendErrorMsg($e->getMessage());
    }
}
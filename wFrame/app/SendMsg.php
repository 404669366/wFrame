<?php
/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/9
 * Time: 11:32
 */

namespace wFrame\app;


use wFrame\resources\View;

class SendMsg
{
    public static function sendErrorMsg($msg)
    {
        View::loadView('Error', ['msg' => $msg]);
    }
}
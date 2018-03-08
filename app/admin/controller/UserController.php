<?php
/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/8
 * Time: 14:49
 */

namespace app\controller;


use wFrame\web\Controller;

class UserController extends Controller
{
    public function actionLogin(){
        return $this->render('user/login',['info'=>'这里是登陆页']);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/7
 * Time: 17:47
 */

namespace app\controller;

use wFrame\web\Controller;

class IndexController extends Controller
{
    public function actionIndex(){
        return $this->render('index',['data'=>'index']);
    }
}
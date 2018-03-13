<?php
/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/7
 * Time: 17:47
 */

namespace app\controller;

use app\model\Test;
use wFrame\web\Controller;

class IndexController extends Controller
{
    public function actionIndex()
    {
        var_dump(Test::do()
            ->where([['name','like','abc']])
            ->andWhere([['name','like','abc'],['name','like','abc']])
            ->orWhere([['name','like','abc']])
            ->buildSql());

        //return $this->render('index', ['data' => '这里是首页']);
    }

}
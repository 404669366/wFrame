<?php
/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/7
 * Time: 17:47
 */

namespace app\controller;

use wFrame\app\SqlBuild;
use wFrame\web\Controller;

class IndexController extends Controller
{
    public function actionIndex()
    {
        var_dump(SqlBuild::find()
            ->select()
            ->from(['user','u'])
            ->where(['name',123])
            ->getSql());
        return $this->render('index', ['data' => '这里是首页']);
    }

}
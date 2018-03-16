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
        Test::key();
        exit();
        $model = Test::find()->from([Test::tableName()=>'bf'])
            ->select(['bf.id','ce.realname','ce.email'])
            ->leftJoin(['com_employee'=>'ce'],['bf.user_id'=>'ce.userid'])
            ->one();
        var_dump($model);
        exit();
        $model->id = 120;
        $re = $model->save();
        var_dump($re);
    }

}
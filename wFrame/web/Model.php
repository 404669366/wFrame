<?php
/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/9
 * Time: 16:47
 */

namespace wFrame\web;


use wFrame\app\SqlBuild;

class Model extends SqlBuild
{
    //数据库名（子类必须配置,自动生成忽略）
    protected $DBName = '';
    //表名（子类不配置默认为子类类名）
    protected $tableName = '';
    //表字段名（子类必须配置,自动生成忽略）
    protected $filed = [];
}
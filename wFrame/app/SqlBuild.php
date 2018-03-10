<?php
/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/10
 * Time: 17:34
 */

namespace wFrame\app;


class SqlBuild
{
    /**
     * 查询单例方法
     * @return object|SqlBuild
     */
    public static function find()
    {
        if (!self::$sqlModel) {
            self::$sqlModel = new self();
        }
        return self::$sqlModel;
    }

    /**
     * sql关键词
     * @var array
     */
    private $words = [
        'select' => 'SELECT ',
        'from' => ' FROM ',
        'as' => ' AS ',
        'where' => ' WHERE ',
        'andWhere' => ' ANDWHERE ',
        'order' => ' ORDER BY ',
        'limit' => ' LIMIT 0 , ',
    ];
    /**
     * @var string 当前sql语句
     */
    private $sql;
    /**
     * @var object 自身对象
     */
    private static $sqlModel;

    /**
     * @param array $select
     * @return $this
     */
    public function select($select = [])
    {
        $select = $select ? $select : ['*'];
        $select = count($select) > 1 ? implode(' , ', $select) : $select[0];
        $this->sql .= $this->words['select'] . $select;
        return $this;
    }

    /**
     * @param array $tableName
     * @return $this
     */
    public function from($tableName = [])
    {
        $tableName = $tableName ? $tableName : [''];
        $this->sql .= $this->words['from'] . $tableName[0];
        if (count($tableName) > 1) {
            $this->sql .= $this->words['as'] . $tableName[1];
        }
        return $this;
    }

    /**
     * @param array $where
     * @return $this
     */
    public function where($where = [])
    {
        if ($where) {
            $this->sql .= count($where) > 1 ? $this->words['where'] . $where[0] . '=' . $where[1] : $this->words['where'] . 'id=' . $where[0];
        }
        return $this;
    }

    public function andWhere($where = [])
    {
        $this->sql .= $this->words['where'] . $where;
        return $this;
    }

    public function order($order = 'id DESC')
    {
        $this->sql .= $this->words['order'] . $order;
        return $this;
    }

    public function limit($limit = '30')
    {
        $this->sql .= $this->words['limit'] . $limit;
        return $this;
    }

    /**
     * 获取当前sql
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }
}
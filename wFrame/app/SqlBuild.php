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
     * MYSQL操作单例入口
     * @return object|SqlBuild
     */
    public static function do()
    {
        if (!self::$sqlModel) {
            self::$sqlModel = new static();
        }
        return self::$sqlModel;
    }

    private $words = [
        'select' => 'SELECT ',
        'from' => ' FROM ',
        'as' => ' AS ',
        'where' => ' WHERE ',
        'order' => ' ORDER BY ',
        'group' => ' GROUP BY ',
        'limit' => ' LIMIT ',
        'offset' => ' OFFSET ',
        'leftJoin' => ' LEFT JOIN ',
        'in' => ' IN ',
        'not' => ' NOT ',
        'and' => ' AND ',
        'or' => ' OR ',
        'on' => ' ON ',
        'insert' => 'INSERT INTO ',
        'delete' => 'DELETE ',
        'values' => ' VALUES ',
        'update' => 'UPDATE ',
        'set' => ' SET ',
    ];

    private $selectOrder = [
        'select' => 'SELECT *',
        'from' => '',
        'leftJoin' => '',
        'where' => '',
        'limit' => '',
        'offset' => '',
        'group' => '',
        'order' => '',
    ];

    private static $sqlModel;


    //todo**************************查询*******************************

    /**
     * 查询头（此方法不使用默认查所有）
     * @param array $select like:['id'] or ['id','name',...]
     * @return $this
     */
    public function select($select = ['*'])
    {
        $select = count($select) > 1 ? implode(' , ', $select) : $select[0];
        $this->selectOrder['select'] = $this->words['select'] . $select;
        return $this;
    }


    /**
     * 定位表（此方法不使用默认当前表）
     * @param array $tableName like：['user'=>'u'] or ['user'=>'u','member'=>'m',...]
     * @return $this
     */
    public function from($tableName = ['' => ''])
    {
        foreach ($tableName as $table => $as) {
            if (!$this->selectOrder['from']) {
                $this->selectOrder['from'] = $this->words['from'] . $table . $this->words['as'] . $as;
            } else {
                $this->selectOrder['from'] .= ',' . $table . $this->words['as'] . $as;
            }
        }
        return $this;
    }


    /**
     * where子句
     * @param array $where
     * like：[['age','<>',[1,2,3]],['name','like','abc'],'id'=>[1,2,3],'id'=>123,...]
     * @return $this
     */
    public function where($where = [])
    {
        $this->selectOrder['where'] = '';
        if ($where) {
            end($where);
            $endKey = key($where);
            foreach ($where as $field => $v) {
                if (!is_numeric($field) && !is_array($v)) {
                    if ($field == $endKey && $this->selectOrder['where']) {
                        $this->selectOrder['where'] .= $this->words['and'] . $field . "='" . $v . "' )";
                    } elseif ($field == $endKey && !$this->selectOrder['where']) {
                        $this->selectOrder['where'] .= $this->words['where'] . $field . "='" . $v . "'";
                    } elseif ($field != $endKey && $this->selectOrder['where']) {
                        $this->selectOrder['where'] .= $this->words['and'] . $field . "='" . $v . "'";
                    } else {
                        $this->selectOrder['where'] .= $this->words['where'] . '( ' . $field . "='" . $v . "'";
                    }
                } elseif (!is_numeric($field) && is_array($v)) {
                    $v = "('" . implode("','", $v) . "')";
                    if ($field == $endKey && $this->selectOrder['where']) {
                        $this->selectOrder['where'] .= $this->words['and'] . $field . $this->words['in'] . $v . " )";
                    } elseif ($field == $endKey && !$this->selectOrder['where']) {
                        $this->selectOrder['where'] .= $this->words['where'] . $field . $this->words['in'] . $v;
                    } elseif ($field != $endKey && $this->selectOrder['where']) {
                        $this->selectOrder['where'] .= $this->words['and'] . $field . $this->words['in'] . $v;
                    } else {
                        $this->selectOrder['where'] .= $this->words['where'] . '( ' . $field . $this->words['in'] . $v;
                    }
                } else {
                    if (count($v) == 3) {
                        if (is_array($v[2])) {
                            end($v[2]);
                            $end = key($v[2]);
                            if ($v[1] == 'like') {
                                foreach ($v[2] as $k => $va) {
                                    $va = $v[0] . " like '%" . $va . "%'";
                                    if ($field == $endKey && $k == $end && $this->selectOrder['where']) {
                                        $this->selectOrder['where'] .= $this->words['and'] . $va . ' )';
                                    } elseif ($field == $endKey && $k == $end && !$this->selectOrder['where']) {
                                        $this->selectOrder['where'] .= $this->words['where'] . $va;
                                    } elseif ($this->selectOrder['where']) {
                                        $this->selectOrder['where'] .= $this->words['and'] . $va;
                                    } else {
                                        $this->selectOrder['where'] .= $this->words['where'] . '( ' . $va;
                                    }
                                }
                            } else {
                                foreach ($v[2] as $k => $va) {
                                    $va = $v[0] . ' ' . $v[1] . " '" . $va . "'";
                                    if ($field == $endKey && $k == $end && $this->selectOrder['where']) {
                                        $this->selectOrder['where'] .= $this->words['and'] . $va . ' )';
                                    } elseif ($field == $endKey && $k == $end && !$this->selectOrder['where']) {
                                        $this->selectOrder['where'] .= $this->words['where'] . $va;
                                    } elseif ($this->selectOrder['where']) {
                                        $this->selectOrder['where'] .= $this->words['and'] . $va;
                                    } else {
                                        $this->selectOrder['where'] .= $this->words['where'] . '( ' . $va;
                                    }
                                }
                            }
                        } else {
                            $va = $v[1] == 'like' ? "'%" . $v[2] . "%'" : "'" . $v[2] . "'";
                            if ($field == $endKey && $this->selectOrder['where']) {
                                $this->selectOrder['where'] .= $this->words['and'] . $v[0] . ' ' . $v[1] . ' ' . $va . " )";
                            } elseif ($field == $endKey && !$this->selectOrder['where']) {
                                $this->selectOrder['where'] .= $this->words['where'] . $v[0] . ' ' . $v[1] . ' ' . $va;
                            } elseif ($this->selectOrder['where']) {
                                $this->selectOrder['where'] .= $this->words['and'] . $v[0] . ' ' . $v[1] . ' ' . $va;
                            } else {
                                $this->selectOrder['where'] .= $this->words['where'] . '( ' . $v[0] . ' ' . $v[1] . ' ' . $va;
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }

    public function andWhere($where = [])
    {
        $this->selectOrder['where'] .= $this->words['where'] . $where;
        return $this;
    }

    public function orWhere($where = [])
    {
        $this->selectOrder['where'] .= $this->words['where'] . $where;
        return $this;
    }

    /**
     * 排序
     * @param array $orders like：['id'=>'DESC'] or ['id'=>'DESC','age'=>'ASC',...]
     * @return $this
     */
    public function orderBy($orders = ['id' => 'DESC'])
    {
        foreach ($orders as $field => $order) {
            if ($this->selectOrder['order']) {
                $this->selectOrder['order'] .= $this->selectOrder['order'] . ',' . $field . ' ' . $order;
            } else {
                $this->selectOrder['order'] = $this->words['order'] . $field . ' ' . $order;
            }
        }
        return $this;
    }

    /**
     * 分组
     * @param array $group like：['id'] or ['id','name',...]
     * @return $this
     */
    public function groupBy($group = ['id'])
    {
        foreach ($group as $k => $field) {
            if (!$k) {
                $this->selectOrder['group'] = $this->words['group'] . $field;
            } else {
                $this->selectOrder['group'] .= ',' . $field;
            }
        }
        return $this;
    }

    /**
     * 限定输出（可单独使用，亦可配合offset使用）
     * @param int $limit
     * @return $this
     */
    public function limit($limit = 0)
    {
        if ($limit) {
            $this->selectOrder['limit'] = $this->words['limit'] . $limit;
        }
        return $this;
    }

    /**
     * 配合limit使用
     * @param int $offset
     * @return $this
     */
    public function offset($offset = 0)
    {
        if ($offset) {
            $this->selectOrder['offset'] = $this->words['offset'] . $offset;
        }
        return $this;
    }

    /**
     * 可多次调用实现多表联查
     * @param string $tableName
     * @param string $on
     * @return $this
     */
    public function leftJoin($tableName = '', $on = '')
    {
        if ($tableName && $on) {
            if (is_array($tableName) && count($tableName) == 2) {
                $this->selectOrder['leftJoin'] .= $this->words['leftJoin'] . $tableName[0] . $this->words['as'] . $tableName[1] . $this->words['on'] . $on;
            } else {
                $this->selectOrder['leftJoin'] .= $this->words['leftJoin'] . $tableName . $this->words['on'] . $on;
            }
        }
        return $this;
    }

    //todo**************************插入******************************

    public function insert()
    {

    }

    //todo**************************更新******************************

    public function update()
    {

    }

    //todo**************************执行******************************
    public static function tableName()
    {
        if (isset(self::$sqlModel->tableName)) {
            $tableName = self::$sqlModel->tableName;
        } else {
            $tableName = explode('\\', static::class);
            $tableName = strtolower($tableName[count($tableName) - 1]);
        }
        return $tableName;
    }

    public function buildSql()
    {
        $sql = $this->selectOrder;
        if (is_array($sql)) {
            $sql['from'] = $sql['from'] ? $sql['from'] : $this->words['from'] . self::tableName();
            $sql = implode('', $sql);
        }
        return $sql;
    }

}
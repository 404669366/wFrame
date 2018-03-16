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
    final public static function find()
    {
        if (!self::$sqlModel) {
            self::$sqlModel = new static();
        }
        return self::$sqlModel;
    }

    final public static function tableName()
    {
        if (isset(self::find()->tableName) && self::find()->tableName) {
            $tableName = self::find()->tableName;
        } else {
            $tableName = explode('\\', static::class);
            $tableName = strtolower($tableName[count($tableName) - 1]);
        }
        return $tableName;
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
        'rightJoin' => ' RIGHT JOIN ',
        'innerJoin' => ' INNER JOIN ',
        'fullJoin' => ' FULL JOIN ',
        'crossJoin' => ' CROSS JOIN ',
        'in' => ' IN ',
        'not' => ' NOT ',
        'and' => ' AND ',
        'or' => ' OR ',
        'on' => ' ON ',
        'like' => ' LIKE ',
        'find_in_set' => ' FIND_IN_SET ',
        'between' => ' BETWEEN ',
        'insert' => 'INSERT INTO ',
        'delete' => 'DELETE FROM ',
        'replace' => 'REPLACE INTO ',
        'update' => 'UPDATE ',
        'values' => ' VALUES ',
        'set' => ' SET ',
    ];

    private $selectOrder = [
        'select' => 'SELECT *',
        'from' => '',
        'crossJoin' => '',
        'leftJoin' => '',
        'rightJoin' => '',
        'innerJoin' => '',
        'fullJoin' => '',
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
    final public function select($select = ['*'])
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
    final public function from($tableName = ['' => ''])
    {
        $this->selectOrder['from'] = '';
        foreach ($tableName as $table => $as) {
            $as = '`' . $as . '`';
            if (!is_numeric($table)) {
                $table = '`' . $table . '`';
                if (!$this->selectOrder['from']) {
                    $this->selectOrder['from'] = $this->words['from'] . $table . $this->words['as'] . $as;
                } else {
                    $this->selectOrder['from'] .= ',' . $table . $this->words['as'] . $as;
                }
            } else {
                if (!$this->selectOrder['from']) {
                    $this->selectOrder['from'] = $this->words['from'] . $as;
                } else {
                    $this->selectOrder['from'] .= ',' . $as;
                }
            }
        }
        return $this;
    }


    /**
     *  where子句
     * @param array $wheres
     * like：[
     *          ['id=1'],
     *          ['age','<>',[1,2,3]],
     *          ['name','like','abc'],
     *          'id'=>[1,2,3],
     *          'id'=>123,
     *          ['age','between',[1,2]],
     *          ['age','not between',[1,2]],
     *          ['age','find_in_set',[1,2]]
     *          ...
     *      ]
     * @return $this
     */
    final public function where($wheres = [])
    {
        $this->selectOrder['where'] = $this->words['where'] . '( ' . $this->buildWhere($wheres) . ' )';
        return $this;
    }

    /**
     * and where子句（必须在where后使用，用法同where）
     * @param array $wheres
     * @return $this
     */
    final public function andWhere($wheres = [])
    {
        if ($this->selectOrder['where']) {
            $this->selectOrder['where'] .= $this->words['and'] . '( ' . $this->buildWhere($wheres) . ' )';
        }
        return $this;
    }

    /**
     * or where子句（必须在where后使用，用法同where）
     * @param array $wheres
     * @return $this
     */
    final public function orWhere($wheres = [])
    {
        if ($this->selectOrder['where']) {
            $this->selectOrder['where'] .= $this->words['or'] . '( ' . $this->buildWhere($wheres) . ' )';
        }
        return $this;
    }

    /**
     * 构建where子句
     * @param array $wheres
     * @return string
     */
    final private function buildWhere($wheres = [])
    {
        $sqlArr = [];
        if ($wheres) {
            foreach ($wheres as $field => $where) {
                $sql = '';
                if (is_numeric($field) && is_array($where) && count($where) == 3 && is_array($where[2])) {
                    $key = strtolower($where[1]);
                    $where[0] = '`' . $where[0] . '`';
                    $sql = $where[0] . $where[1] . "'" . implode("'" . $this->words['and'] . $where[0] . $where[1] . "'", $where[2]) . "'";
                    if ($key == 'like') {
                        $sql = $where[0] . $this->words['like'] . "'" . implode("'" . $this->words['and'] . $where[0] . $this->words['like'] . "'", $where[2]) . "'";
                    }
                    if ($key == 'in') {
                        $sql = $where[0] . $this->words['in'] . "( '" . implode("','", $where[2]) . "' )";
                    }
                    if ($key == 'not in') {
                        $sql = $where[0] . $this->words['not'] . $this->words['in'] . "( '" . implode("','", $where[2]) . "' )";
                    }
                    if ($key == 'find_in_set') {
                        $sql = $this->words['find_in_set'] . "('" . implode(',', $where[2]) . "'," . $where[0] . ')';
                    }
                    if ($key == 'between' && count($where[2]) == 2) {
                        $sql = $where[0] . $this->words['between'] . "'" . $where[2][0] . "'" . $this->words['and'] . "'" . $where[2][1] . "'";
                    }
                    if ($key == 'not between' && count($where[2]) == 2) {
                        $sql = $where[0] . $this->words['not'] . $this->words['between'] . "'" . $where[2][0] . "'" . $this->words['and'] . "'" . $where[2][1] . "'";
                    }
                }
                if (is_numeric($field) && is_array($where) && count($where) == 3 && !is_array($where[2])) {
                    $key = strtolower($where[1]);
                    $where[0] = '`' . $where[0] . '`';
                    $sql = $where[0] . $where[1] . "'" . $where[2] . "'";
                    if ($key == 'like') {
                        $sql = $where[0] . $this->words['like'] . "'" . $where[2] . "'";
                    }
                    if ($key == 'in') {
                        $sql = $where[0] . $this->words['in'] . "( '" . $where[2] . "' )";
                    }
                    if ($key == 'not in') {
                        $sql = $where[0] . $this->words['not'] . $this->words['in'] . "( '" . $where[2] . "' )";
                    }
                    if ($key == 'find_in_set') {
                        $sql = $this->words['find_in_set'] . "('" . $where[2] . "'," . $where[0] . ')';
                    }
                }

                if (is_numeric($field) && !is_array($where)) {
                    $sql = $where;
                }
                if (!is_numeric($field) && is_array($where)) {
                    $field = '`' . $field . '`';
                    $sql = $field . $this->words['in'] . "( '" . implode("','", $where) . "' )";
                }
                if (!is_numeric($field) && !is_array($where)) {
                    $field = '`' . $field . '`';
                    $sql = $field . "='" . $where . "'";
                }
                if ($sql) {
                    array_push($sqlArr, $sql);
                }
            }
            return implode($this->words['and'], $sqlArr);
        }
        return '';
    }

    /**
     * 排序
     * @param array $orders like：['id'=>'DESC'] or ['id'=>'DESC','age'=>'ASC',...]
     * @return $this
     */
    final public function orderBy($orders = ['id' => 'DESC'])
    {
        foreach ($orders as $field => $order) {
            $field = '`' . $field . '`';
            if ($this->selectOrder['order']) {
                $this->selectOrder['order'] .= ',' . $field . ' ' . $order;
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
    final public function groupBy($group = ['id'])
    {
        foreach ($group as $v) {
            $v = '`' . $v . '`';
            if ($this->selectOrder['group']) {
                $this->selectOrder['group'] .= ',' . $v;
            } else {
                $this->selectOrder['group'] = $this->words['group'] . $v;
            }
        }
        return $this;
    }

    /**
     * 限定输出（可单独使用，亦可配合offset使用）
     * @param int $limit
     * @return $this
     */
    final public function limit($limit = 0)
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
    final public function offset($offset = 0)
    {
        if ($offset) {
            $this->selectOrder['offset'] = $this->words['offset'] . $offset;
        }
        return $this;
    }

    /**
     * 左 可多次调用实现多表联查
     * @param string $tableName
     * @param string $on
     * @return $this
     */
    final public function leftJoin($tableName = '', $on = '')
    {
        $sql = '';
        if ($tableName && $on) {
            if (is_array($tableName)) {
                if (count($tableName) == 1) {
                    foreach ($tableName as $k => $v) {
                        $sql = $this->words['leftJoin'] . $k . $this->words['as'] . $v . $this->words['on'];
                    }
                }
            } else {
                $sql = $this->words['leftJoin'] . $tableName . $this->words['on'];
            }
            if (is_array($on)) {
                foreach ($on as $key => &$value) {
                    $value = $key . '=' . $value;
                }
                $on = implode($this->words['and'], $on);
            }
        }
        $this->selectOrder['leftJoin'] .= $sql . $on;
        return $this;
    }

    /**
     * 右  可多次调用实现多表联查
     * @param string $tableName
     * @param string $on
     * @return $this
     */
    final public function rightJoin($tableName = '', $on = '')
    {
        $sql = '';
        if ($tableName && $on) {
            if (is_array($tableName)) {
                if (count($tableName) == 1) {
                    foreach ($tableName as $k => $v) {
                        $sql = $this->words['rightJoin'] . $k . $this->words['as'] . $v . $this->words['on'];
                    }
                }
            } else {
                $sql = $this->words['rightJoin'] . $tableName . $this->words['on'];
            }
            if (is_array($on)) {
                foreach ($on as $key => &$value) {
                    $value = $key . '=' . $value;
                }
                $on = implode($this->words['and'], $on);
            }
        }
        $this->selectOrder['rightJoin'] .= $sql . $on;
        return $this;
    }

    /**
     * 内  可多次调用实现多表联查
     * @param string $tableName
     * @param string $on
     * @return $this
     */
    final public function innerJoin($tableName = '', $on = '')
    {
        $sql = '';
        if ($tableName && $on) {
            if (is_array($tableName)) {
                if (count($tableName) == 1) {
                    foreach ($tableName as $k => $v) {
                        $sql = $this->words['innerJoin'] . $k . $this->words['as'] . $v . $this->words['on'];
                    }
                }
            } else {
                $sql = $this->words['innerJoin'] . $tableName . $this->words['on'];
            }
            if (is_array($on)) {
                foreach ($on as $key => &$value) {
                    $value = $key . '=' . $value;
                }
                $on = implode($this->words['and'], $on);
            }
        }
        $this->selectOrder['innerJoin'] .= $sql . $on;
        return $this;
    }

    /**
     * 外  可多次调用实现多表联查
     * @param string $tableName
     * @param string $on
     * @return $this
     */
    final public function fullJoin($tableName = '', $on = '')
    {
        $sql = '';
        if ($tableName && $on) {
            if (is_array($tableName)) {
                if (count($tableName) == 1) {
                    foreach ($tableName as $k => $v) {
                        $sql = $this->words['fullJoin'] . $k . $this->words['as'] . $v . $this->words['on'];
                    }
                }
            } else {
                $sql = $this->words['fullJoin'] . $tableName . $this->words['on'];
            }
            if (is_array($on)) {
                foreach ($on as $key => &$value) {
                    $value = $key . '=' . $value;
                }
                $on = implode($this->words['and'], $on);
            }
        }
        $this->selectOrder['fullJoin'] .= $sql . $on;
        return $this;
    }

    /**
     * 交叉  可多次调用实现多表联查
     * @param string $tableName
     * @return $this
     */
    final public function crossJoin($tableName = '')
    {
        if ($tableName) {
            if (is_array($tableName)) {
                if (count($tableName) == 1) {
                    foreach ($tableName as $k => $v) {
                        $this->selectOrder['crossJoin'] .= $this->words['crossJoin'] . $k . $this->words['as'] . $v;
                    }
                }
            } else {
                $this->selectOrder['crossJoin'] .= $this->words['crossJoin'] . $tableName;
            }
        }
        return $this;
    }

    /**
     * 查找当前一条数据对象
     * @return DataObj
     */
    final public function one()
    {
        $re = self::PDO()->select($this->buildSelect(), false);
        $re = new DataObj($re, $this);
        return $re;
    }

    /**
     * 查找当前所有数据对象
     * @return DataObj
     */
    final public function all()
    {
        $res = self::PDO()->select($this->buildSelect(), true);
        $res = new DataObj($res, $this);
        return $res;
    }

    /**
     * 返回当前查找数据条数
     * @return mixed
     */
    final public function count()
    {
        $this->selectOrder['select'] = $this->words['select'] . ' COUNT(*)' . $this->words['as'] . 'NUM';
        $re = self::PDO()->select($this->buildSelect(), false);
        return (int)$re->NUM;
    }

    /**
     * 拼凑查询语句
     * @return array|string
     */
    final private function buildSelect()
    {
        $sql = $this->selectOrder;
        if (is_array($sql)) {
            $sql['from'] = $sql['from'] ? $sql['from'] : $this->words['from'] . '`' . self::tableName() . '`';
            $sql = implode('', $sql);
        }
        return $sql;
    }

    //todo**************************数据操作******************************

    /**
     * 插入一条数据
     * @param array $data
     * @param string $table
     * @return bool
     */
    final public static function insert($data = [], $table = '')
    {
        $table = $table ? $table : self::tableName();
        if ($table && $data) {
            $fields = [];
            $values = [];
            foreach ($data as $k => $v) {
                array_push($fields, $k);
                array_push($values, $v);
            }
            $fields = ' (`' . implode('`,`', $fields) . '`)';
            $values = "('" . implode("','", $data) . "')";
            $sql = self::$sqlModel->words['insert'] . '`' . $table . '`' . $fields . self::$sqlModel->words['values'] . $values;
            return self::PDO()->execSql($sql, $table, $data);
        }
        Error::showError('参数错误');
        return false;
    }

    /**
     * 更新符合条件的数据
     * @param array $where
     * @param array $data
     * @param string $table
     * @return bool
     */
    final public static function update($where = [], $data = [], $table = '')
    {
        $table = $table ? $table : self::tableName();
        if ($where && $table && $data) {
            foreach ($data as $k => &$v) {
                $v = '`' . $k . "`='" . $v . "'";
            }
            $value = implode(',', $data);
            $sql = self::$sqlModel->words['update'] . '`' . $table . '`' . self::$sqlModel->words['set'] . $value . self::$sqlModel->words['where'] . '( ' . self::$sqlModel->buildWhere($where) . ' )';
            return self::PDO()->execSql($sql, $table, $data);
        }
        Error::showError('参数错误');
        return false;
    }

    /**
     * 更新所有的数据
     * @param array $data
     * @param string $table
     * @return bool
     */
    final public static function updateALL($data = [], $table = '')
    {
        $table = $table ? $table : self::tableName();
        if ($table && $data) {
            foreach ($data as $k => &$v) {
                $v = '`' . $k . "`='" . $v . "'";
            }
            $value = implode(',', $data);
            $sql = self::$sqlModel->words['update'] . '`' . $table . '`' . self::$sqlModel->words['set'] . $value;
            return self::PDO()->execSql($sql, $table, $data);
        }
        Error::showError('参数错误');
        return false;
    }

    /**
     * 根据唯一主键自动判断是插入还是更新
     * @param array $data
     * @param string $table
     * @return bool
     */
    final public static function replace($data = [], $table = '')
    {
        $table = $table ? $table : self::tableName();
        if ($table && $data) {
            $fields = [];
            $values = [];
            foreach ($data as $k => $v) {
                array_push($fields, $k);
                array_push($values, $v);
            }
            $fields = ' (`' . implode('`,`', $fields) . '`)';
            $values = "('" . implode("','", $data) . "')";
            $sql = self::$sqlModel->words['replace'] . '`' . $table . '`' . $fields . self::$sqlModel->words['values'] . $values;
            return self::PDO()->execSql($sql, $table, $data);
        }
        Error::showError('参数错误');
        return false;
    }

    /**
     * 删除符合条件数据
     * @param array $where
     * @return bool
     */
    final public static function del($where = [])
    {
        if ($where) {
            $sql = self::$sqlModel->words['delete'] . '`' . $table . '`' . self::$sqlModel->words['where'] . '( ' . self::$sqlModel->buildWhere($where) . ' )';
            return self::PDO()->execSql($sql);
        }
        Error::showError('参数错误');
        return false;
    }

    //todo**************************PDO******************************

    /**
     * 创建数据库连接
     * @return null|\PDO
     */
    final private static function PDO()
    {
        $DBName = self::find()->DBName;
        return MyPDO::getPdo($DBName);
    }

    /**
     * 获取主键和自增字段
     * @return mixed
     */
    private function getKey(){
        return self::PDO()->getKey(self::tableName());
    }


}
<?php
/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/9
 * Time: 18:08
 */

namespace wFrame\app;

class RedisCache
{

    /**
     * 单例划redis类
     * @param array $config
     * @return \Redis|RedisCache
     */
    public static function redis($config = ['host' => '127.0.0.1', 'port' => 6379, 'password' => ''])
    {
        if (!self::$redis) {
            self::$redis = new self($config);
        }
        return self::$redis;
    }

    /**
     * redis自身对象
     * @var \Redis
     */
    private static $redis;

    /**
     * redis实例化时静态变量
     * @var \Redis
     */
    private $redis_obj;

    /**
     * RedisCache constructor.
     * @param array $config
     */
    private function __construct($config = [])
    {
        $redis = new \Redis();
        if(!$redis->connect($config['host'], $config['port'])){
            Error::addError('连接Redis服务器出错');
        }
        $redis->auth($config['password']);
        $this->redis_obj = $redis;
    }

    /*------------------------------------start 1.string结构----------------------------------------------------*/
    /**
     * 增，设置值  构建一个字符串
     * @param string $key KEY名称
     * @param string $value 设置值
     * @param int $timeOut 时间  0表示无过期时间
     * @return bool true【总是返回true】
     */
    public function set($key, $value, $timeOut = 0)
    {
        $setRes = $this->redis_obj->set($key, $value);
        if ($timeOut > 0) $this->redis_obj->expire($key, $timeOut);
        return $setRes;
    }

    /**
     * 查，获取 某键对应的值，不存在返回false
     * @param $key ,键值
     * @return bool|string ，查询成功返回信息，失败返回false
     */
    public function get($key)
    {
        $setRes = $this->redis_obj->get($key);//不存在返回false
        if ($setRes === 'false') {
            return false;
        }
        return $setRes;
    }
    /*------------------------------------1.end string结构----------------------------------------------------*/


    /*------------------------------------2.start list结构----------------------------------------------------*/
    /**
     * 增，构建一个列表(先进后去，类似栈)
     * @param String $key KEY名称
     * @param string $value 值
     * @param $timeOut |num  过期时间
     */
    public function lpush($key, $value, $timeOut = 0)
    {
        $re = $this->redis_obj->LPUSH($key, $value);
        if ($timeOut > 0) $this->redis_obj->expire($key, $timeOut);
        return $re;
    }

    /**
     * 增，构建一个列表(先进先去，类似队列)
     * @param string $key KEY名称
     * @param string $value 值
     * @param $timeOut |num  过期时间
     */
    public function rpush($key, $value, $timeOut = 0)
    {
//          echo "$key - $value \n";
        $re = $this->redis_obj->RPUSH($key, $value);
        if ($timeOut > 0) $this->redis_obj->expire($key, $timeOut);
        return $re;
    }

    /**
     * 查，获取所有列表数据（从头到尾取）
     * @param string $key KEY名称
     * @param int $head 开始
     * @param int $tail 结束
     */
    public function lranges($key, $head, $tail)
    {
        return $this->redis_obj->lrange($key, $head, $tail);
    }

    /*------------------------------------2.end list结构----------------------------------------------------*/


    /*------------------------------------3.start set结构----------------------------------------------------*/

    /**
     * 增，构建一个集合(无序集合)
     * @param string $key 集合Y名称
     * @param string|array $value 值
     * @param int $timeOut 时间  0表示无过期时间
     * @return
     */
    public function sadd($key, $value, $timeOut = 0)
    {
        $re = $this->redis_obj->sadd($key, $value);
        if ($timeOut > 0) $this->redis_obj->expire($key, $timeOut);
        return $re;
    }

    /**
     * 查，取集合对应元素
     * @param $key
     * @return bool
     */
    public function smembers($key)
    {
        $re = $this->redis_obj->exists($key);//存在返回1，不存在返回0
        if (!$re) return false;
        return $this->redis_obj->smembers($key);
    }

    /*------------------------------------3.end  set结构----------------------------------------------------*/


    /*------------------------------------4.start sort set结构----------------------------------------------------*/
    /**
     * 增，改，构建一个集合(有序集合),支持批量写入,更新
     * @param $key
     * @param $score_value
     * @param int $timeOut
     * @return bool|int
     */
    public function zadd($key, $score_value, $timeOut = 0)
    {
        if (!is_array($score_value)) return false;
        $a = 0;//存放插入的数量
        foreach ($score_value as $score => $value) {
            $re = $this->redis_obj->zadd($key, $score, $value);//当修改时，可以修改，但不返回更新数量
            $re && $a += 1;
            if ($timeOut > 0) $this->redis_obj->expire($key, $timeOut);
        }
        return $a;
    }

    /**
     * 查，有序集合查询，可升序降序,默认从第一条开始，查询一条数据
     * @param $key
     * @param int $min
     * @param int $num
     * @param string $order
     * @return bool
     */
    public function zrange($key, $min = 0, $num = 1, $order = 'desc')
    {
        $re = $this->redis_obj->exists($key);//存在返回1，不存在返回0
        if (!$re) return false;//不存在键值
        if ('desc' == strtolower($order)) {
            $re = $this->redis_obj->zrevrange($key, $min, $min + $num - 1);
        } else {
            $re = $this->redis_obj->zrange($key, $min, $min + $num - 1);
        }
        if (!$re) return false;//查询的范围值为空
        return $re;
    }

    /**
     * 返回集合key中，成员member的排名
     * @param $key
     * @param $member
     * @param string $type
     * @return bool
     */
    public function zrank($key, $member, $type = 'desc')
    {
        $type = strtolower(trim($type));
        if ($type == 'desc') {
            $re = $this->redis_obj->zrevrank($key, $member);//其中有序集成员按score值递减(从大到小)顺序排列，返回其排位
        } else {
            $re = $this->redis_obj->zrank($key, $member);//其中有序集成员按score值递增(从小到大)顺序排列，返回其排位
        }
        if (!is_numeric($re)) return false;//不存在键值
        return $re;
    }

    /**
     * 返回名称为key的zset中score >= star且score <= end的所有元素
     * @param $key
     * @param $star
     * @param $end
     * @return mixed
     */
    public function zrangbyscore($key, $star, $end)
    {
        return $this->redis_obj->ZRANGEBYSCORE($key, $star, $end);
    }

    /**
     * 返回名称为key的zset中元素member的score
     * @param $key
     * @param $member
     * @return string ,返回查询的member值
     */
    function zscore($key, $member)
    {
        return $this->redis_obj->ZSCORE($key, $member);
    }
    /*------------------------------------4.end sort set结构----------------------------------------------------*/


    /*------------------------------------5.hash结构----------------------------------------------------*/
    /**
     * 增，以json格式插入数据到缓存,hash类型
     * @param $redis_key
     * @param $token
     * @param $id
     * @param $data
     * @param int $timeOut
     * @return mixed
     */
    public function hset_json($redis_key, $token, $id, $data, $timeOut = 0)
    {
        $redis_table_name = $redis_key['key'] . ':' . $token;           //key的名称
        $redis_key_name = $redis_key['field'] . ':' . $id;              //field的名称，表示第几个活动
        $redis_info = json_encode($data);                           //field的数据value，以json的形式存储
        $re = $this->redis_obj->hSet($redis_table_name, $redis_key_name, $redis_info);//存入缓存
        if ($timeOut > 0) $this->redis_obj->expire($redis_table_name, $timeOut);//设置过期时间
        return $re;
    }

    /**
     * 查，json形式存储的哈希缓存，有值则返回;无值则查询数据库并存入缓存
     * @param $redis_key
     * @param $token
     * @param $id
     * @return bool|mixed
     */
    public function hget_json($redis_key, $token, $id)
    {
        $re = $this->redis_obj->hexists($redis_key['key'] . ':' . $token, $redis_key['field'] . ':' . $id);//返回缓存中该hash类型的field是否存在
        if ($re) {
            $info = $this->redis_obj->hget($redis_key['key'] . ':' . $token, $redis_key['field'] . ':' . $id);
            $info = json_decode($info, true);
        } else {
            $info = false;
        }
        return $info;
    }

    /**
     * 增，普通逻辑的插入hash数据类型的值
     * @param $key ,键名
     * @param $data |array 一维数组，要存储的数据
     * @param $timeOut |num  过期时间
     * @return $number 返回OK【更新和插入操作都返回ok】
     */
    public function hmset($key, $data, $timeOut = 0)
    {
        $re = $this->redis_obj->hmset($key, $data);
        if ($timeOut > 0) $this->redis_obj->expire($key, $timeOut);
        return $re;
    }

    /**
     * 查，普通的获取值
     * @param $key ,表示该hash的下标值
     * @return array 。成功返回查询的数组信息，不存在信息返回false
     */
    public function hval($key)
    {
        $re = $this->redis_obj->exists($key);//存在返回1，不存在返回0
        if (!$re) return false;
        $vals = $this->redis_obj->hvals($key);
        $keys = $this->redis_obj->hkeys($key);
        $re = array_combine($keys, $vals);
        foreach ($re as $k => $v) {
            if (!is_null(json_decode($v))) {
                $re[$k] = json_decode($v, true);//true表示把json返回成数组
            }
        }
        return $re;
    }

    /**
     *
     * @param $key
     * @param $filed
     * @return bool|string
     */
    public function hget($key, $filed)
    {
        $re = $this->redis_obj->hget($key, $filed);
        if (!$re) {
            return false;
        }
        return $re;
    }

    /*------------------------------------end hash结构----------------------------------------------------*/


    /*------------------------------------其他结构----------------------------------------------------*/
    /**
     * 设置自增,自减功能
     * @param $key
     * @param int $num
     * @param string $member
     * @param string $type
     * @return bool
     */
    public function incre($key, $num = 1, $member = '', $type = '')
    {
        $num = intval($num);
        switch (strtolower(trim($type))) {
            case "zset":
                $re = $this->redis_obj->zIncrBy($key, $num, $member);//增长权值
                break;
            case "hash":
                $re = $this->redis_obj->hincrby($key, $member, $num);//增长hashmap里的值
                break;
            default:
                if ($num > 0) {
                    $re = $this->redis_obj->incrby($key, $num);//默认增长
                } else {
                    $re = $this->redis_obj->decrBy($key, -$num);//默认增长
                }
                break;
        }
        if ($re) return $re;
        return false;
    }


    /**
     * 清除缓存
     * @param int $type 默认为0，清除当前数据库；1表示清除所有缓存
     */
    function flush($type = 0)
    {
        if ($type) {
            $this->redis_obj->flushAll();//清除所有数据库
        } else {
            $this->redis_obj->flushdb();//清除当前数据库
        }
    }

    /**
     * 检验某个键值是否存在
     * @param $keys
     * @param string $type
     * @param string $field
     * @return mixed
     */
    public function exists($keys, $type = '', $field = '')
    {
        switch (strtolower(trim($type))) {
            case 'hash':
                $re = $this->redis_obj->hexists($keys, $field);//有返回1，无返回0
                break;
            default:
                $re = $this->redis_obj->exists($keys);
                break;
        }
        return $re;
    }

    /**
     * 删除缓存
     * @param $key
     * @param $type
     * @param string $field
     * @return mixed
     */
    public function delete($key, $type, $field = '')
    {
        switch (strtolower(trim($type))) {
            case 'hash':
                $re = $this->redis_obj->hDel($key, $field);//返回删除个数
                break;
            case 'set':
                $re = $this->redis_obj->sRem($key, $field);//返回删除个数
                break;
            case 'zset':
                $re = $this->redis_obj->zDelete($key, $field);//返回删除个数
                break;
            default:
                $re = $this->redis_obj->del($key);//返回删除个数
                break;
        }
        return $re;
    }

    /**
     * 日志记录
     * @param $log_content
     * @param string $position
     */
    public function logger($log_content, $position = 'user')
    {
        $max_size = 1000000;   //声明日志的最大尺寸1000K

        $log_dir = './log';//日志存放根目录

        if (!file_exists($log_dir)) mkdir($log_dir, 0777);//如果不存在该文件夹，创建

        if ($position == 'user') {
            $log_filename = "{$log_dir}/User_redis_log.txt";  //日志名称
        } else {
            $log_filename = "{$log_dir}/Wap_redis_log.txt";  //日志名称
        }

        //如果文件存在并且大于了规定的最大尺寸就删除了
        if (file_exists($log_filename) && (abs(filesize($log_filename)) > $max_size)) {
            unlink($log_filename);
        }

        //写入日志，内容前加上时间， 后面加上换行， 以追加的方式写入
        file_put_contents($log_filename, date('Y-m-d_H:i:s') . " " . $log_content . "\n", FILE_APPEND);
    }
}
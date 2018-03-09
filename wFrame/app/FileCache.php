<?php
/**
 * Created by PhpStorm.
 * User: 40466
 * Date: 2018/3/9
 * Time: 16:50
 */

namespace wFrame\app;
class FileCache
{
    /**
     * @var string 字符串的每一个缓存键前缀。这是你储存时所需要的。
     * 不同的应用程序以避免冲突同样在[[cachepath]]缓存数据。
     * 为了确保互操作性，只能使用字母数字字符。
     */
    public $keyPrefix = '';
    /**
     * @var string 存储缓存文件的目录。您可以在这里使用路径别名。
     * 如果没有设置，它将在应用程序运行时路径下使用“缓存”子目录。
     */
    public $cachePath = '';
    /**
     * @var string 缓存文件后缀。默认为“bin”。
     */
    public $cacheFileSuffix = '.bin';
    /**
     * @var integer 存储高速缓存文件的子目录的级别。默认值为0。
     * 如果系统有大量的缓存文件（如一百万），您可以使用更大的值（通常不大于3）。使用子目录主要是为了确保文件系统不会因为一个目录有太多文件而不堪重负。
     */
    public $directoryLevel = 0;
    /**
     * @var integer 当在缓存中存储一段数据时，应该执行垃圾收集（GC）的概率（百万分之一）。
     * 默认为10，意味着0.001%的几率。这个数字应该在0到1000000之间。值0意味着根本不会执行GC。
     */
    public $gcProbability = 10;
    /**
     * @var integer 为新创建的缓存文件设置权限。
     * 这个值可以通过PHP chmod()函数使用。没有umask将应用。
     * 如果未设置，则权限将由当前环境决定。
     */
    public $fileMode;
    /**
     * @var integer 将要为新创建的目录设置的权限整数。
     * 这个值可以通过PHP chmod()函数使用。没有umask将应用。
     * 默认值为0775，表示目录是由所有者和组读写的，但对其他用户只读。
     */
    public $dirMode = 0775;

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->cachePath = CONFIG['Cache']['cachePath'];
    }

    public function FileCache()
    {
        $this->__construct();
    }

    /**
     * 将密钥标识的值存储到缓存中。如果缓存中已经包含了这样的密钥，则现有的值和到期时间将分别替换为新的值和过期时间。
     * @param mixed $key 确定要缓存的值的键。这可以是一个简单的字符串，也可以是由表示键的因素组成的复杂数据结构。
     * @param mixed $value 要缓存的值。
     * @param integer $duration 缓存值将过期的秒数。0永不过期。
     * @return boolean 是否成功地将值存储到缓存中
     */
    public function set($key, $value, $duration = 0)
    {
        $value = serialize([$value]);
        $key = $this->buildKey($key);
        return $this->setValue($key, $value, $duration);
    }

    /**
     * 从具有指定键的高速缓存中检索一个值。
     * @param mixed $key 识别缓存值的键。这可以是一个简单的字符串或由表示键的因素组成的复杂数据结构。
     * @return mixed 如果缓存中的值未过期，则存储在缓存中的值为false，或者与缓存数据相关的值发生了变化。
     */
    public function get($key)
    {
        $key = $this->buildKey($key);
        $value = $this->getValue($key);
        if ($value === false) {
            return $value;
        } else {
            $value = unserialize($value);
        }
        if (is_array($value)) {
            return $value[0];
        } else {
            return false;
        }
    }

    /**
     * 从缓存中删除具有指定键的值。
     * @param mixed $key 标识要从缓存中删除的值的键。这可以是一个简单的字符串，也可以是由表示键的因素组成的复杂数据结构。
     * @return boolean 删除期间有没有错误发生
     */
    public function delete($key)
    {
        $key = $this->buildKey($key);

        return $this->deleteValue($key);
    }

    /**
     * 从给定的键构建规范化的缓存键。
     * 如果给定的key是一个字符串包含字母数字字符，不超过32个字符，然后重点将返回以[keyprefix]。
     * 否则，正常化的关键是生成的序列化给定的键，使用MD5哈希，和前缀与 [keyprefix]。
     * @param mixed $key 规范化的关键
     * @return string 生成缓存的关键
     */
    public function buildKey($key)
    {
        if (is_string($key)) {
            $key = ctype_alnum($key) && mb_strlen($key, '8bit') <= 32 ? $key : md5($key);
        } else {
            $key = md5(json_encode($key, true));
        }

        return $this->keyPrefix . $key;
    }

    /**
     * 存储由缓存中的密钥标识的值。
     * 这是在父类中声明的方法的实现。
     * @param string $key 确定要缓存的值的键。
     * @param string $value 要缓存的值。
     * @param integer $duration 缓存值将过期的秒数。0永不过期。
     * @return boolean 如果成功地将值存储到缓存中，则为true，否则为false。
     */
    protected function setValue($key, $value, $duration)
    {
        $this->gc();
        $cacheFile = $this->getCacheFile($key);
        if ($this->directoryLevel > 0) {
            @mkdir(dirname($cacheFile), $this->dirMode, true);
        }
        if (@file_put_contents($cacheFile, $value, LOCK_EX) !== false) {
            if ($this->fileMode !== null) {
                @chmod($cacheFile, $this->fileMode);
            }
            if ($duration <= 0) {
                $duration = 31536000; // 1 year
            }

            return @touch($cacheFile, $duration + time());
        } else {
            $error = error_get_last();
            Error::addError("Unable to write cache file '{$cacheFile}': {$error['message']}");
            return false;
        }
    }

    /**
     * 从具有指定键的高速缓存中检索一个值。
     * 这是在父类中声明的方法的实现。
     * @param string $key 标识缓存值的唯一键。
     * @return string|boolean 存储在缓存中的值，如果值不在缓存中或过期时，则为false。
     */
    protected function getValue($key)
    {
        $cacheFile = $this->getCacheFile($key);

        if (@filemtime($cacheFile) > time()) {
            $fp = @fopen($cacheFile, 'r');
            if ($fp !== false) {
                @flock($fp, LOCK_SH);
                $cacheValue = @stream_get_contents($fp);
                @flock($fp, LOCK_UN);
                @fclose($fp);
                return $cacheValue;
            }
        }

        return false;
    }

    /**
     * 从缓存中删除具有指定键的值，这是父类声明的方法的实现。
     * @param string $key 要删除的值的键。
     * @return boolean 如果删除期间没有错误发生
     */
    protected function deleteValue($key)
    {
        $cacheFile = $this->getCacheFile($key);

        return @unlink($cacheFile);
    }

    /**
     * 返回给定缓存密钥的缓存文件路径。
     * @param string $key 缓存键
     * @return string the 缓存文件路径
     */
    protected function getCacheFile($key)
    {
        if ($this->directoryLevel > 0) {
            $base = $this->cachePath;
            for ($i = 0; $i < $this->directoryLevel; ++$i) {
                if (($prefix = substr($key, $i + $i, 2)) !== false) {
                    $base .= DIRECTORY_SEPARATOR . $prefix;
                }
            }
            return $base . DIRECTORY_SEPARATOR . $key . $this->cacheFileSuffix;
        } else {
            return $this->cachePath . DIRECTORY_SEPARATOR . $key . $this->cacheFileSuffix;
        }
    }

    /**
     * 删除过期缓存文件。
     * @param boolean $force 是否执行垃圾回收
     * 默认为false，这意味着实际发生的概率删除指定的[[gcprobability]]
     * @param boolean $expiredOnly 是否只删除过期缓存文件。
     * 如果是错的，所有的缓存文件在[cachepath]将被删除。
     */
    public function gc($force = false, $expiredOnly = true)
    {
        if ($force || mt_rand(0, 1000000) < $this->gcProbability) {
            $this->gcRecursive($this->cachePath, $expiredOnly);
        }
    }

    /**
     * 递归删除目录下过期的缓存文件。
     * 这种方法主要用于gc()。
     * @param string $path 已删除过期缓存文件的目录
     * @param boolean $expiredOnly 是否只删除过期缓存文件。如果FALSE，所有文件下的$path将被删除。
     */
    protected function gcRecursive($path, $expiredOnly)
    {
        if (($handle = opendir($path)) !== false) {
            while (($file = readdir($handle)) !== false) {
                if ($file[0] === '.') {
                    continue;
                }
                $fullPath = $path . DIRECTORY_SEPARATOR . $file;
                if (is_dir($fullPath)) {
                    $this->gcRecursive($fullPath, $expiredOnly);
                    if (!$expiredOnly) {
                        if (!@rmdir($fullPath)) {
                            $error = error_get_last();
                            Error::addError("Unable to remove directory '{$fullPath}': {$error['message']}");
                        }
                    }
                } elseif (!$expiredOnly || $expiredOnly && @filemtime($fullPath) < time()) {
                    if (!@unlink($fullPath)) {
                        $error = error_get_last();
                        Error::addError("Unable to remove file '{$fullPath}': {$error['message']}");
                    }
                }
            }
            closedir($handle);
        }
    }
}
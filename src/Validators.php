<?php
/**
 * @date 2015.08.04
 * 验证器列表
 * @note 验证数据; 成功则返回预期的类型， 失败返回 false
 * @description INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV  几个输入数据常量中的值在请求时即固定下来了，
 * 后期通过类似 $_GET['test']='help'; 将不会存在 输入数据常量中(INPUT_GET 没有test项)。
 */

namespace Inhere\Validate;

use Inhere\Validate\Utils\Helper;

/**
 * Class Validators
 * @package Inhere\Validate
 */
class Validators
{
    /**
     * don't allow create instance.
     */
    private function __construct()
    {
    }

    /*******************************************************************************
     * Validators
     ******************************************************************************/

    /**
     * 判断值是否为空
     * 值符合下方任一条件时即为「空」
     * - 该值为 null.
     * - 该值为空字符串。
     * - 该值为空数组
     * - 该值为空对象 -- 空的 `可数` 对象
     * - 该值为没有路径的上传文件(这里不做判断)
     * @param mixed $val
     * @return bool
     */
    public static function isEmpty($val)
    {
        if (\is_string($val)) {
            $val = trim($val);

        } elseif (\is_object($val)) {
            $val = get_object_vars($val);
        }

        return $val === '' || $val === null || $val === [];
    }

    /*******************************************************************************
     * bool/int/float/string validators
     ******************************************************************************/

    /**
     * 布尔值验证，转换成字符串后是下列的一个，就认为他是个bool值
     *   - "1"、"true"、"on" 和 "yes" (equal TRUE)
     *   - "0"、"false"、"off"、"no" 和 ""(equal FALSE)
     * 注意： NULL 不是标量类型
     * @param  mixed $val 要验证的数据
     * @return bool
     */
    public static function boolean($val)
    {
        if (!is_scalar($val)) {
            return false;
        }

        // $ret = filter_var($val, FILTER_VALIDATE_BOOLEAN, $settings);
        $val = (string)$val;

        if ($val === '') {
            return true;
        }

        return false !== stripos(Helper::IS_BOOL, '|' . $val . '|');
    }

    /**
     * @see Validators::boolean()
     * {@inheritdoc}
     */
    public static function bool($val)
    {
        return self::boolean($val);
    }

    /**
     * @param  mixed $val 要验证的变量
     * @param  null|integer|float $min 最小值
     * @param  null|int|float $max 最大值
     * $options = [
     *      'default' => 'default value',
     *      'decimal' => 2
     *  ]
     * @param  int $flags FILTER_FLAG_ALLOW_THOUSAND
     * @return mixed
     */
    public static function float($val, $min = null, $max = null, $flags = 0)
    {
        $settings = [];

        if ($flags !== 0) {
            $settings['flags'] = $flags;
        }

        if (filter_var($val, FILTER_VALIDATE_FLOAT, $settings) === false) {
            return false;
        }

        $minIsNum = is_numeric($min);
        $maxIsNum = is_numeric($max);

        if ($minIsNum && $maxIsNum) {
            if ($max > $min) {
                $minV = $min;
                $maxV = $max;
            } else {
                $minV = $max;
                $maxV = $min;
            }

            return $val >= $minV && $val <= $maxV;
        }

        if ($minIsNum) {
            return $val >= $min;
        }

        if ($maxIsNum) {
            return $val <= $max;
        }

        return true;
    }

    /**
     * int 验证 (所有的最小、最大都是包含边界值的)
     * @param  mixed $val 要验证的变量
     * @param  null|integer $min 最小值
     * @param  null|int $max 最大值
     * @param  int $flags 标志
     *                    FILTER_FLAG_ALLOW_OCTAL - 允许八进制数值
     *                    FILTER_FLAG_ALLOW_HEX - 允许十六进制数值
     * @return bool false
     * @example
     * $options = [
     *    'min_range' => 0,
     *    'max_range' => 256 // 添加范围限定
     *    // 'default' => 3, // value to return if the filter fails
     * ]
     */
    public static function integer($val, $min = null, $max = null, $flags = 0)
    {
        if (!is_numeric($val)) {
            return false;
        }

        $options = $settings = [];
        $minIsNum = is_numeric($min);
        $maxIsNum = is_numeric($max);

        if ($minIsNum && $maxIsNum) {
            if ($max > $min) {
                $options['min_range'] = (int)$min;
                $options['max_range'] = (int)$max;
            } else {
                $options['min_range'] = (int)$max;
                $options['max_range'] = (int)$min;
            }
        } elseif ($minIsNum) {
            $options['min_range'] = (int)$min;
        } elseif ($maxIsNum) {
            $options['max_range'] = (int)$max;
        }

        if ($options) {
            $settings['options'] = $options;
        }

        if ($flags !== 0) {
            $settings['flags'] = $flags;
        }

        return filter_var($val, FILTER_VALIDATE_INT, $settings) !== false;
    }

    /**
     * @see Validators::integer()
     * {@inheritdoc}
     */
    public static function int($val, $min = null, $max = null, $flags = 0)
    {
        return self::integer($val, $min, $max, $flags);
    }

    /**
     * check var is a integer and greater than 0
     * @param mixed $val
     * @param  null|integer $min 最小值
     * @param  null|int $max 最大值
     * @param int $flags
     * @return bool
     */
    public static function number($val, $min = null, $max = null, $flags = 0)
    {
        if (!is_numeric($val)) {
            return false;
        }

        if ($val <= 0) {
            return false;
        }

        return self::integer($val, $min, $max, $flags);
    }

    /**
     * @see Validators::number()
     * {@inheritdoc}
     */
    public static function num($val, $min = null, $max = null, $flags = 0)
    {
        return self::number($val, $min, $max, $flags);
    }

    /**
     * check val is a string
     * @param mixed $val
     * @param int $minLen
     * @param null|int $maxLen
     * @return bool
     */
    public static function string($val, $minLen = 0, $maxLen = null)
    {
        if (!\is_string($val)) {
            return false;
        }

        // only type check.
        if ($minLen === 0 && $maxLen === null) {
            return true;
        }

        return self::integer(Helper::strlen($val), $minLen, $maxLen);
    }

    /**
     * 验证的字段必须为 yes、 on、 1、true。这在确认「服务条款」是否同意时相当有用。
     * @from laravel
     * @param mixed $val
     * @return bool
     */
    public static function accepted($val)
    {
        if (!is_scalar($val)) {
            return false;
        }

        return false !== stripos(Helper::IS_TRUE, (string)$val);
    }

    /**
     * 验证字段值是否仅包含字母字符
     * @param  string $val
     * @return bool
     */
    public static function alpha($val)
    {
        return \is_string($val) && preg_match('/^[a-zA-Z]+$/', $val);
    }

    /**
     * 验证字段值是否仅包含字母、数字
     * @param  string $val
     * @return bool
     */
    public static function alphaNum($val)
    {
        if (!\is_string($val) && !is_numeric($val)) {
            return false;
        }

        return 1 === preg_match('/^[a-zA-Z0-9]+$/', $val);
    }

    /**
     * 验证字段值是否仅包含字母、数字、破折号（ - ）以及下划线（ _ ）
     * @param  string $val
     * @return bool
     */
    public static function alphaDash($val)
    {
        if (!\is_string($val) && !is_numeric($val)) {
            return false;
        }

        return 1 === preg_match('/^[\w-]+$/', $val);
    }

    /*******************************************************************************
     * size/range/length validators
     ******************************************************************************/

    /**
     * 范围检查
     * $min $max 即使传错位置也会自动调整
     * @param  int|string|array $val 待检测的值。 数字检查数字范围； 字符串、数组则检查长度
     * @param  null|integer $min 最小值
     * @param  null|int $max 最大值
     * @return mixed
     */
    public static function size($val, $min = null, $max = null)
    {
        if (!\is_int($val)) {
            if (\is_string($val)) {
                $val = Helper::strlen(trim($val));
            } elseif (\is_array($val)) {
                $val = \count($val);
            } else {
                return false;
            }
        }

        return self::integer($val, $min, $max);
    }

    /**
     * @see Validators::size()
     * {@inheritdoc}
     */
    public static function between($val, $min = null, $max = null)
    {
        return self::size($val, $min, $max);
    }

    /**
     * @see Validators::size()
     * {@inheritdoc}
     */
    public static function range($val, $min = null, $max = null)
    {
        return self::size($val, $min, $max);
    }

    /**
     * 最小值检查
     * @param  int|string|array $val
     * @param  integer $minRange
     * @return bool
     */
    public static function min($val, $minRange)
    {
        return self::size($val, (int)$minRange);
    }

    /**
     * 最大值检查
     * @param  int|string|array $val
     * @param  int $maxRange
     * @return bool
     */
    public static function max($val, $maxRange)
    {
        return self::size($val, null, (int)$maxRange);
    }

    /**
     * 字符串/数组长度检查
     * @param  string|array $val 字符串/数组
     * @param  integer $minLen 最小长度
     * @param  int $maxLen 最大长度
     * @return bool
     */
    public static function length($val, $minLen = 0, $maxLen = null)
    {
        if (!\is_string($val) && !\is_array($val)) {
            return false;
        }

        return self::size($val, $minLen, $maxLen);
    }

    /**
     * 固定的长度
     * @param mixed $val
     * @param int $size
     * @return bool
     */
    public static function fixedSize($val, $size)
    {
        if (!\is_int($val)) {
            if (\is_string($val)) {
                $val = Helper::strlen(trim($val));
            } elseif (\is_array($val)) {
                $val = \count($val);
            } else {
                return false;
            }
        }

        return $val === (int)$size;
    }

    /**
     * @param mixed $val
     * @param int $size
     * @return bool
     */
    public static function lengthEq($val, $size)
    {
        return self::fixedSize($val, $size);
    }

    /**
     * @param mixed $val
     * @param int $size
     * @return bool
     */
    public static function sizeEq($val, $size)
    {
        return self::fixedSize($val, $size);
    }

    /*******************************************************************************
     * extra string validators
     ******************************************************************************/

    /**
     * 值是否包含给的数据
     * @param string|mixed $val
     * @param string|array $needle
     * @return bool
     */
    public static function contains($val, $needle)
    {
        if (!$val || !\is_string($val)) {
            return false;
        }

        if (\is_string($needle)) {
            return stripos($val, $needle) !== false;
        }

        if (\is_array($needle)) {
            foreach ((array)$needle as $item) {
                if (stripos($val, $item) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 用正则验证数据
     * @param  string $val 要验证的数据
     * @param  string $regexp 正则表达式 "/^M(.*)/"
     * @param null $default
     * @return bool
     */
    public static function regexp($val, $regexp, $default = null)
    {
        $options = [
            'regexp' => $regexp
        ];

        if ($default !== null) {
            $options['default'] = $default;
        }

        return (bool)filter_var($val, FILTER_VALIDATE_REGEXP, ['options' => $options]);
    }

    /**
     * alias of the 'regexp()'
     * @param string $val
     * @param string $regexp
     * @param null $default
     * @return bool
     */
    public static function regex($val, $regexp, $default = null)
    {
        return self::regexp($val, $regexp, $default);
    }

    /**
     * url地址验证
     * @param  string $val 要验证的数据
     * @param  mixed $default 设置验证失败时返回默认值
     * @param  int $flags 标志
     *                    FILTER_FLAG_SCHEME_REQUIRED - 要求 URL 是 RFC 兼容 URL（比如 http://example）
     *                    FILTER_FLAG_HOST_REQUIRED - 要求 URL 包含主机名（比如 http://www.example.com）
     *                    FILTER_FLAG_PATH_REQUIRED - 要求 URL 在域名后存在路径（比如 www.example.com/example1/test2/）
     *                    FILTER_FLAG_QUERY_REQUIRED - 要求 URL 存在查询字符串（比如 "example.php?name=Peter&age=37"）
     * @return bool
     */
    public static function url($val, $default = null, $flags = 0)
    {
        $settings = [];

        if ($default !== null) {
            $settings['options']['default'] = $default;
        }

        if ($flags !== 0) {
            $settings['flags'] = $flags;
        }

        return (bool)filter_var($val, FILTER_VALIDATE_URL, $settings);
    }

    /**
     * email 地址验证
     * @param  string $val 要验证的数据
     * @param  mixed $default 设置验证失败时返回默认值
     * @return bool
     */
    public static function email($val, $default = null)
    {
        $options = [];

        if ($default !== null) {
            $options['default'] = $default;
        }

        return (bool)filter_var($val, FILTER_VALIDATE_EMAIL, ['options' => $options]);
    }

    /**
     * IP 地址验证
     * @param  string $val 要验证的数据
     * @param  mixed $default 设置验证失败时返回默认值
     * @param  int $flags 标志
     *                    FILTER_FLAG_IPV4 - 要求值是合法的 IPv4 IP（比如 255.255.255.255）
     *                    FILTER_FLAG_IPV6 - 要求值是合法的 IPv6 IP（比如 2001:0db8:85a3:08d3:1319:8a2e:0370:7334）
     *                    FILTER_FLAG_NO_PRIV_RANGE - 要求值不在 RFC 指定的私有范围 IP 内（比如 192.168.0.1）
     *                    FILTER_FLAG_NO_RES_RANGE - 要求值不在保留的 IP 范围内。该标志接受 IPV4 和 IPV6 值
     * @return bool
     */
    public static function ip($val, $default = null, $flags = 0)
    {
        $settings = [];

        if ($default !== null) {
            $settings['options']['default'] = $default;
        }

        if ($flags !== 0) {
            $settings['flags'] = $flags;
        }

        return (bool)filter_var($val, FILTER_VALIDATE_IP, $settings);
    }

    /**
     * IPv4 地址验证
     * @param  string $val 要验证的数据
     * @return bool
     */
    public static function ipv4($val)
    {
        return self::ip($val, false, FILTER_FLAG_IPV4);
    }

    /**
     * IPv6 地址验证
     * @param  string $val 要验证的数据
     * @return bool
     */
    public static function ipv6($val)
    {
        return self::ip($val, false, FILTER_FLAG_IPV6);
    }

    /**
     * mac Address
     * @param string $input
     * @return bool
     */
    public static function macAddress($input)
    {
        return !empty($input) && preg_match('/^(([0-9a-fA-F]{2}-){5}|([0-9a-fA-F]{2}:){5})[0-9a-fA-F]{2}$/', $input);
    }

    /**
     * english chars string
     * @param  string $val
     * @return bool
     */
    public static function english($val)
    {
        if (!$val || !\is_string($val)) {
            return false;
        }

        return preg_match('/^[A-Za-z]+$/', $val) === 1;
    }

    /**
     * 验证字段值是否是一个有效的 JSON 字符串。
     * @param mixed $val
     * @param bool $strict
     * @return bool
     */
    public static function json($val, $strict = true)
    {
        if (!$val || (!\is_string($val) && !method_exists($val, '__toString'))) {
            return false;
        }

        $val = (string)$val;

        // must start with: { OR [
        if ($strict && '[' !== $val[0] && '{' !== $val[0]) {
            return false;
        }

        json_decode($val);

        return json_last_error() === JSON_ERROR_NONE;
    }

    /*******************************************************************************
     * array(list/map/enum) validators
     ******************************************************************************/

    /**
     * 验证值是否是一个数组
     * @param  mixed $val
     * @return bool
     */
    public static function isArray($val)
    {
        return \is_array($val);
    }

    /**
     * 验证值是否是一个非自然数组 map (key不是自然增长的 OR key - value 形式的)
     * @param  mixed $val
     * @return bool
     */
    public static function isMap($val)
    {
        if (!\is_array($val)) {
            return false;
        }

        /** @var array $val */
        $keys = array_keys($val);

        return array_keys($keys) !== $keys;
    }

    /**
     * 验证值是否是一个自然数组 list (key是从0自然增长的)
     * @param array|mixed $val
     * @return bool
     */
    public static function isList($val)
    {
        if (!\is_array($val) || !isset($val[0])) {
            return false;
        }

        /** @var array $val */
        $keys = array_keys($val);

        return array_keys($keys) === $keys;
    }

    /**
     * 验证字段值是否是一个 int list(key是从0自然增长的, val是数字)
     * @param array|mixed $val
     * @return bool
     */
    public static function intList($val)
    {
        if (!\is_array($val) || !isset($val[0])) {
            return false;
        }

        $lastK = -1;

        /** @var array $val */
        foreach ($val as $k => $v) {
            if (!\is_int($k) || $k !== $lastK + 1) {
                return false;
            }

            if (!is_numeric($v)) {
                return false;
            }

            $lastK = $k;
        }

        return true;
    }

    /**
     * 验证字段值是否是一个 number list(key是从0自然增长的, val是大于0的数字)
     * @param array|mixed $val
     * @return bool
     */
    public static function numList($val)
    {
        if (!\is_array($val) || !isset($val[0])) {
            return false;
        }

        $lastK = -1;

        /** @var array $val */
        foreach ($val as $k => $v) {
            if (!\is_int($k) || $k !== $lastK + 1) {
                return false;
            }

            if (!is_numeric($v) || $v <= 0) {
                return false;
            }

            $lastK = $k;
        }

        return true;
    }

    /**
     * 验证字段值是否是一个 string list(key是从0自然增长的, val是 string)
     * @param array|mixed $val
     * @return bool
     */
    public static function strList($val)
    {
        if (!$val || !\is_array($val)) {
            return false;
        }

        $lastK = -1;

        /** @var array $val */
        foreach ($val as $k => $v) {
            if (!\is_int($k) || $k !== $lastK + 1) {
                return false;
            }

            if (!\is_string($v)) {
                return false;
            }

            $lastK = $k;
        }

        return true;
    }

    /**
     * 验证字段值是否是一个 array list, 多维数组
     * @param $val
     * @return bool
     */
    public static function arrList($val)
    {
        if (!$val || !\is_array($val)) {
            return false;
        }

        /** @var array $val */
        foreach ($val as $k => $v) {
            if (!\is_array($v)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array|mixed $val
     * @param string|int|array $key
     * @return bool
     */
    public static function hasKey($val, $key)
    {
        if (!$val || !\is_array($val)) {
            return false;
        }

        if (\is_string($key) || \is_int($key)) {
            return array_key_exists($key, $val);
        }

        if (\is_array($key)) {
            $keys = array_keys($val);

            return !array_diff($key, $keys);
        }

        return false;
    }

    /**
     * 验证数组时，指定的字段不能有任何重复值。
     * `['foo.*.id', 'distinct']`
     * @param mixed $val
     * @return bool
     */
    public static function distinct($val)
    {
        if (!$val || !\is_array($val)) {
            return false;
        }

        return array_unique($val) === $val;
    }

    /**
     * @param mixed $val
     * @param array|string $dict
     * @param bool $strict Use strict check, will check data type.
     * @return bool
     */
    public static function in($val, $dict, $strict = false)
    {
        if (\is_string($dict)) {
            // $dict = array_map('trim', explode(',', $dict));
            return false !== ($strict ? strpos($dict, (string)$val) : stripos($dict, (string)$val));
        }

        return \in_array($val, (array)$dict, $strict);
    }

    /**
     * alias of 'in()'
     * @param  mixed $val
     * @param array|string $dict
     * @param bool $strict
     * @return bool
     */
    public static function enum($val, $dict, $strict = false)
    {
        return self::in($val, $dict, $strict);
    }

    /**
     * @param  mixed $val
     * @param array|string $dict
     * @param bool $strict
     * @return bool
     */
    public static function notIn($val, $dict, $strict = false)
    {
        if (\is_string($dict) && strpos($dict, ',')) {
            $dict = array_map('trim', explode(',', $dict));
        }

        return !\in_array($val, (array)$dict, $strict);
    }

    /*******************************************************************************
     * mixed data validators
     ******************************************************************************/

    /**
     * @param mixed $val
     * @param string $start
     * @param bool $strict
     * @return bool
     */
    public static function startWith($val, $start, $strict = true)
    {
        $start = (string)$start;

        if (\is_string($val)) {
            return ($strict ? strpos($val, $start) : stripos($val, $start)) === 0;
        }

        if (\is_array($val)) {
            $first = array_shift($val);

            return $strict ? $first === $start : $first == $start;
        }

        return false;
    }

    /**
     * @param mixed $val
     * @param string $end
     * @param bool $strict
     * @return bool
     */
    public static function endWith($val, $end, $strict = true)
    {
        $last = null;
        $end = (string)$end;

        if (\is_string($val)) {
            $last = substr($val, -Helper::strlen($end));
        }

        if (\is_array($val)) {
            $last = array_pop($val);
        }

        return $strict ? $last === $end : $last == $end;
    }

    /**
     * 必须是等于给定值
     * @param  mixed $val
     * @param  mixed $excepted
     * @param bool $strict
     * @return bool
     */
    public static function mustBe($val, $excepted, $strict = true)
    {
        return $strict ? $val === $excepted : $val == $excepted;
    }

    /**
     * 不能等于给定值
     * @param  mixed $val
     * @param  mixed $excepted
     * @param bool $strict
     * @return bool
     */
    public static function notBe($val, $excepted, $strict = true)
    {
        return $strict ? $val !== $excepted : $val != $excepted;
    }

    /*******************************************************************************
     * date validators
     ******************************************************************************/

    /**
     * 校验字段值是否是日期格式
     * @param string $val 日期
     * @return boolean
     */
    public static function date($val)
    {
        // strtotime 转换不对，日期格式显然不对。
        return strtotime($val) ? true : false;
    }

    /**
     * 校验字段值是否是等于给定日期
     * @param string $val
     * @param string $date 给定日期
     * @return boolean
     */
    public static function dateEquals($val, $date)
    {
        if (!$val || (!$time = strtotime($val))) {
            return false;
        }

        if (!$date) {
            return false;
        }

        return $time === strtotime($date);
    }

    /**
     * 校验字段值是否是日期并且是否满足设定格式
     * @param string $val 日期
     * @param string $format 需要检验的格式数组
     * @return boolean
     */
    public static function dateFormat($val, $format = 'Y-m-d')
    {
        if (!$val || !($unixTime = strtotime($val))) {
            return false;
        }

        // 校验日期的格式有效性
        return date($format, $unixTime) === $val;
    }

    /**
     * 字段值必须是给定日期之前的值
     * @param string $val
     * @param string $beforeDate 若为空，将使用当前时间
     * @param string $symbol allow '<' '<='
     * @return bool
     */
    public static function beforeDate($val, $beforeDate, $symbol = '<')
    {
        if (!$val || !\is_string($val)) {
            return false;
        }

        if (!$valueTime = strtotime($val)) {
            return false;
        }

        $beforeTime = $beforeDate ? strtotime($beforeDate) : time();

        if ($symbol === '>') {
            return $beforeTime < $valueTime;
        }

        return $beforeTime <= $valueTime;
    }

    /**
     * 字段值必须是小于或等于给定日期的值
     * @param string $val
     * @param string $beforeDate
     * @return bool
     */
    public static function beforeOrEqualDate($val, $beforeDate)
    {
        return self::beforeDate($val, $beforeDate, '<=');
    }

    /**
     * 字段值必须是给定日期之后的值
     * @param string $val
     * @param string $afterDate
     * @param string $symbol allow: '>' '>='
     * @return bool
     */
    public static function afterDate($val, $afterDate, $symbol = '>')
    {
        if (!$val || !\is_string($val)) {
            return false;
        }

        if (!$valueTime = strtotime($val)) {
            return false;
        }

        $afterTime = $afterDate ? strtotime($afterDate) : time();

        if ($symbol === '>') {
            return $afterTime > $valueTime;
        }

        return $afterTime >= $valueTime;
    }

    /**
     * 字段值必须是大于或等于给定日期的值
     * @param string $val
     * @param string $afterDate
     * @return bool
     */
    public static function afterOrEqualDate($val, $afterDate)
    {
        return self::afterDate($val, $afterDate, '>=');
    }

    /**
     * @todo
     * @param string $val
     * @param string $compareDate
     * @param int $expected
     * @param string $op
     */
    public static function diffDate($val, $compareDate, $expected, $op = '>=')
    {

    }

    /**
     * Check for date format
     * @param string $date Date to validate
     * @return bool Validity is ok or not
     */
    public static function isDateFormat($date)
    {
        return (bool)preg_match('/^([\d]{4})-((0?[\d])|(1[0-2]))-((0?[\d])|([1-2][\d])|(3[01]))( [\d]{2}:[\d]{2}:[\d]{2})?$/',
            $date);
    }

    /**
     * Check for date validity
     * @param string $date Date to validate
     * @return bool Validity is ok or not
     */
    public static function isDate($date)
    {
        if (!preg_match('/^([\d]{4})-((?:0?[\d])|(?:1[0-2]))-((?:0?[\d])|(?:[1-2][\d])|(?:3[01]))( [\d]{2}:[\d]{2}:[\d]{2})?$/',
            $date, $matches)) {
            return false;
        }

        return checkdate((int)$matches[2], (int)$matches[3], (int)$matches[1]);
    }

    /*******************************************************************************
     * extension validators
     ******************************************************************************/

    /**
     * @param $val
     * @return bool
     */
    public static function phone($val)
    {
        return 1 === preg_match('/^1[2-9]\d{9}$/', $val);
    }

    // public static function telNumber($val)
    // {}

    /**
     * Check for postal code validity
     * @param string $val Postal code to validate
     * @return bool Validity is ok or not
     */
    public static function postCode($val)
    {
        return empty($val) || preg_match('/^\d{6}$/', $val);
    }

    /**
     * Check for price validity
     * @param string $price Price to validate
     * @return bool Validity is ok or not
     */
    public static function price($price)
    {
        return 1 === preg_match('/^[\d]{1,10}(\.[\d]{1,9})?$/', $price);
    }

    /**
     * Check for price validity (including negative price)
     * @param string $price Price to validate
     * @return bool Validity is ok or not
     */
    public static function negativePrice($price)
    {
        return 1 === preg_match('/^[-]?[\d]{1,10}(\.[\d]{1,9})?$/', $price);
    }

    /**
     * Check for a float number validity
     * @param float $float Float number to validate
     * @return bool Validity is ok or not
     */
    public static function isFloat($float)
    {
        return (string)((float)$float) === (string)$float;
    }

    public static function isUnsignedFloat($float)
    {
        return (string)((float)$float) === (string)$float && $float >= 0;
    }

    /**
     * Check for an integer validity
     * @param int $value Integer to validate
     * @return bool Validity is ok or not
     */
    public static function isInt($value)
    {
        return ((string)(int)$value === (string)$value || $value === false);
    }

    /**
     * Check for an integer validity (unsigned)
     * @param int $value Integer to validate
     * @return bool Validity is ok or not
     */
    public static function isUnsignedInt($value)
    {
        return ((string)(int)$value === (string)$value && $value < 4294967296 && $value >= 0);
    }

    /**
     * Check for MD5 string validity
     * @param string $val MD5 string to validate
     * @return bool Validity is ok or not
     */
    public static function md5($val)
    {
        if (!$val || !\is_string($val)) {
            return false;
        }

        return preg_match('/^[a-f0-9A-F]{32}$/', $val);
    }

    /**
     * Check for SHA1 string validity
     * @param string $val SHA1 string to validate
     * @return bool Validity is ok or not
     */
    public static function sha1($val)
    {
        if (!$val || !\is_string($val)) {
            return false;
        }

        return preg_match('/^[a-fA-F0-9]{40}$/', $val);
    }

    /**
     * Check object validity
     * @param string $val e.g '#dedede'
     * @return bool Validity is ok or not
     */
    public static function color($val)
    {
        if (!$val || !\is_string($val)) {
            return false;
        }

        return preg_match('/^(#[0-9a-fA-F]{6}|[a-zA-Z0-9-]*)$/', $val);
    }

    /**
     * Check if URL is absolute
     * @param string $url URL to validate
     * @return bool Validity is ok or not
     */
    public static function absoluteUrl($url)
    {
        if (!empty($url)) {
            return preg_match('/^(https?:)?\/\/[$~:;#,%&_=\(\)\[\]\.\? \+\-@\/a-zA-Z0-9]+$/', $url);
        }

        return false;
    }

    /**
     * Check for standard name file validity
     * @param string $name Name to validate
     * @return bool Validity is ok or not
     */
    public static function fileName($name)
    {
        return preg_match('/^[a-zA-Z0-9_.-]+$/', $name);
    }

    /**
     * Check for standard name directory validity
     * @param string $dir Directory to validate
     * @return bool Validity is ok or not
     */
    public static function dirName($dir)
    {
        return (bool)preg_match('/^[a-zA-Z0-9_.-]*$/', $dir);
    }
}

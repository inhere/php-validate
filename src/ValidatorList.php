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
 * Class ValidatorList
 * @package Inhere\Validate
 */
final class ValidatorList
{
    /*******************************************************************************
     * Validators
     ******************************************************************************/
    /**
     * 值是否为空判断
     * @param mixed $val
     * @return bool
     */
    public static function isEmpty($val)
    {
        if (\is_string($val)) {
            $val = trim($val);
        }

        return $val === '' || $val === null || $val === false || $val === [];
    }

    /*******************************************************************************
     * bool/int/float/string validators
     ******************************************************************************/
    /**
     * 布尔值验证，把值作为布尔选项来验证。
     *   如果是 "1"、"true"、"on" 和 "yes"，则返回 TRUE。
     *   如果是 "0"、"false"、"off"、"no" 和 ""，则返回 FALSE。
     *   否则返回 NULL。
     * @param  mixed $val 要验证的数据
     * @param  mixed $default 设置验证失败时返回默认值
     * @param  int $flags 标志  FILTER_NULL_ON_FAILURE
     * @return bool
     */
    public static function boolean($val, $default = null, $flags = 0)
    {
        $settings = [];
        if ($default !== null) {
            $settings['options']['default'] = $default;
        }
        if ($flags !== 0) {
            $settings['flags'] = $flags;
        }

        return filter_var($val, FILTER_VALIDATE_BOOLEAN, $settings);
    }

    /**
     * @see ValidatorList::boolean()
     * {@inheritdoc}
     */
    public static function bool($val, $default = null, $flags = 0)
    {
        return self::boolean($val, $default, $flags);
    }

    /**
     * @param  mixed $val 要验证的变量
     * @param  array $options 可选的选项设置
     * $options = [
     *      'default' => 'default value',
     *      'decimal' => 2
     *  ]
     * @param  int $flags FILTER_FLAG_ALLOW_THOUSAND
     * @return bool
     */
    public static function float($val, array $options = [], $flags = 0)
    {
        $settings = [];
        if ($options) {
            $settings['options'] = $options;
        }
        if ($flags !== 0) {
            $settings['flags'] = $flags;
        }

        return filter_var($val, FILTER_VALIDATE_FLOAT, $settings);
    }

    /**
     * int 验证
     * @param  mixed $val 要验证的变量
     * @param  array $options 可选的选项设置
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
    public static function integer($val, array $options = [], $flags = 0)
    {
        if (!is_numeric($val)) {
            return false;
        }
        $settings = [];
        if ($options) {
            $settings['options'] = $options;
        }
        if ($flags !== 0) {
            $settings['flags'] = $flags;
        }

        return filter_var($val, FILTER_VALIDATE_INT, $settings) !== false;
    }

    /**
     * @see ValidatorList::integer()
     * {@inheritdoc}
     */
    public static function int($val, array $options = [], $flags = 0)
    {
        return self::integer($val, $options, $flags);
    }

    /**
     * check var is a integer and greater than 0
     * @param $val
     * @param array $options
     * @param int $flags
     * @return bool
     */
    public static function number($val, array $options = [], $flags = 0)
    {
        return self::integer($val, $options, $flags) && $val > 0;
    }

    /**
     * @see ValidatorList::number()
     * {@inheritdoc}
     */
    public static function num($val, array $options = [], $flags = 0)
    {
        return self::number($val, $options, $flags);
    }

    /**
     * check val is a string
     * @param mixed $val
     * @param int $minLength
     * @param null|int $maxLength
     * @return bool
     */
    public static function string($val, $minLength = 0, $maxLength = null)
    {
        return !\is_string($val) ? false : self::length($val, $minLength, $maxLength);
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

        return 1 === preg_match('/^[\\w-]+$/', $val);
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
     * @return bool
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

        $options = [];
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
        } else {
            return false;
        }

        return self::integer($val, $options);
    }

    /**
     * @see ValidatorList::size()
     * {@inheritdoc}
     */
    public static function between($val, $min = null, $max = null)
    {
        return self::size($val, $min, $max);
    }

    /**
     * @see ValidatorList::size()
     * {@inheritdoc}
     */
    public static function range($val, $min = null, $max = null)
    {
        return self::size($val, $min, $max);
    }

    /**
     * 必须是等于给定值
     * @param  mixed $val
     * @param  mixed $excepted
     * @return bool
     */
    public static function mustBe($val, $excepted)
    {
        return $val === $excepted;
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
     * @param  integer $minLength 最小长度
     * @param  int $maxLength 最大长度
     * @return bool
     */
    public static function length($val, $minLength = 0, $maxLength = null)
    {
        if (!\is_string($val) && !\is_array($val)) {
            return false;
        }

        return self::size($val, $minLength, $maxLength);
    }
    /*******************************************************************************
     * custom validators
     ******************************************************************************/
    /**
     * 用正则验证数据
     * @param  string $val 要验证的数据
     * @param  string $regexp 正则表达式 "/^M(.*)/"
     * @param null $default
     * @return bool
     */
    public static function regexp($val, $regexp, $default = null)
    {
        $options = ['regexp' => $regexp];
        if ($default !== null) {
            $options['default'] = $default;
        }

        return (bool)filter_var($val, FILTER_VALIDATE_REGEXP, ['options' => $options]);
    }

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
    /*******************************************************************************
     * list/map/enum validators
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
     * 验证值是否是一个非自然数组 map (key - value 形式的)
     * @param  mixed $val
     * @return bool
     */
    public static function isMap($val)
    {
        if (!\is_array($val)) {
            return false;
        }

        /** @var array $val */
        foreach ($val as $k => $v) {
            if (\is_string($k)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 验证值是否是一个自然数组 list (key是从0自然增长的)
     * @param  array|mixed $val
     * @return bool
     */
    public static function isList($val)
    {
        if (!\is_array($val) || !isset($val[0])) {
            return false;
        }

        $prevKey = 0;

        /** @var array $val */
        foreach ($val as $k => $v) {
            if (!\is_int($k)) {
                return false;
            }

            if ($k !== $prevKey) {
                return false;
            }

            $prevKey++;
        }

        return true;
    }

    /**
     * 验证字段值是否是一个 int list
     * @param  array|mixed $val
     * @return bool
     */
    public static function intList($val)
    {
        if (!$val || !\is_array($val)) {
            return false;
        }

        /** @var array $val */
        foreach ($val as $k => $v) {
            if (!\is_int($k)) {
                return false;
            }

            if (!is_numeric($v)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 验证字段值是否是一个 number list
     * @param  array|mixed $val
     * @return bool
     */
    public static function numList($val)
    {
        if (!$val || !\is_array($val)) {
            return false;
        }

        /** @var array $val */
        foreach ($val as $k =>  $v) {
            if (!\is_int($k)) {
                return false;
            }

            if (!is_numeric($v) || $v <= 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * 验证字段值是否是一个 string list
     * @param  array|mixed $val
     * @return bool
     */
    public static function strList($val)
    {
        if (!$val || !\is_array($val)) {
            return false;
        }

        /** @var array $val */
        foreach ($val as $k => $v) {
            if (!\is_int($k)) {
                return false;
            }

            if (\is_string($v)) {
                return true;
            }
        }

        return false;
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

    /**
     * @param  mixed $val
     * @param array|string $dict
     * @return bool
     */
    public static function in($val, $dict)
    {
        if (\is_string($dict) && strpos($dict, ',')) {
            $val = (string)$val;
            // fixed: data type
            $dict = array_map('trim', explode(',', $dict));
        }

        return \in_array($val, (array)$dict, false);
    }

    /**
     * @param  mixed $val
     * @param array|string $dict
     * @return bool
     */
    public static function enum($val, $dict)
    {
        return self::in($val, $dict);
    }

    /**
     * @param  mixed $val
     * @param array|string $dict
     * @return bool
     */
    public static function notIn($val, $dict)
    {
        if (\is_string($dict) && strpos($dict, ',')) {
            $dict = array_map('trim', explode(',', $dict));
        }

        return !\in_array($val, (array)$dict, true);
    }

    /**
     * @param mixed $val
     * @param mixed $compareVal
     * @return bool
     */
    public static function compare($val, $compareVal)
    {
        return $val === $compareVal;
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
        if (!$val || !($time = strtotime($val))) {
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
        if (date($format, $unixTime) === $val) {
            return true;
        }

        return false;
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
        if (!($valueTime = strtotime($val))) {
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
        if (!($valueTime = strtotime($val))) {
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
     * Check for date format
     * @param string $date Date to validate
     * @return bool Validity is ok or not
     */
    public static function isDateFormat($date)
    {
        return (bool)preg_match('/^([\\d]{4})-((0?[\\d])|(1[0-2]))-((0?[\\d])|([1-2][\\d])|(3[01]))( [\\d]{2}:[\\d]{2}:[\\d]{2})?$/', $date);
    }

    /**
     * Check for date validity
     * @param string $date Date to validate
     * @return bool Validity is ok or not
     */
    public static function isDate($date)
    {
        if (!preg_match('/^([\\d]{4})-((?:0?[\\d])|(?:1[0-2]))-((?:0?[\\d])|(?:[1-2][\\d])|(?:3[01]))( [\\d]{2}:[\\d]{2}:[\\d]{2})?$/', $date, $matches)) {
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
        return 1 === preg_match('/^1[2-9]\\d{9}$/', $val);
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
        return empty($val) || preg_match('/^\\d{6}$/', $val);
    }

    /**
     * Check for price validity
     * @param string $price Price to validate
     * @return bool Validity is ok or not
     */
    public static function price($price)
    {
        return 1 === preg_match('/^[\\d]{1,10}(\\.[\\d]{1,9})?$/', $price);
    }

    /**
     * Check for price validity (including negative price)
     * @param string $price Price to validate
     * @return bool Validity is ok or not
     */
    public static function negativePrice($price)
    {
        return 1 === preg_match('/^[-]?[\\d]{1,10}(\\.[\\d]{1,9})?$/', $price);
    }

    /**
     * Check for a float number validity
     * @param float $float Float number to validate
     * @return bool Validity is ok or not
     */
    public static function isFloat($float)
    {
        return (string)(double)$float === (string)$float;
    }

    public static function isUnsignedFloat($float)
    {
        return (string)(double)$float === (string)$float && $float >= 0;
    }

    /**
     * Check for an integer validity
     * @param int $value Integer to validate
     * @return bool Validity is ok or not
     */
    public static function isInt($value)
    {
        return (string)(int)$value === (string)$value || $value === false;
    }

    /**
     * Check for an integer validity (unsigned)
     * @param int $value Integer to validate
     * @return bool Validity is ok or not
     */
    public static function isUnsignedInt($value)
    {
        return (string)(int)$value === (string)$value && $value < 4294967296 && $value >= 0;
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
            return preg_match('/^(https?:)?\\/\\/[$~:;#,%&_=\\(\\)\\[\\]\\.\\? \\+\\-@\\/a-zA-Z0-9]+$/', $url);
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
    ///////////////////////////////////////////

    /**
     * @link http://php.net/manual/zh/function.filter-input.php
     * @param  int $type INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV
     * @param $varName
     * @param  array $filter 过滤/验证器 {@link http://php.net/manual/zh/filter.filters.php}
     * @param  array $options 一个选项的关联数组，或者按位区分的标示。
     *                         如果过滤器接受选项，可以通过数组的 "flags" 位去提供这些标示。
     * 如果成功的话返回所请求的变量。
     * 如果成功的话返回所请求的变量。
     * 如果过滤失败则返回 FALSE ，
     * 如果 varName 不存在的话则返回 NULL 。
     * 如果标示 FILTER_NULL_ON_FAILURE 被使用了，那么当变量不存在时返回 FALSE ，当过滤失败时返回 NULL 。
     */
    public static function input($type, $varName, $filter, array $options = [])
    {
    }

    public static function multi(array $data, array $filters = [])
    {
    }

    /**
     * @link http://php.net/manual/zh/function.filter-input-array.php
     * 检查(验证/过滤)输入数据中的多个变量名 like filter_input_array()
     * 当需要获取很多变量却不想重复调用 filter_input()时很有用。
     * @param  int $type One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV. 要检查的输入数据
     * @param  mixed $definition 一个定义参数的数组。
     *                            一个有效的键必须是一个包含变量名的string，
     *                            一个有效的值要么是一个filter type，或者是一个array 指明了过滤器、标示和选项。
     *                            如果值是一个数组，那么它的有效的键可以是 :
     *                                filter， 用于指明 filter type，
     *                                flags 用于指明任何想要用于过滤器的标示，
     *                                options 用于指明任何想要用于过滤器的选项。
     *                            参考下面的例子来更好的理解这段说明。
     * @param  bool $addEmpty 在返回值中添加 NULL 作为不存在的键。
     * 如果成功的话返回一个所请求的变量的数组，
     * 如果失败的话返回 FALSE 。
     * 对于数组的值，
     *     如果过滤失败则返回 FALSE ，
     *     如果 variable_name 不存在的话则返回 NULL 。
     * 如果标示 FILTER_NULL_ON_FAILURE 被使用了，那么当变量不存在时返回 FALSE ，当过滤失败时返回 NULL 。
     */
    public static function inputMulti($type, $definition, $addEmpty = true)
    {
    }

    /**
     * 检查变量名是否存在
     * @param  int $type One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV. 要检查的输入数据
     * @param  string $varName Name of a variable to check. 要检查的变量名
     */
    public static function inputHasVar($type, $varName)
    {
    }
}

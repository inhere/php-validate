<?php
/**
 *
 */
namespace Inhere\Validate;

/**
 * Class StrHelper
 * @package Inhere\Validate
 */
class Helper
{
    /**
     * @param $str
     * @return bool|string
     */
    public static function strtolower($str)
    {
        if (is_array($str)) {
            return false;
        }

        if (function_exists('mb_strtolower')) {
            return mb_strtolower($str, 'utf-8');
        }

        return strtolower($str);
    }

    /**
     * @param string $str
     * @param string $encoding
     * @return int
     */
    public static function strlen($str, $encoding = 'UTF-8')
    {
        $str = html_entity_decode($str, ENT_COMPAT, 'UTF-8');

        if (function_exists('mb_strlen')) {
            return mb_strlen($str, $encoding);
        }

        return strlen($str);
    }

    /**
     * @param $str
     * @return bool|string
     */
    public static function strtoupper($str)
    {
        if (is_array($str)) {
            return false;
        }

        if (function_exists('mb_strtoupper')) {
            return mb_strtoupper($str, 'utf-8');
        }

        return strtoupper($str);
    }

    /**
     * @param $str
     * @param $start
     * @param bool|false $length
     * @param string $encoding
     * @return bool|string
     */
    public static function substr($str, $start, $length = false, $encoding = 'utf-8')
    {
        if (is_array($str)) {
            return false;
        }

        if (function_exists('mb_substr')) {
            return mb_substr($str, (int)$start, ($length === false ? self::strlen($str) : (int)$length), $encoding);
        }

        return substr($str, $start, ($length === false ? self::strlen($str) : (int)$length));
    }

    /**
     * @param $str
     * @param $find
     * @param int $offset
     * @param string $encoding
     * @return bool|int
     */
    public static function strpos($str, $find, $offset = 0, $encoding = 'UTF-8')
    {
        if (function_exists('mb_strpos')) {
            return mb_strpos($str, $find, $offset, $encoding);
        }

        return strpos($str, $find, $offset);
    }

    /**
     * @param $str
     * @param $find
     * @param int $offset
     * @param string $encoding
     * @return bool|int
     */
    public static function strrpos($str, $find, $offset = 0, $encoding = 'utf-8')
    {
        if (function_exists('mb_strrpos')) {
            return mb_strrpos($str, $find, $offset, $encoding);
        }

        return strrpos($str, $find, $offset);
    }

    /**
     * @param $str
     * @return string
     */
    public static function ucfirst($str)
    {
        return self::strtoupper(self::substr($str, 0, 1)).self::substr($str, 1);
    }

    /**
     * @param $str
     * @return string
     */
    public static function ucwords($str)
    {
        if (function_exists('mb_convert_case')) {
            return mb_convert_case($str, MB_CASE_TITLE);
        }

        return ucwords(self::strtolower($str));
    }

    /**
     * Translates a string with underscores into camel case (e.g. first_name -> firstName)
     * @prototype string public static function toCamelCase(string $str[, bool $capitalise_first_char = false])
     * @param $str
     * @param bool $upper_case_first_char
     * @return mixed
     */
    public static function toCamelCase($str, $upper_case_first_char = false)
    {
        $str = self::strtolower($str);

        if ($upper_case_first_char) {
            $str = self::ucfirst($str);
        }

        return preg_replace_callback('/_+([a-z])/', function($c){ return strtoupper($c[1]);}, $str);
    }

    /**
     * Transform a CamelCase string to underscore_case string
     *
     * @param string $string
     * @param string $sep
     * @return string
     */
    public static function toUnderscoreCase($string, $sep='_')
    {
        // 'CMSCategories' => 'cms_categories'
        // 'RangePrice' => 'range_price'
        return self::strtolower(trim(preg_replace('/([A-Z][a-z])/', $sep . '$1', $string), $sep));
    }

    /**
     * getValueOfArray 支持以 '.' 分割进行子级值获取 eg: 'goods.apple'
     * @param  array $array
     * @param  array|string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValueOfArray(array $array, $key, $default = null)
    {
        if (null === $key) {
            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * @param $cb
     * @param array $args
     * @return mixed
     */
    public static function call($cb, array $args = [])
    {
        $args = array_values($args);

        if (
            (is_object($cb) && method_exists($cb, '__invoke')) ||
            (is_string($cb) && function_exists($cb))
        ) {
            $ret = $cb(...$args);
        } elseif (is_array($cb)) {
            list($obj, $mhd) = $cb;

            $ret = is_object($obj) ? $obj->$mhd(...$args) : $obj::$mhd(...$args);
        } elseif (method_exists('Swoole\Coroutine', 'call_user_func_array')) {
            $ret = \Swoole\Coroutine::call_user_func_array($cb, $args);
        } else {
            $ret = call_user_func_array($cb, $args);
        }

        return $ret;
    }
}

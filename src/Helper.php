<?php
/**
 *
 */
namespace inhere\validate;

/**
 * Class StrHelper
 * @package inhere\validate
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
     * @param  array  $data
     * @param  array|string  $key
     * @return mixed
     */
    public static function getValueOfArray(array $data, $key, $default = null)
    {
        if (!$key) {
            return $data;
        }

        if (is_string($key)) {
            $nodes = strpos(trim($key, '. '), '.') ? explode('.', $key) : [$key];
        } else {
            $nodes = (array)$key;
        }

        $temp = $data;

        foreach ($nodes as $name) {
            if (isset($temp[$name])) {
                $temp = $temp[$name];
            } else {
                $temp = $default;
                break;
            }
        }

        unset($data);
        return $temp;
    }

    /**
     * 使用正则验证数据
     * @access public
     * @param string $value  要验证的数据
     * @param string $rule 验证规则 require email url currency number integer english
     * @return boolean
     */
    public static function regexVerify($value,$rule)
    {
        $value    = trim($value);
        $validate = array(
            'require'   =>  '/\S+/',
            'email'     =>  '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            // 'url'       =>  '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
            'url'       =>  '/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i',
            'currency'  =>  '/^\d+(\.\d+)?$/', # 货币
            'number'    =>  '/^\d+$/',
            'zip'       =>  '/^\d{6}$/',
            'integer'   =>  '/^[-\+]?\d+$/',
            'double'    =>  '/^[-\+]?\d+(\.\d+)?$/',
            'english'   =>  '/^[A-Za-z]+$/',
        );

        // 检查是否有内置的正则表达式
        if (isset($validate[strtolower($rule)])){
            $rule       =   $validate[strtolower($rule)];
        }

        return preg_match($rule,$value)===1;
    }
}

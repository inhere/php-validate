<?php
/**
 *
 */
namespace inhere\validate;

/**
 * Class StrHelper
 * @package inhere\validate
 */
class StrHelper
{
    /**
     * 使用正则验证数据
     * @access public
     * @param string $value  要验证的数据
     * @param string $rule 验证规则 require email url currency number integer english
     * @return boolean
     */
    static public function regexVerify($value,$rule)
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

    /**
     * 检查字符串是否是正确的变量名
     * @param $string
     * @return bool
     */
    static public function isVarName($string)
    {
        return preg_match('@^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*@i', $string)===1;
    }

    /**
     * 计算字符长度
     * @param  [type] $str
     * @return int|string [type]
     */
    static public function length($str)
    {

        if (empty($str)){
          return '0';
        }

        if ((string)$str=='0'){
          return '1';
        }

        if (function_exists('mb_strlen')){
            return mb_strlen($str,'utf-8');
        }
        else {
            preg_match_all("/./u", $str, $arr);

            return count($arr[0]);
        }
    }

    /**
     * @from web
     * 可以统计中文字符串长度的函数
     * @param string $str 要计算长度的字符串
     * @internal param bool $type 计算长度类型，0(默认)表示一个中文算一个字符，1表示一个中文算两个字符
     * @return int
     */
    static public function abs_length($str)
    {
        if (empty($str)){
            return 0;
        }

        if (function_exists('mb_strwidth')){
            return mb_strwidth($str,'utf-8');
        } else if (function_exists('mb_strlen')){
            return mb_strlen($str,'utf-8');
        }
        else {
            preg_match_all("/./u", $str, $ar);
            return count($ar[0]);
        }
    }

    /**
     * @from web
     *  utf-8编码下截取中文字符串,参数可以参照substr函数
     * @param string $str 要进行截取的字符串
     * @param int $start 要进行截取的开始位置，负数为反向截取
     * @param int $end 要进行截取的长度
     * @return string
     */
    static public function utf8_substr($str,$start=0,$end=null)
    {
        if (empty($str)){
            return false;
        }

        if (function_exists('mb_substr')){
            if (func_num_args() >= 3) {
                $end = func_get_arg(2);

                return mb_substr($str,$start,$end,'utf-8');
            } else {
                mb_internal_encoding("UTF-8");

                return mb_substr($str,$start);
            }

        } else {
            $null = "";
            preg_match_all("/./u", $str, $ar);

            if (func_num_args() >= 3) {
                $end = func_get_arg(2);

                return implode($null, array_slice($ar[0],$start,$end));
            } else {
                return implode($null, array_slice($ar[0],$start));
            }
        }
    }


    /**
     * @from web
     * 中文截取，支持gb2312,gbk,utf-8,big5   *
     * @param string $str 要截取的字串
     * @param int $start 截取起始位置
     * @param int $length 截取长度
     * @param string $charset utf-8|gb2312|gbk|big5 编码
     * @param bool $suffix 是否加尾缀
     * @return string
     */
    static public function zh_substr($str, $start=0, $length, $charset="utf-8", $suffix=true)
    {
        if (function_exists("mb_substr"))
        {
            if (mb_strlen($str, $charset) <= $length) {
                return $str;
            }

            $slice = mb_substr($str, $start, $length, $charset);
        } else {
            $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312']  = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk']     = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5']    = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";

            preg_match_all($re[$charset], $str, $match);
            if (count($match[0]) <= $length) {
                return $str;
            }

            $slice = implode("",array_slice($match[0], $start, $length));
        }

        return (bool)$suffix ? $slice."…" : $slice;
    }

    /**
     * @param $str
     * @return bool|string
     */
    static public function strtolower($str)
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
     * @param $str
     * @param string $encoding
     * @return bool|int
     */
    static public function strlen($str, $encoding = 'UTF-8')
    {
        if (is_array($str)) {
            return false;
        }

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
    static public function strtoupper($str)
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
    static public function substr($str, $start, $length = false, $encoding = 'utf-8')
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
    static public function strpos($str, $find, $offset = 0, $encoding = 'UTF-8')
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
    static public function strrpos($str, $find, $offset = 0, $encoding = 'utf-8')
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
    static public function ucfirst($str)
    {
        return self::strtoupper(self::substr($str, 0, 1)).self::substr($str, 1);
    }

    /**
     * @param $str
     * @return string
     */
    static public function ucwords($str)
    {
        if (function_exists('mb_convert_case')) {
            return mb_convert_case($str, MB_CASE_TITLE);
        }

        return ucwords(self::strtolower($str));
    }

    /**
     * Translates a string with underscores into camel case (e.g. first_name -> firstName)
     * @prototype string static public function toCamelCase(string $str[, bool $capitalise_first_char = false])
     * @param $str
     * @param bool $upper_case_first_char
     * @return mixed
     */
    static public function toCamelCase($str, $upper_case_first_char = false)
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
     * @return string
     */
    static public function toUnderscoreCase($string)
    {
        // 'CMSCategories' => 'cms_categories'
        // 'RangePrice' => 'range_price'
        return self::strtolower(trim(preg_replace('/([A-Z][a-z])/', '_$1', $string), '_'));
    }

    /**
     * Convert a shorthand byte value from a PHP configuration directive to an integer value
     * @param string $value value to convert
     * @return int
     */
    static public function convertBytes($value)
    {
        if (is_numeric($value))
            return $value;
        else {
            $value_length = strlen($value);
            $qty = (int)substr($value, 0, $value_length - 1 );
            $unit = self::strtolower(substr($value, $value_length - 1));
            switch ($unit)
            {
                case 'k':
                    $qty *= 1024;
                    break;
                case 'm':
                    $qty *= 1048576;
                    break;
                case 'g':
                    $qty *= 1073741824;
                    break;
            }
            return $qty;
        }
    }

    /**
     * Format a number into a human readable format
     * e.g. 24962496 => 23.81M
     * @param     $size
     * @param int $precision
     * @return string
     */
    static public function formatBytes($size, $precision = 2)
    {
        if (!$size) {
            return '0';
        }

        $base     = log($size) / log(1024);
        $suffixes = array('', 'k', 'M', 'G', 'T');
        $floorBase = floor($base);

        return round(pow(1024, $base - $floorBase), $precision).$suffixes[(int)$floorBase];
    }
}
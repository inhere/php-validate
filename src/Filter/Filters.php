<?php
/**
 * @date 2015.08.05 sanitize
 * 过滤器(strainer/filter): 过滤数据，去除不合要求的数据，返回过滤后的数据(始终返回字符串, 全部不符合返回空字符串)
 */

namespace Inhere\Validate\Filter;

use Inhere\Validate\Utils\Helper;

/**
 * Class Filters
 * @package Inhere\Validate\Filter
 */
final class Filters
{
    /**
     * don't allow create instance.
     */
    private function __construct()
    {
    }

    /**
     * 布尔值验证，转换成字符串后是下列的一个，就认为他是个bool值
     *   - "1"、"true"、"on" 和 "yes" (equal TRUE)
     *   - "0"、"false"、"off"、"no" 和 ""(equal FALSE)
     * 注意： NULL 不是标量类型
     * @param  mixed $val
     * @param bool $nullAsFalse
     * @return bool
     */
    public static function boolean($val, $nullAsFalse = false)
    {
        if ($val !== null && !is_scalar($val)) {
            return (bool)$val;
        }

        return filter_var($val, FILTER_VALIDATE_BOOLEAN, [
            'flags' => $nullAsFalse ? FILTER_NULL_ON_FAILURE : 0
        ]);
    }

    /**
     * @see Validators::boolean()
     * {@inheritdoc}
     */
    public static function bool($val, $nullAsFalse = false)
    {
        return self::boolean($val, $nullAsFalse);
    }

    /**
     * 过滤器删除数字中所有非法的字符。
     * @note 该过滤器允许所有数字以及 . + -
     * @param  mixed $val 要过滤的变量
     * @return int
     */
    public static function integer($val)
    {
        if (\is_array($val)) {
            return array_map(self::class . '::integer', $val);
        }

        return (int)filter_var($val, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * @see Filters::integer()
     * {@inheritdoc}
     */
    public static function int($val)
    {
        return self::integer($val);
    }

    /**
     * @param mixed $val
     * @return number
     */
    public static function abs($val)
    {
        return abs((int)$val);
    }

    /**
     * 过滤器删除浮点数中所有非法的字符。
     * @note 该过滤器默认允许所有数字以及 + -
     * @param  mixed $val 要过滤的变量
     * @param null|int $decimal
     * @param  int $flags 标志
     *                    FILTER_FLAG_ALLOW_FRACTION - 允许小数分隔符 （比如 .）
     *                    FILTER_FLAG_ALLOW_THOUSAND - 允许千位分隔符（比如 ,）
     *                    FILTER_FLAG_ALLOW_SCIENTIFIC - 允许科学记数法（比如 e 和 E）
     * @return mixed
     */
    public static function float($val, $decimal = null, $flags = FILTER_FLAG_ALLOW_FRACTION)
    {
        $settings = [];

        if ((int)$flags !== 0) {
            $settings['flags'] = (int)$flags;
        }

        $ret = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, $settings);
        $new = strpos($ret, '.') ? (float)$ret : $ret;

        if (\is_int($decimal)) {
            return round($new, $decimal);
        }

        return $new;
    }

    /**
     * 去除标签，去除或编码特殊字符。
     * @param  string|array $val
     * @param  int $flags 标志
     *                    FILTER_FLAG_NO_ENCODE_QUOTES - 该标志不编码引号
     *                    FILTER_FLAG_STRIP_LOW - 去除 ASCII 值在 32 以下的字符
     *                    FILTER_FLAG_STRIP_HIGH - 去除 ASCII 值在 127 以上的字符
     *                    FILTER_FLAG_ENCODE_LOW - 编码 ASCII 值在 32 以下的字符
     *                    FILTER_FLAG_ENCODE_HIGH - 编码 ASCII 值在 127 以上的字符
     *                    FILTER_FLAG_ENCODE_AMP - 把 & 字符编码为 &amp;
     * @return string
     */
    public static function string($val, $flags = 0)
    {
        if (\is_array($val)) {
            return array_map(self::class . '::string', $val);
        }

        $settings = [];

        if ((int)$flags !== 0) {
            $settings['flags'] = (int)$flags;
        }

        return filter_var($val, FILTER_SANITIZE_FULL_SPECIAL_CHARS, $settings);
    }

    /**
     * @see Filters::string()
     * {@inheritdoc}
     */
    public static function stripped($val, $flags = 0)
    {
        return self::string($val, $flags);
    }

    /**
     * Convert \n and \r\n and \r to <br/>
     * @param string $str String to transform
     * @return string New string
     */
    public static function nl2br($str)
    {
        return str_replace(["\r\n", "\r", "\n"], '<br/>', $str);
    }

    /**
     * simple trim space
     * @param string|array $val
     * @return string|array
     */
    public static function trim($val)
    {
        return \is_array($val) ? array_map(function ($val) {
            return \is_string($val) ? \trim($val) : $val;
        }, $val) : \trim((string)$val);
    }

    /**
     * clear space
     * @param string $val
     * @return mixed
     */
    public static function clearSpace($val)
    {
        return str_replace(' ', '', \trim($val));
    }

    /**
     * clear newline `\n` `\r\n` `\r`
     * @param string $val
     * @return mixed
     */
    public static function clearNewline($val)
    {
        return str_replace(["\r\n", "\r", "\n"], '', \trim($val));
    }

    /**
     * string to lowercase
     * @param string $val
     * @return string
     */
    public static function lower($val)
    {
        return self::lowercase($val);
    }

    /**
     * string to lowercase
     * @param string $val
     * @return string
     */
    public static function lowercase($val)
    {
        if (!$val || !\is_string($val)) {
            return \is_numeric($val) ? $val : '';
        }

        return Helper::strToLower($val);
    }

    /**
     * string to uppercase
     * @param string $val
     * @return string
     */
    public static function upper($val)
    {
        return self::uppercase($val);
    }

    /**
     * string to uppercase
     * @param string $val
     * @return string
     */
    public static function uppercase($val)
    {
        if (!$val || !\is_string($val)) {
            return \is_numeric($val) ? $val : '';
        }

        return Helper::strToUpper($val);
    }

    /**
     * string to snakeCase
     * @param string $val
     * @param string $sep
     * @return string
     */
    public static function snake($val, $sep = '_')
    {
        return self::snakeCase($val, $sep);
    }

    /**
     * string to snakeCase
     * @param string $val
     * @param string $sep
     * @return string
     */
    public static function snakeCase($val, $sep = '_')
    {
        if (!$val || !\is_string($val)) {
            return '';
        }

        return Helper::toSnakeCase($val, $sep);
    }

    /**
     * string to camelcase
     * @param string $val
     * @param bool $ucFirst
     * @return string
     */
    public static function camel($val, $ucFirst = false)
    {
        return self::camelCase($val, $ucFirst);
    }

    /**
     * string to camelcase
     * @param string $val
     * @param bool $ucFirst
     * @return string
     */
    public static function camelCase($val, $ucFirst = false)
    {
        if (!$val || !\is_string($val)) {
            return '';
        }

        return Helper::toCamelCase($val, $ucFirst);
    }

    /**
     * string to time
     * @param string $val
     * @return int
     */
    public static function timestamp($val)
    {
        return self::strToTime($val);
    }

    /**
     * string to time
     * @param string $val
     * @return int
     */
    public static function strToTime($val)
    {
        if (!$val || !\is_string($val)) {
            return 0;
        }

        return (int)strtotime($val);
    }

    /**
     * @param string $str
     * @param string $sep
     * @return array
     */
    public static function str2list($str, $sep = ',')
    {
        return self::str2array($str, $sep);
    }

    /**
     * var_dump(str2array('34,56,678, 678, 89, '));
     * @param string $str
     * @param string $sep
     * @return array
     */
    public static function str2array($str, $sep = ',')
    {
        $str = trim($str, "$sep ");

        if (!$str) {
            return [];
        }

        return preg_split("/\s*$sep\s*/", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @param mixed $val
     * @param null|string $allowedTags
     * @return string
     */
    public static function clearTags($val, $allowedTags = null)
    {
        return self::stripTags($val, $allowedTags);
    }

    /**
     * @param mixed $val
     * @param null|string $allowedTags e.g '<p><a>' 允许 <p> 和 <a>
     * @return string
     */
    public static function stripTags($val, $allowedTags = null)
    {
        if (!$val || !\is_string($val)) {
            return '';
        }

        return $allowedTags ? strip_tags($val, $allowedTags) : strip_tags($val);
    }

    /**
     * 去除 URL 编码不需要的字符。
     * @note 与 urlencode() 函数很类似。
     * @param  string $val 要过滤的数据
     * @param  int $flags 标志
     *                    FILTER_FLAG_STRIP_LOW - 去除 ASCII 值在 32 以下的字符
     *                    FILTER_FLAG_STRIP_HIGH - 去除 ASCII 值在 32 以上的字符
     *                    FILTER_FLAG_ENCODE_LOW - 编码 ASCII 值在 32 以下的字符
     *                    FILTER_FLAG_ENCODE_HIGH - 编码 ASCII 值在 32 以上的字符
     * @return mixed
     */
    public static function encoded($val, $flags = 0)
    {
        $settings = [];

        if ((int)$flags !== 0) {
            $settings['flags'] = (int)$flags;
        }

        return filter_var($val, FILTER_SANITIZE_ENCODED, $settings);
    }

    /**
     * 应用 addslashes() 转义数据
     * @param  string $val
     * @return string
     */
    public static function quotes($val)
    {
        return filter_var($val, FILTER_SANITIZE_MAGIC_QUOTES);
    }

    /**
     * like htmlspecialchars(), HTML 转义字符 '"<>& 以及 ASCII 值小于 32 的字符。
     * @param  string $val
     * @param  int $flags 标志
     *                    FILTER_FLAG_STRIP_LOW - 去除 ASCII 值在 32 以下的字符
     *                    FILTER_FLAG_STRIP_HIGH - 去除 ASCII 值在 32 以上的字符
     *                    FILTER_FLAG_ENCODE_HIGH - 编码 ASCII 值在 32 以上的字符
     * @return string
     */
    public static function specialChars($val, $flags = 0)
    {
        $settings = [];

        if ((int)$flags !== 0) {
            $settings['flags'] = (int)$flags;
        }

        return filter_var($val, FILTER_SANITIZE_SPECIAL_CHARS, $settings);
    }

    /**
     * @param $val
     * @param int $flags
     * @return string
     */
    public static function escape($val, $flags = 0)
    {
        return self::specialChars($val, $flags);
    }

    /**
     *  HTML 转义字符 '"<>& 以及 ASCII 值小于 32 的字符。
     * @param  string $val
     * @param  int $flags 标志 FILTER_FLAG_NO_ENCODE_QUOTES
     * @return string
     */
    public static function fullSpecialChars($val, $flags = 0)
    {
        $settings = [];

        if ((int)$flags !== 0) {
            $settings['flags'] = (int)$flags;
        }

        return filter_var($val, FILTER_SANITIZE_FULL_SPECIAL_CHARS, $settings);
    }

    /**
     * 字符串长度过滤截取
     * @param  string $string 字符串
     * @param  integer $start 起始长度
     * @param  int $end 结束位置
     * @return string
     */
    public static function stringCute($string, $start = 0, $end = null)
    {
        if (!\is_string($string)) {
            return '';
        }

        // $length    = Helper::strlen($string);
        return Helper::subStr($string, $start, $end);
    }

    /**
     * @param string $string
     * @param int $start
     * @param null $end
     * @return string
     */
    public static function cut($string, $start = 0, $end = null)
    {
        return self::stringCute($string, $start, $end);
    }

    /**
     * url地址过滤 移除所有不符合 url 的字符
     * @note 该过滤器允许所有的字母、数字以及 $-_.+!*'(),{}|\^~[]`"><#%;/?:@&=
     * @param  string $val 要过滤的数据
     * @return mixed
     */
    public static function url($val)
    {
        return filter_var($val, FILTER_SANITIZE_URL);
    }

    /**
     * email 地址过滤 移除所有不符合 email 的字符
     * @param  string $val 要过滤的数据
     * @return mixed
     */
    public static function email($val)
    {
        return filter_var($val, FILTER_SANITIZE_EMAIL);
    }

    /**
     * 不进行任何过滤，去除或编码特殊字符。这个过滤器也是FILTER_DEFAULT别名。
     * 该过滤器删除那些对应用程序有潜在危害的数据。它用于去除标签以及删除或编码不需要的字符。
     * 如果不规定标志，则该过滤器没有任何行为。
     * @param  string $string
     * @param  int $flags 标志
     *                    FILTER_FLAG_STRIP_LOW - 去除 ASCII 值在 32 以下的字符
     *                    FILTER_FLAG_STRIP_HIGH - 去除 ASCII 值在 32 以上的字符
     *                    FILTER_FLAG_ENCODE_LOW - 编码 ASCII 值在 32 以下的字符
     *                    FILTER_FLAG_ENCODE_HIGH - 编码 ASCII 值在 32 以上的字符
     *                    FILTER_FLAG_ENCODE_AMP - 把 & 字符编码为 &amp;
     * @return string
     */
    public static function unsafeRaw($string, $flags = 0)
    {
        $settings = [];

        if ((int)$flags !== 0) {
            $settings['flags'] = (int)$flags;
        }

        return filter_var($string, FILTER_UNSAFE_RAW, $settings);
    }

    /**
     * 自定义回调过滤
     * @param  mixed $val
     * @param  callable $callback
     * @return bool
     */
    public static function callback($val, $callback)
    {
        return filter_var($val, FILTER_CALLBACK, ['options' => $callback]);
    }

    /**
     * 去除数组中的重复值
     * @param mixed $val
     * @return array
     */
    public static function unique($val)
    {
        if (!$val || \is_array($val)) {
            return $val;
        }

        return array_unique($val);
    }
}

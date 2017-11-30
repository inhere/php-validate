<?php
/**
 * @date 2015.08.05 sanitize
 * 过滤器(strainer/filter): 过滤数据，去除不合要求的数据，返回过滤后的数据(始终返回字符串, 全部不符合返回空字符串)
 */

namespace Inhere\Validate\Filter;

use Inhere\Validate\Utils\Helper;

/**
 * Class FilterList
 * @package Inhere\Validate\Filter
 */
final class FilterList
{
    /**
     * 过滤器删除数字中所有非法的字符。
     * @note 该过滤器允许所有数字以及 . + -
     * @param  mixed $var 要过滤的变量
     * @return mixed $string
     */
    public static function integer($var)
    {
        return (int)filter_var($var, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * @see FilterList::integer()
     * {@inheritdoc}
     */
    public static function int($var)
    {
        return self::integer($var);
    }

    /**
     * @param mixed $var
     * @return number
     */
    public static function abs($var)
    {
        return abs((int)$var);
    }

    /**
     * 过滤器删除浮点数中所有非法的字符。
     * @note 该过滤器默认允许所有数字以及 + -
     * @param  mixed $var 要过滤的变量
     * @param  int $flags 标志
     *                    FILTER_FLAG_ALLOW_FRACTION - 允许小数分隔符 （比如 .）
     *                    FILTER_FLAG_ALLOW_THOUSAND - 允许千位分隔符（比如 ,）
     *                    FILTER_FLAG_ALLOW_SCIENTIFIC - 允许科学记数法（比如 e 和 E）
     * @return mixed
     */
    public static function float($var, $flags = FILTER_FLAG_ALLOW_FRACTION)
    {
        $settings = [];

        if ((int)$flags !== 0) {
            $settings['flags'] = (int)$flags;
        }

        $ret = filter_var($var, FILTER_SANITIZE_NUMBER_FLOAT, $settings);

        return strpos($ret, '.') ? (float)$ret : $ret;
    }

    /**
     * 去除标签，去除或编码特殊字符。
     * @param  string $var
     * @param  int $flags 标志
     *                    FILTER_FLAG_NO_ENCODE_QUOTES - 该标志不编码引号
     *                    FILTER_FLAG_STRIP_LOW - 去除 ASCII 值在 32 以下的字符
     *                    FILTER_FLAG_STRIP_HIGH - 去除 ASCII 值在 127 以上的字符
     *                    FILTER_FLAG_ENCODE_LOW - 编码 ASCII 值在 32 以下的字符
     *                    FILTER_FLAG_ENCODE_HIGH - 编码 ASCII 值在 127 以上的字符
     *                    FILTER_FLAG_ENCODE_AMP - 把 & 字符编码为 &amp;
     * @return string
     */
    public static function string($var, $flags = 0)
    {
        $settings = [];

        if ((int)$flags !== 0) {
            $settings['flags'] = (int)$flags;
        }

        return filter_var($var, FILTER_SANITIZE_FULL_SPECIAL_CHARS, $settings);
    }

    /**
     * @see FilterList::string()
     * {@inheritdoc}
     */
    public static function stripped($var, $flags = 0)
    {
        return self::string($var, $flags);
    }

    /**
     * simple trim space
     * @param string|array $var
     * @return string|array
     */
    public static function trim($var)
    {
        return \is_array($var) ? array_map(function ($val) {
            return \is_string($val) ? trim($val) : $val;
        }, $var) : trim((string)$var);
    }

    /**
     * string to lowercase
     * @param string $var
     * @return string
     */
    public static function lowercase($var)
    {
        if (!$var || !\is_string($var)) {
            return \is_numeric($var) ? $var : '';
        }

        return Helper::strToLower($var);
    }

    /**
     * string to uppercase
     * @param string $var
     * @return string
     */
    public static function uppercase($var)
    {
        if (!$var || !\is_string($var)) {
            return \is_numeric($var) ? $var : '';
        }

        return Helper::strToUpper($var);
    }

    /**
     * string to time
     * @param string $var
     * @return int
     */
    public static function timestamp($var)
    {
        return self::strToTime($var);
    }

    /**
     * string to time
     * @param string $var
     * @return int
     */
    public static function strToTime($var)
    {
        if (!$var || !\is_string($var)) {
            return 0;
        }

        return (int)strtotime($var);
    }

    /**
     * 去除 URL 编码不需要的字符。
     * @note 与 urlencode() 函数很类似。
     * @param  string $var 要过滤的数据
     * @param  int $flags 标志
     *                    FILTER_FLAG_STRIP_LOW - 去除 ASCII 值在 32 以下的字符
     *                    FILTER_FLAG_STRIP_HIGH - 去除 ASCII 值在 32 以上的字符
     *                    FILTER_FLAG_ENCODE_LOW - 编码 ASCII 值在 32 以下的字符
     *                    FILTER_FLAG_ENCODE_HIGH - 编码 ASCII 值在 32 以上的字符
     * @return mixed
     */
    public static function encoded($var, $flags = 0)
    {
        $settings = [];

        if ((int)$flags !== 0) {
            $settings['flags'] = (int)$flags;
        }

        return filter_var($var, FILTER_SANITIZE_ENCODED, $settings);
    }

    /**
     *  应用 addslashes() 转义数据
     * @param  string $var
     * @return string
     */
    public static function quotes($var)
    {
        return filter_var($var, FILTER_SANITIZE_MAGIC_QUOTES);
    }

    /**
     *  HTML 转义字符 '"<>& 以及 ASCII 值小于 32 的字符。
     * @param  string $var
     * @param  int $flags 标志
     *                    FILTER_FLAG_STRIP_LOW - 去除 ASCII 值在 32 以下的字符
     *                    FILTER_FLAG_STRIP_HIGH - 去除 ASCII 值在 32 以上的字符
     *                    FILTER_FLAG_ENCODE_HIGH - 编码 ASCII 值在 32 以上的字符
     * @return string
     */
    public static function specialChars($var, $flags = 0)
    {
        $settings = [];

        if ((int)$flags !== 0) {
            $settings['flags'] = (int)$flags;
        }

        return filter_var($var, FILTER_SANITIZE_SPECIAL_CHARS, $settings);
    }

    /**
     *  HTML 转义字符 '"<>& 以及 ASCII 值小于 32 的字符。
     * @param  string $var
     * @param  int $flags 标志 FILTER_FLAG_NO_ENCODE_QUOTES
     * @return string
     */
    public static function fullSpecialChars($var, $flags = 0)
    {
        $settings = [];

        if ((int)$flags !== 0) {
            $settings['flags'] = (int)$flags;
        }

        return filter_var($var, FILTER_SANITIZE_FULL_SPECIAL_CHARS, $settings);
    }

    /**
     * 字符串长度过滤截取
     * @param  string $string 字符串
     * @param  integer $start 起始长度
     * @param  int $end 结束位置
     * @return mixed
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
     * url地址过滤 移除所有不符合 url 的字符
     * @note 该过滤器允许所有的字母、数字以及 $-_.+!*'(),{}|\^~[]`"><#%;/?:@&=
     * @param  string $var 要过滤的数据
     * @return mixed
     */
    public static function url($var)
    {
        return filter_var($var, FILTER_SANITIZE_URL);
    }

    /**
     * email 地址过滤 移除所有不符合 email 的字符
     * @param  string $var 要过滤的数据
     * @return mixed
     */
    public static function email($var)
    {
        return filter_var($var, FILTER_SANITIZE_EMAIL);
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
}

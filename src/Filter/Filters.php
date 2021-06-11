<?php declare(strict_types=1);
/**
 * @date 2015.08.05 sanitize
 * 过滤器(strainer/filter): 过滤数据，去除不合要求的数据，返回过滤后的数据(始终返回字符串, 全部不符合返回空字符串)
 */

namespace Inhere\Validate\Filter;

use Inhere\Validate\Helper;
use Inhere\Validate\Traits\NameAliasTrait;
use function abs;
use function array_map;
use function array_unique;
use function explode;
use function filter_var;
use function function_exists;
use function is_array;
use function is_int;
use function is_scalar;
use function is_string;
use function mb_convert_case;
use function mb_strtolower;
use function mb_strtoupper;
use function mb_substr;
use function preg_replace;
use function preg_replace_callback;
use function round;
use function str_replace;
use function strip_tags;
use function strpos;
use function strtolower;
use function strtotime;
use function strtoupper;
use function substr;
use function trim;
use function ucwords;
use const FILTER_CALLBACK;
use const FILTER_FLAG_ALLOW_FRACTION;
use const FILTER_NULL_ON_FAILURE;
use const FILTER_SANITIZE_EMAIL;
use const FILTER_SANITIZE_ENCODED;
use const FILTER_SANITIZE_FULL_SPECIAL_CHARS;
use const FILTER_SANITIZE_MAGIC_QUOTES;
use const FILTER_SANITIZE_NUMBER_FLOAT;
use const FILTER_SANITIZE_NUMBER_INT;
use const FILTER_SANITIZE_SPECIAL_CHARS;
use const FILTER_SANITIZE_URL;
use const FILTER_UNSAFE_RAW;
use const FILTER_VALIDATE_BOOLEAN;
use const MB_CASE_TITLE;
use const PHP_VERSION_ID;

/**
 * Class Filters
 *
 * @package Inhere\Validate\Filter
 */
final class Filters
{
    use NameAliasTrait;

    /** @var array filter aliases map */
    private static $aliases = [
        'substr'       => 'subStr',
        'substring'    => 'subStr',
        'str2list'     => 'explode',
        'str2array'    => 'explode',
        'string2list'  => 'explode',
        'string2array' => 'explode',
        'toUpper'      => 'uppercase',
        'str2upper'    => 'uppercase',
        'strToUpper'   => 'uppercase',
        'toLower'      => 'lowercase',
        'str2lower'    => 'lowercase',
        'strToLower'   => 'lowercase',
        'clearNl'      => 'clearNewline',
        'str2time'     => 'strToTime',
        'strtotime'    => 'strToTime',
    ];

    /**
     * 布尔值验证，转换成字符串后是下列的一个，就认为他是个bool值
     *   - "1"、"true"、"on" 和 "yes" (equal TRUE)
     *   - "0"、"false"、"off"、"no" 和 ""(equal FALSE)
     * 注意： NULL 不是标量类型
     *
     * @param mixed $val
     * @param bool  $nullAsFalse
     *
     * @return bool
     */
    public static function boolean($val, $nullAsFalse = false): bool
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
    public static function bool($val, $nullAsFalse = false): bool
    {
        return self::boolean($val, $nullAsFalse);
    }

    /**
     * 过滤器删除数字中所有非法的字符。
     *
     * @note 该过滤器允许所有数字以及 . + -
     *
     * @param mixed $val 要过滤的变量
     *
     * @return int|array
     */
    public static function integer($val)
    {
        if (is_array($val)) {
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
     *
     * @return int
     */
    public static function abs($val): int
    {
        return abs((int)$val);
    }

    /**
     * 过滤器删除浮点数中所有非法的字符。
     *
     * @note 该过滤器默认允许所有数字以及 + -
     *
     * @param mixed    $val   要过滤的变量
     * @param null|int $decimal
     * @param int      $flags 标志
     *                        FILTER_FLAG_ALLOW_FRACTION - 允许小数分隔符 （比如 .）
     *                        FILTER_FLAG_ALLOW_THOUSAND - 允许千位分隔符（比如 ,）
     *                        FILTER_FLAG_ALLOW_SCIENTIFIC - 允许科学记数法（比如 e 和 E）
     *
     * @return mixed
     */
    public static function float($val, $decimal = null, $flags = FILTER_FLAG_ALLOW_FRACTION)
    {
        $options = (int)$flags !== 0 ? ['flags' => (int)$flags] : [];

        $ret = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, $options);
        $new = strpos($ret, '.') ? (float)$ret : (int)$ret;

        if (is_int($decimal)) {
            return round($new, $decimal);
        }

        return $new;
    }

    /**
     * 去除标签，去除或编码特殊字符。
     *
     * @param string|array $val
     * @param int          $flags 标志
     *                            FILTER_FLAG_NO_ENCODE_QUOTES - 该标志不编码引号
     *                            FILTER_FLAG_STRIP_LOW - 去除 ASCII 值在 32 以下的字符
     *                            FILTER_FLAG_STRIP_HIGH - 去除 ASCII 值在 127 以上的字符
     *                            FILTER_FLAG_ENCODE_LOW - 编码 ASCII 值在 32 以下的字符
     *                            FILTER_FLAG_ENCODE_HIGH - 编码 ASCII 值在 127 以上的字符
     *                            FILTER_FLAG_ENCODE_AMP - 把 & 字符编码为 &amp;
     *
     * @return string|array
     */
    public static function string($val, $flags = 0)
    {
        if (is_array($val)) {
            return array_map(self::class . '::string', $val);
        }

        $options = (int)$flags !== 0 ? ['flags' => (int)$flags] : [];

        return (string)filter_var($val, FILTER_SANITIZE_FULL_SPECIAL_CHARS, $options);
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
     *
     * @param string $str String to transform
     *
     * @return string New string
     */
    public static function nl2br($str): string
    {
        return str_replace(["\r\n", "\r", "\n"], '<br/>', (string)$str);
    }

    /**
     * simple trim space
     *
     * @param string|array $val
     *
     * @return string|array
     */
    public static function trim($val)
    {
        return is_array($val) ? array_map(function ($val) {
            return is_string($val) ? trim($val) : $val;
        }, $val) : trim((string)$val);
    }

    /**
     * clear space
     *
     * @param string $val
     *
     * @return mixed
     */
    public static function clearSpace($val): string
    {
        return str_replace(' ', '', trim((string)$val));
    }

    /**
     * clear newline `\n` `\r\n` `\r`
     *
     * @param string $val
     *
     * @return mixed
     */
    public static function clearNewline($val): string
    {
        return str_replace(["\r\n", "\r", "\n"], '', trim((string)$val));
    }

    /**
     * string to lowercase
     *
     * @param string $val
     *
     * @return string
     */
    public static function lower($val): string
    {
        return self::lowercase($val);
    }

    /**
     * string to lowercase
     *
     * @param string|int $val
     *
     * @return string
     */
    public static function lowercase($val): string
    {
        if (!$val || !is_string($val)) {
            return is_int($val) ? (string)$val : '';
        }

        if (function_exists('mb_strtolower')) {
            return mb_strtolower($val, 'utf-8');
        }

        return strtolower($val);
    }

    /**
     * string to uppercase
     *
     * @param string $val
     *
     * @return string
     */
    public static function upper($val): string
    {
        return self::uppercase($val);
    }

    /**
     * string to uppercase
     *
     * @param string|int $str
     *
     * @return string
     */
    public static function uppercase($str): string
    {
        if (!$str || !is_string($str)) {
            return is_int($str) ? (string)$str : '';
        }

        if (function_exists('mb_strtoupper')) {
            return mb_strtoupper($str, 'utf-8');
        }

        return strtoupper($str);
    }

    /**
     * @param string|mixed $str
     *
     * @return string
     */
    public static function ucfirst($str): string
    {
        if (!$str || !is_string($str)) {
            return '';
        }

        return self::uppercase(self::subStr($str, 0, 1)) . self::subStr($str, 1);
    }

    /**
     * @param string|mixed $str
     *
     * @return string
     */
    public static function ucwords($str): string
    {
        if (!$str || !is_string($str)) {
            return '';
        }

        if (function_exists('mb_convert_case')) {
            return mb_convert_case($str, MB_CASE_TITLE);
        }

        return ucwords(self::lowercase($str));
    }

    /**
     * string to snake case
     *
     * @param string|mixed $val
     * @param string       $sep
     *
     * @return string
     */
    public static function snake($val, string $sep = '_'): string
    {
        return self::snakeCase($val, $sep);
    }

    /**
     * Transform a CamelCase string to underscore_case string
     *  'CMSCategories' => 'cms_categories'
     *  'RangePrice' => 'range_price'
     *
     * @param string $val
     * @param string $sep
     *
     * @return string
     */
    public static function snakeCase($val, string $sep = '_'): string
    {
        if (!$val || !is_string($val)) {
            return '';
        }

        $val = preg_replace('/([A-Z][a-z])/', $sep . '$1', $val);

        return self::lowercase(trim($val, $sep));
    }

    /**
     * string to camelcase
     *
     * @param string|mixed $val
     * @param bool         $ucFirst
     *
     * @return string
     */
    public static function camel($val, $ucFirst = false): string
    {
        return self::camelCase($val, $ucFirst);
    }

    /**
     * Translates a string with underscores into camel case (e.g. first_name -> firstName)
     *
     * @param string $val
     * @param bool   $ucFirst
     *
     * @return string
     */
    public static function camelCase($val, $ucFirst = false): string
    {
        if (!$val || !is_string($val)) {
            return '';
        }

        $str = self::lowercase($val);

        if ($ucFirst) {
            $str = self::ucfirst($str);
        }

        return preg_replace_callback('/_+([a-z])/', function ($c) {
            return strtoupper($c[1]);
        }, $str);
    }

    /**
     * string to time
     *
     * @param string $val
     *
     * @return int
     */
    public static function timestamp($val): int
    {
        return self::strToTime($val);
    }

    /**
     * string to time
     *
     * @param string $val
     *
     * @return int
     */
    public static function strToTime($val): int
    {
        if (!$val || !is_string($val)) {
            return 0;
        }

        return (int)strtotime($val);
    }

    /**
     * @param string $str
     * @param int    $start
     * @param int    $length
     * @param string $encoding
     *
     * @return bool|string
     */
    public static function subStr(string $str, int $start, int $length = 0, string $encoding = 'utf-8')
    {
        $length = $length === 0 ? Helper::strlen($str) : $length;

        if (function_exists('mb_substr')) {
            return mb_substr($str, $start, $length, $encoding);
        }

        return substr($str, $start, $length);
    }

    /**
     * @param string $string
     * @param string $delimiter
     * @param int    $limit
     *
     * @return array
     */
    public static function explode(string $string, string $delimiter = ',', int $limit = 0): array
    {
        $string = trim($string, "$delimiter ");
        if ($string === '') {
            return [];
        }

        $values  = [];
        $rawList = $limit < 1 ? explode($delimiter, $string) : explode($delimiter, $string, $limit);

        foreach ($rawList as $val) {
            if (($val = trim($val)) !== '') {
                $values[] = $val;
            }
        }

        return $values;
    }

    public static function str2list(string $str, string $sep = ',', int $limit = 0): array
    {
        return self::explode($str, $sep, $limit);
    }

    public static function str2array(string $string, string $delimiter = ',', int $limit = 0): array
    {
        return self::explode($string, $delimiter, $limit);
    }

    /**
     * @param mixed       $val
     * @param null|string $allowedTags
     *
     * @return string
     */
    public static function clearTags($val, $allowedTags = null): string
    {
        return self::stripTags($val, $allowedTags);
    }

    /**
     * @param mixed       $val
     * @param null|string $allowedTags e.g '<p><a>' 允许 <p> 和 <a>
     *
     * @return string
     */
    public static function stripTags($val, $allowedTags = null): string
    {
        if (!$val || !is_string($val)) {
            return '';
        }

        return $allowedTags ? strip_tags($val, $allowedTags) : strip_tags($val);
    }

    /**
     * 去除 URL 编码不需要的字符。
     *
     * @note 与 urlencode() 函数很类似。
     *
     * @param string $val   要过滤的数据
     * @param int    $flags 标志
     *                      FILTER_FLAG_STRIP_LOW - 去除 ASCII 值在 32 以下的字符
     *                      FILTER_FLAG_STRIP_HIGH - 去除 ASCII 值在 32 以上的字符
     *                      FILTER_FLAG_ENCODE_LOW - 编码 ASCII 值在 32 以下的字符
     *                      FILTER_FLAG_ENCODE_HIGH - 编码 ASCII 值在 32 以上的字符
     *
     * @return string
     */
    public static function encoded(string $val, int $flags = 0): string
    {
        $settings = $flags !== 0 ? ['flags' => $flags] : [];

        return (string)filter_var($val, FILTER_SANITIZE_ENCODED, $settings);
    }

    /**
     * 应用 addslashes() 转义数据
     *
     * @param string $val
     *
     * @return string
     */
    public static function quotes(string $val): string
    {
        if (PHP_VERSION_ID > 70300) {
            /** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
            $flag = FILTER_SANITIZE_ADD_SLASHES;
        } else {
            $flag = FILTER_SANITIZE_MAGIC_QUOTES;
        }

        return (string)filter_var($val, $flag);
    }

    /**
     * like htmlspecialchars(), HTML 转义字符 '"<>& 以及 ASCII 值小于 32 的字符。
     *
     * @param string $val
     * @param int    $flags 标志
     *                      FILTER_FLAG_STRIP_LOW - 去除 ASCII 值在 32 以下的字符
     *                      FILTER_FLAG_STRIP_HIGH - 去除 ASCII 值在 32 以上的字符
     *                      FILTER_FLAG_ENCODE_HIGH - 编码 ASCII 值在 32 以上的字符
     *
     * @return string
     */
    public static function specialChars($val, int $flags = 0): string
    {
        $settings = $flags !== 0 ? ['flags' => $flags] : [];

        return (string)filter_var($val, FILTER_SANITIZE_SPECIAL_CHARS, $settings);
    }

    /**
     * @param string $val
     * @param int    $flags
     *
     * @return string
     */
    public static function escape($val, $flags = 0): string
    {
        return self::specialChars($val, $flags);
    }

    /**
     *  HTML 转义字符 '"<>& 以及 ASCII 值小于 32 的字符。
     *
     * @param string $val
     * @param int    $flags 标志 FILTER_FLAG_NO_ENCODE_QUOTES
     *
     * @return string
     */
    public static function fullSpecialChars($val, int $flags = 0): string
    {
        $settings = $flags !== 0 ? ['flags' => $flags] : [];

        return (string)filter_var($val, FILTER_SANITIZE_FULL_SPECIAL_CHARS, $settings);
    }

    /**
     * 字符串长度过滤截取
     *
     * @param string  $string
     * @param integer $start
     * @param int     $length
     *
     * @return string
     */
    public static function stringCute($string, $start = 0, $length = 0): string
    {
        if (!is_string($string)) {
            return '';
        }

        return self::subStr($string, (int)$start, (int)$length);
    }

    /**
     * @param string $string
     * @param int    $start
     * @param int    $length
     *
     * @return string
     */
    public static function cut($string, $start = 0, $length = 0): string
    {
        return self::stringCute($string, $start, $length);
    }

    /**
     * url地址过滤 移除所有不符合 url 的字符
     *
     * @note 该过滤器允许所有的字母、数字以及 $-_.+!*'(),{}|\^~[]`"><#%;/?:@&=
     *
     * @param string $val 要过滤的数据
     *
     * @return string
     */
    public static function url($val): string
    {
        if (!is_string($val)) {
            return '';
        }

        return (string)filter_var($val, FILTER_SANITIZE_URL);
    }

    /**
     * email 地址过滤 移除所有不符合 email 的字符
     *
     * @param string $val 要过滤的数据
     *
     * @return string
     */
    public static function email($val): string
    {
        if (!is_string($val)) {
            return '';
        }

        return (string)filter_var($val, FILTER_SANITIZE_EMAIL);
    }

    /**
     * 不进行任何过滤，去除或编码特殊字符。这个过滤器也是FILTER_DEFAULT别名。
     * 该过滤器删除那些对应用程序有潜在危害的数据。它用于去除标签以及删除或编码不需要的字符。
     * 如果不规定标志，则该过滤器没有任何行为。
     *
     * @param string $string
     * @param int    $flags 标志
     *                      FILTER_FLAG_STRIP_LOW - 去除 ASCII 值在 32 以下的字符
     *                      FILTER_FLAG_STRIP_HIGH - 去除 ASCII 值在 32 以上的字符
     *                      FILTER_FLAG_ENCODE_LOW - 编码 ASCII 值在 32 以下的字符
     *                      FILTER_FLAG_ENCODE_HIGH - 编码 ASCII 值在 32 以上的字符
     *                      FILTER_FLAG_ENCODE_AMP - 把 & 字符编码为 &amp;
     *
     * @return string|mixed
     */
    public static function unsafeRaw($string, $flags = 0)
    {
        $settings = (int)$flags !== 0 ? ['flags' => (int)$flags] : [];

        return filter_var($string, FILTER_UNSAFE_RAW, $settings);
    }

    /**
     * 自定义回调过滤
     *
     * @param mixed    $val
     * @param callable $callback
     *
     * @return bool|mixed
     */
    public static function callback($val, $callback)
    {
        return filter_var($val, FILTER_CALLBACK, ['options' => $callback]);
    }

    /**
     * 去除数组中的重复值
     *
     * @param mixed $val
     *
     * @return array
     */
    public static function unique($val): array
    {
        if (!$val || !is_array($val)) {
            return (array)$val;
        }

        return array_unique($val);
    }
}

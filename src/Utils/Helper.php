<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-03-17
 * Time: 11:26
 */

namespace Inhere\Validate\Utils;

/**
 * Class StrHelper
 * @package Inhere\Validate\Utils
 */
class Helper
{
    const IS_TRUE  = '|yes|on|1|true|';
    const IS_FALSE = '|no|off|0|false|';
    const IS_BOOL  = '|yes|on|1|true|no|off|0|false|';

    /**
     * known image mime types
     * @link https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
     */
    public static $imgMimeTypes = [
        'bmp'  => 'image/bmp',
        'gif'  => 'image/gif',
        'ief'  => 'image/ief',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'jpe'  => 'image/jpeg',
        'png'  => 'image/png',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
    ];

    /**
     * @var array
     */
    public static $imgMimeConstants = [
        IMAGETYPE_GIF,
        IMAGETYPE_JPEG,
        IMAGETYPE_PNG,
        IMAGETYPE_BMP,
        IMAGETYPE_WBMP,
        IMAGETYPE_ICO
    ];

    /**
     * @param string $ext
     * @return string
     */
    public static function getImageMime(string $ext): string
    {
        return self::$imgMimeTypes[$ext] ?? '';
    }

    /**
     * @param string $mime
     * @return mixed|null
     */
    public static function getImageExtByMime(string $mime)
    {
        $key = \array_search($mime, self::$imgMimeTypes, true);

        return false !== $key ? self::$imgMimeTypes[$key] : null;
    }

    /**
     * @param string $file
     * @return string eg: 'image/gif'
     */
    public static function getMimeType(string $file): string
    {
        // return mime_content_type($file);
        return (string)\finfo_file(\finfo_open(FILEINFO_MIME_TYPE), $file);
    }

    /**
     * @param string $string
     * @param string $delimiter
     * @param int    $limit
     * @return array
     */
    public static function explode(string $string, string $delimiter = ',', int $limit = 0): array
    {
        $string = \trim($string, $delimiter);

        if (!\strpos($string, $delimiter)) {
            return [$string];
        }

        if ($limit < 1) {
            $list = \explode($delimiter, $string);
        } else {
            $list = \explode($delimiter, $string, $limit);
        }

        return \array_filter(\array_map('trim', $list), 'strlen');
    }

    /**
     * @param string $str
     * @param string $encoding
     * @return int
     */
    public static function strlen(string $str, string $encoding = 'UTF-8'): int
    {
        $str = \html_entity_decode($str, ENT_COMPAT, 'UTF-8');

        if (\function_exists('mb_strlen')) {
            return \mb_strlen($str, $encoding);
        }

        return \strlen($str);
    }

    /**
     * @param $str
     * @return string
     */
    public static function strToLower(string $str): string
    {
        if (\function_exists('mb_strtolower')) {
            return \mb_strtolower($str, 'utf-8');
        }

        return \strtolower($str);
    }

    /**
     * @param string $str
     * @return string
     */
    public static function strToUpper(string $str): string
    {
        if (\function_exists('mb_strtoupper')) {
            return \mb_strtoupper($str, 'utf-8');
        }

        return \strtoupper($str);
    }

    /**
     * @param string $str
     * @param int    $start
     * @param int    $length
     * @param string $encoding
     * @return bool|string
     */
    public static function subStr(string $str, int $start, int $length = null, string $encoding = 'utf-8')
    {
        $length = $length === null ? self::strlen($str) : $length;

        if (\function_exists('mb_substr')) {
            return \mb_substr($str, $start, $length, $encoding);
        }

        return \substr($str, $start, $length);
    }

    /**
     * @param        $str
     * @param        $find
     * @param int    $offset
     * @param string $encoding
     * @return bool|int
     */
    public static function strPos(string $str, $find, $offset = 0, $encoding = 'UTF-8')
    {
        if (\function_exists('mb_strpos')) {
            return \mb_strpos($str, $find, $offset, $encoding);
        }

        return \strpos($str, $find, $offset);
    }

    /**
     * @param        $str
     * @param        $find
     * @param int    $offset
     * @param string $encoding
     * @return bool|int
     */
    public static function strrpos(string $str, $find, $offset = 0, $encoding = 'utf-8')
    {
        if (\function_exists('mb_strrpos')) {
            return \mb_strrpos($str, $find, $offset, $encoding);
        }

        return \strrpos($str, $find, $offset);
    }

    /**
     * @param string $str
     * @return string
     */
    public static function ucfirst(string $str): string
    {
        return self::strToUpper(self::subStr($str, 0, 1)) . self::subStr($str, 1);
    }

    /**
     * @param string $str
     * @return string
     */
    public static function ucwords(string $str): string
    {
        if (\function_exists('mb_convert_case')) {
            return \mb_convert_case($str, MB_CASE_TITLE);
        }

        return \ucwords(self::strToLower($str));
    }

    /**
     * Translates a string with underscores into camel case (e.g. first_name -> firstName)
     * @prototype string public static function toCamelCase(string $str[, bool $capitalise_first_char = false])
     * @param      $str
     * @param bool $upperCaseFirstChar
     * @return mixed
     */
    public static function toCamelCase(string $str, $upperCaseFirstChar = false)
    {
        $str = self::strToLower($str);

        if ($upperCaseFirstChar) {
            $str = self::ucfirst($str);
        }

        return \preg_replace_callback('/_+([a-z])/', function ($c) {
            return \strtoupper($c[1]);
        }, $str);
    }

    /**
     * Transform a CamelCase string to underscore_case string
     * @param string $string
     * @param string $sep
     * @return string
     */
    public static function toSnakeCase(string $string, string $sep = '_'): string
    {
        // 'CMSCategories' => 'cms_categories'
        // 'RangePrice' => 'range_price'
        return self::strToLower(trim(\preg_replace('/([A-Z][a-z])/', $sep . '$1', $string), $sep));
    }

    /**
     * @param string $field
     * @return string
     */
    public static function prettifyFieldName(string $field): string
    {
        $str = self::toSnakeCase($field, ' ');

        return \strpos($str, '_') ? \str_replace('_', ' ', $str) : $str;
    }

    /**
     * getValueOfArray 支持以 '.' 分割进行子级值获取 eg: 'goods.apple'
     * @param  array        $array
     * @param  array|string $key
     * @param mixed         $default
     * @return mixed
     */
    public static function getValueOfArray(array $array, $key, $default = null)
    {
        if (null === $key) {
            return $array;
        }

        if (\array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (!\strpos($key, '.')) {
            return $default;
        }

        foreach (\explode('.', $key) as $segment) {
            if (\is_array($array) && \array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * @param callable|mixed $cb
     * @param array          $args
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public static function call($cb, ...$args)
    {
        if (\is_string($cb)) {
            // function
            if (\strpos($cb, '::') === false) {
                return $cb(...$args);
            }

            // ClassName/Service::method
            $cb = \explode('::', $cb, 2);
        } elseif (\is_object($cb) && \method_exists($cb, '__invoke')) {
            return $cb(...$args);
        }

        if (\is_array($cb)) {
            list($obj, $mhd) = $cb;

            return \is_object($obj) ? $obj->$mhd(...$args) : $obj::$mhd(...$args);
        }

        throw new \InvalidArgumentException('The parameter is not a callable');
    }
}

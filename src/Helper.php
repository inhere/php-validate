<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-03-17
 * Time: 11:26
 */

namespace Inhere\Validate;

use Inhere\Validate\Filter\Filters;

/**
 * Class Helper
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

    /** @var string */
    public static $fileValidators = '|file|image|mimeTypes|mimes|';

    /**
     * @param string $ext
     *
     * @return string
     */
    public static function getImageMime(string $ext): string
    {
        return self::$imgMimeTypes[$ext] ?? '';
    }

    /**
     * @param string $mime
     *
     * @return string|null
     */
    public static function getImageExtByMime(string $mime): string
    {
        $key = \array_search($mime, self::$imgMimeTypes, true);
        return (string)$key;
    }

    /**
     * @param string $file
     *
     * @return string eg: 'image/gif'
     */
    public static function getMimeType(string $file): string
    {
        if (!\file_exists($file)) {
            return '';
        }

        if (\function_exists('mime_content_type')) {
            return \mime_content_type($file);
        }

        if (\function_exists('finfo_file')) {
            return (string)\finfo_file(\finfo_open(FILEINFO_MIME_TYPE), $file);
        }

        return '';
    }

    /**
     * @param int|string|array $val
     *
     * @return int
     */
    public static function length($val): int
    {
        if (\is_int($val)) {
            return $val;
        }

        if (\is_string($val)) {
            return self::strlen($val);
        }

        return \is_array($val) ? \count($val) : -1;
    }

    /**
     * @param string $str
     * @param string $encoding
     *
     * @return int
     */
    public static function strlen(string $str, string $encoding = 'UTF-8'): int
    {
        $str = \html_entity_decode($str, \ENT_COMPAT, 'UTF-8');

        if (\function_exists('mb_strlen')) {
            return \mb_strlen($str, $encoding);
        }

        return \strlen($str);
    }

    /**
     * @param string $str
     * @param string $find
     * @param int    $offset
     * @param string $encoding
     *
     * @return bool|int
     */
    public static function strPos(string $str, $find, int $offset = 0, $encoding = 'UTF-8')
    {
        if (\function_exists('mb_strpos')) {
            return \mb_strpos($str, $find, $offset, $encoding);
        }

        return \strpos($str, $find, $offset);
    }

    /**
     * @param string $str
     * @param string $find
     * @param int    $offset
     * @param string $encoding
     *
     * @return bool|int
     */
    public static function strrpos(string $str, $find, int $offset = 0, string $encoding = 'utf-8')
    {
        if (\function_exists('mb_strrpos')) {
            return \mb_strrpos($str, $find, $offset, $encoding);
        }

        return \strrpos($str, $find, $offset);
    }

    /**
     * @param string $field
     *
     * @return string
     */
    public static function prettifyFieldName(string $field): string
    {
        $str = Filters::snakeCase($field, ' ');

        return \strpos($str, '_') ? \str_replace('_', ' ', $str) : $str;
    }

    /**
     * getValueOfArray 支持以 '.' 分割进行子级值获取 eg: 'goods.apple'
     *
     * @param array        $array
     * @param array|string $key
     * @param mixed        $default
     *
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

        $found = false;
        foreach (\explode('.', $key) as $segment) {
            if (\is_array($array) && \array_key_exists($segment, $array)) {
                $found = true;
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $found ? $array : $default;
    }

    /**
     * @param callable|mixed $cb
     * @param array          $args
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public static function call($cb, ...$args)
    {
        if (\is_string($cb)) {
            // className::method
            if (\strpos($cb, '::') > 0) {
                $cb = \explode('::', $cb, 2);
                // function
            } elseif (\function_exists($cb)) {
                return $cb(...$args);
            }
        } elseif (\is_object($cb) && \method_exists($cb, '__invoke')) {
            return $cb(...$args);
        }

        if (\is_array($cb)) {
            list($obj, $mhd) = $cb;

            return \is_object($obj) ? $obj->$mhd(...$args) : $obj::$mhd(...$args);
        }

        throw new \InvalidArgumentException('The parameter is not a callable');
    }

    /**
     * compare of size
     * - int    Compare size
     * - string Compare length
     * - array  Compare length
     *
     * @param mixed  $val
     * @param mixed  $expected
     * @param string $operator
     *
     * @return bool
     */
    public static function compareSize($val, string $operator, $expected): bool
    {
        // type must be same
        if (\gettype($val) !== \gettype($expected)) {
            return false;
        }

        // not in: int, string, array
        if (($len = self::length($val)) < 0) {
            return false;
        }

        $wantLen = self::length($expected);

        switch ($operator) {
            case '>':
                $ok = $len > $wantLen;
                break;
            case '>=':
                $ok = $len >= $wantLen;
                break;
            case '<':
                $ok = $len < $wantLen;
                break;
            case '<=':
                $ok = $len <= $wantLen;
                break;
            default:
                $ok = false;
        }

        return $ok;
    }
}

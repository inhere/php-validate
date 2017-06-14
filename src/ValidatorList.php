<?php
/**
 * @date 2015.08.04
 * 验证器列表
 * @note 验证数据; 成功则返回预期的类型， 失败返回 false
 * @description INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV  几个输入数据常量中的值在请求时即固定下来了，
 * 后期通过类似 $_GET['test']='help'; 将不会存在 输入数据常量中(INPUT_GET 没有test项)。
 */

namespace inhere\validate;

/**
 * Class ValidatorList
 * @package inhere\validate
 */
final class ValidatorList
{

/////////////////////////////// validator list ///////////////////////////////

    /**
     * 属性是否为空判断
     * @param array $data
     * @param $attr
     * @return bool
     */
    public static function isEmpty(array $data, $attr)
    {
        return empty($data[$attr]);
    }

    /**
     * 数据中是否存在
     * @param  array  $data
     * @param  string $attr
     * @return bool
     */
    public static function required($data, $attr)
    {
        return isset($data[$attr]) && $data[$attr]!=='' && $data[$attr] !== null && $data[$attr] !== [];
    }

    /**
     * int 验证
     * @param  mixed $int 要验证的变量
     * @param  array $options 可选的选项设置
     * @param  int $flags 标志
     *                    FILTER_FLAG_ALLOW_OCTAL - 允许八进制数值
     *                    FILTER_FLAG_ALLOW_HEX - 允许十六进制数值
     * @return mixed $int|false
     * @example
     * $options = [
     *    'min_range' => 0,
     *    'max_range' => 256 // 添加范围限定
     *    // 'default' => 3, // value to return if the filter fails
     * ]
     */
    public static function integer($int, array $options=[], $flags=0)
    {
        if (!is_numeric($int)) {
            return false;
        }

        $settings = [];

        if ($options) {
            $settings['options'] = $options;
        }

        if ( $flags !== 0 ) {
            $settings['flags'] = $flags;
        }

        return filter_var($int ,FILTER_VALIDATE_INT, $settings);
    }
    public static function int($int, array $options=[], $flags=0)
    {
        return self::integer($int, $options, $flags);
    }

    /**
     * check var is a integer and greater than 0
     * @param $int
     * @param array $options
     * @param int $flags
     * @return mixed
     */
    public static function number($int, array $options=[], $flags=0)
    {
        return self::integer($int, $options, $flags) && self::size($int, 1);
    }
    public static function num($int, array $options=[], $flags=0)
    {
        return self::number($int, $options, $flags);
    }

    /**
     * check var is a string
     * @param mixed $var
     * @param int $minLength
     * @param null|int $maxLength
     * @return mixed
     */
    public static function string($var, $minLength = 0, $maxLength=null)
    {
        return !is_string($var) ? false : self::length($var, $minLength, $maxLength);
    }

    /**
     * 范围检查
     * $min $max 即使传错位置也会自动调整
     *
     * @param  int|string|array $var 待检测的值。 数字检查数字范围； 字符串、数组则检查长度
     * @param  null|integer     $min 最小值
     * @param  null|int         $max 最大值
     * @return mixed
     */
    public static function size($var, $min = null, $max = null)
    {
        $options   = [];

        if (is_numeric($var)) {
            $var = (int)$var;
        } elseif (is_string($var)) {
            $var = Helper::strlen($var);
        } elseif (is_array($var)) {
            $var = count($var);
        } else {
            return false;
        }

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

        return self::integer($var, $options);
    }
    public static function range($var, $min = null, $max = null)
    {
        return self::size($var, $min, $max);
    }

    /**
     * 最小值检查
     * @param  int  $var
     * @param  integer $minRange
     * @return mixed
     */
    public static function min($var, $minRange)
    {
        return self::size($var, (int)$minRange);
    }

    /**
     * 最大值检查
     * @param  int  $var
     * @param  int  $maxRange
     * @return mixed
     */
    public static function max($var, $maxRange)
    {
        return self::size($var, null, (int)$maxRange);
    }

    /**
     * 字符串/数组长度检查
     * @param  string|array   $var         字符串/数组
     * @param  integer        $minLength   最小长度
     * @param  int            $maxLength   最大长度
     * @return mixed
     */
    public static function length($var, $minLength=0, $maxLength=null)
    {
        if (is_string($var) ) {
            $length = Helper::strlen($var);
        }elseif (is_array($var)) {
            $length = count($var);
        } else {
            return false;
        }

        return self::size($length, $minLength, $maxLength);
    }

    /**
     * 布尔值验证，把值作为布尔选项来验证。
     *   如果是 "1"、"true"、"on" 和 "yes"，则返回 TRUE。
     *   如果是 "0"、"false"、"off"、"no" 和 ""，则返回 FALSE。
     *   否则返回 NULL。
     * @param  mixed $var 要验证的数据
     * @param  mixed $default 设置验证失败时返回默认值
     * @param  int $flags 标志  FILTER_NULL_ON_FAILURE
     * @return mixed
     */
    public static function boolean($var, $default = null, $flags=0)
    {
        $settings = [];

        if ( $default !== null ) {
            $settings['options']['default'] = $default;
        }

        if ( $flags !== 0 ) {
            $settings['flags'] = $flags;
        }

        return filter_var($var ,FILTER_VALIDATE_BOOLEAN, $settings);
    }
    public static function bool($var, $default=null, $flags=0)
    {
        return self::boolean($var, $default, $flags);
    }

    /**
     * @param  mixed $var 要验证的变量
     * @param  array $options 可选的选项设置
     * $options = [
     *      'default' => 'default value',
     *      'decimal' => 2
     *  ]
     * @param  int $flags FILTER_FLAG_ALLOW_THOUSAND
     * @return mixed
     */
    public static function float($var, array $options=[], $flags=0)
    {
        $settings = [];

        if ( $options ) {
            $settings['options'] = $options;
        }

        if ( $flags !== 0 ) {
            $settings['flags'] = $flags;
        }

        return filter_var($var, FILTER_VALIDATE_FLOAT, $settings);
    }

    /**
     * 用正则验证数据
     * @param  string $var 要验证的数据
     * @param  string $regexp 正则表达式 "/^M(.*)/"
     * @param null $default
     * @return mixed
     */
    public static function regexp($var, $regexp=null, $default=null)
    {
        $options = [];

        if ( $regexp ) {
            $options['regexp'] = $regexp;
        }

        if ( $default !== null ) {
            $options['default'] = $default;
        }

        return filter_var($var ,FILTER_VALIDATE_REGEXP, ['options' => $options]);
    }
    public static function regex($var, $regexp=null)
    {
        return self::regexp($var, $regexp);
    }

    /**
     * url地址验证
     * @param  string $var 要验证的数据
     * @param  mixed $default 设置验证失败时返回默认值
     * @param  int $flags 标志
     *                    FILTER_FLAG_SCHEME_REQUIRED - 要求 URL 是 RFC 兼容 URL（比如 http://example）
     *                    FILTER_FLAG_HOST_REQUIRED - 要求 URL 包含主机名（比如 http://www.example.com）
     *                    FILTER_FLAG_PATH_REQUIRED - 要求 URL 在域名后存在路径（比如 www.example.com/example1/test2/）
     *                    FILTER_FLAG_QUERY_REQUIRED - 要求 URL 存在查询字符串（比如 "example.php?name=Peter&age=37"）
     * @return mixed
     */
    public static function url($var, $default=null, $flags=0)
    {
        $settings = [];

        if ( $default !== null ) {
            $settings['options']['default'] = $default;
        }

        if ( $flags !== 0 ) {
            $settings['flags'] = $flags;
        }

        return filter_var($var ,FILTER_VALIDATE_URL, $settings);
    }

    /**
     * email 地址验证
     * @param  string $var 要验证的数据
     * @param  mixed $default 设置验证失败时返回默认值
     * @return mixed
     */
    public static function email($var, $default=null)
    {
        $options = [];

        if ( $default !== null ) {
            $options['default'] = $default;
        }

        return filter_var($var ,FILTER_VALIDATE_EMAIL, ['options' => $options]);
    }

    /**
     * ip 地址验证
     * @param  string $var 要验证的数据
     * @param  mixed $default 设置验证失败时返回默认值
     * @param  int $flags 标志
     *                    FILTER_FLAG_IPV4 - 要求值是合法的 IPv4 IP（比如 255.255.255.255）
     *                    FILTER_FLAG_IPV6 - 要求值是合法的 IPv6 IP（比如 2001:0db8:85a3:08d3:1319:8a2e:0370:7334）
     *                    FILTER_FLAG_NO_PRIV_RANGE - 要求值不在 RFC 指定的私有范围 IP 内（比如 192.168.0.1）
     *                    FILTER_FLAG_NO_RES_RANGE - 要求值不在保留的 IP 范围内。该标志接受 IPV4 和 IPV6 值
     * @return mixed
     */
    public static function ip($var, $default=null, $flags=0)
    {
        $settings = [];

        if ( $default !== null ) {
            $settings['options']['default'] = $default;
        }

        if ( $flags !== 0 ) {
            $settings['flags'] = $flags;
        }

        return filter_var($var ,FILTER_VALIDATE_IP, $settings);
    }

    public static function callback($var, $callback)
    {
        return filter_var($var, FILTER_CALLBACK,['options' => $callback]);
    }

    /**
     * @param  mixed  $var
     * @return bool
     */
    public static function isArray($var)
    {
        return is_array($var) ? $var : false;
    }

    /**
     * @param  mixed $var
     * @param array $range
     * @return bool
     */
    public static function in($var, array $range)
    {
        return in_array($var, $range, true) ? $var : false;
    }

    /**
     * @param mixed $var
     * @param mixed $compareVar
     * @return bool
     */
    public static function compare($var, $compareVar)
    {
        return $var === $compareVar;
    }

/////////////////////////////// extension validator ///////////////////////////////

    /**
     * @param $value
     * @return bool
     */
    public static function phone($value)
    {
        return 1 === preg_match('/^1[2-9]\d{9}$/', $value);
    }

    public static function telNumber($value)
    {
        # code...
    }

    /**
     * Check for postal code validity
     *
     * @param string $postcode Postal code to validate
     * @return bool Validity is ok or not
     */
    public static function postCode($postcode)
    {
        return empty($postcode) || preg_match('/^\d{6}$/', $postcode);
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
     * Check for date format
     * @param string $date Date to validate
     * @return bool Validity is ok or not
     */
    public static function dateFormat($date)
    {
        return (bool)preg_match('/^([\d]{4})-((0?[\d])|(1[0-2]))-((0?[\d])|([1-2][\d])|(3[01]))( [\d]{2}:[\d]{2}:[\d]{2})?$/', $date);
    }

    /**
     * Check for date validity
     * @param string $date Date to validate
     * @return bool Validity is ok or not
     */
    public static function date($date)
    {
        if (!preg_match('/^([\d]{4})-((?:0?[\d])|(?:1[0-2]))-((?:0?[\d])|(?:[1-2][\d])|(?:3[01]))( [\d]{2}:[\d]{2}:[\d]{2})?$/', $date, $matches)) {
            return false;
        }

        return checkdate((int)$matches[2], (int)$matches[3], (int)$matches[1]);
    }

    /**
     * Check for a float number validity
     * @param float $float Float number to validate
     * @return bool Validity is ok or not
     */
    // public static function isFloat($float)
    // {
    //     return (string)((float)$float) === (string)$float;
    // }

    public static function isUnsignedFloat($float)
    {
        return (string)((float)$float) === (string)$float && $float >= 0;
    }

    /**
     * Check for an integer validity
     * @param int $value Integer to validate
     * @return bool Validity is ok or not
     */
    // public static function isInt($value)
    // {
    //     return ((string)(int)$value === (string)$value || $value === false);
    // }

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
     * @param string $md5 MD5 string to validate
     * @return bool Validity is ok or not
     */
    public static function md5($md5)
    {
        return preg_match('/^[a-f0-9A-F]{32}$/', $md5);
    }

    /**
     * Check for SHA1 string validity
     * @param string $sha1 SHA1 string to validate
     * @return bool Validity is ok or not
     */
    public static function sha1($sha1)
    {
        return preg_match('/^[a-fA-F0-9]{40}$/', $sha1);
    }

    /**
     * Check object validity
     * @param $color
     * @return bool Validity is ok or not
     */
    public static function color($color)
    {
        return preg_match('/^(#[0-9a-fA-F]{6}|[a-zA-Z0-9-]*)$/', $color);
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

        return true;
    }

    /**
     * Check for standard name file validity
     *
     * @param string $name Name to validate
     * @return bool Validity is ok or not
     */
    public static function fileName($name)
    {
        return preg_match('/^[a-zA-Z0-9_.-]+$/', $name);
    }

    /**
     * Check for standard name directory validity
     *
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
    public static function input($type, $varName , $filter, array $options=[])
    {
    }

    public static function multi(array $data, array $filters=[])
    {
    }

    /**
     * @link http://php.net/manual/zh/function.filter-input-array.php
     * 检查(验证/过滤)输入数据中的多个变量名 like filter_input_array()
     * 当需要获取很多变量却不想重复调用 filter_input()时很有用。
     * @param  int $type One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV. 要检查的输入数据
     * @param  mixed  $definition 一个定义参数的数组。
     *                            一个有效的键必须是一个包含变量名的string，
     *                            一个有效的值要么是一个filter type，或者是一个array 指明了过滤器、标示和选项。
     *                            如果值是一个数组，那么它的有效的键可以是 :
     *                                filter， 用于指明 filter type，
     *                                flags 用于指明任何想要用于过滤器的标示，
     *                                options 用于指明任何想要用于过滤器的选项。
     *                            参考下面的例子来更好的理解这段说明。
     * @param  bool  $addEmpty 在返回值中添加 NULL 作为不存在的键。
     * 如果成功的话返回一个所请求的变量的数组，
     * 如果失败的话返回 FALSE 。
     * 对于数组的值，
     *     如果过滤失败则返回 FALSE ，
     *     如果 variable_name 不存在的话则返回 NULL 。
     * 如果标示 FILTER_NULL_ON_FAILURE 被使用了，那么当变量不存在时返回 FALSE ，当过滤失败时返回 NULL 。
     */
    public static function inputMulti($type, $definition, $addEmpty=true)
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

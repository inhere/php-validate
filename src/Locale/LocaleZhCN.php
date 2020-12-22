<?php declare(strict_types=1);

namespace Inhere\Validate\Locale;

use Inhere\Validate\Validator\GlobalMessage;

/**
 * Class LocaleZhCN
 *
 * @package Inhere\Validate\Locale
 */
class LocaleZhCN
{
    /**
     * Default error messages
     *
     * @var array
     */
    public static $messages = [
        // 'int' 'integer'
        'integer'    => [
            '{attr} 必须是整数!',
            '{attr} 必须是整数并且最小值是 {min}',
            '{attr} 必须是整数并且值的范围必须在 {min} ~ {max}',
        ],
        // 'num'
        'number'     => [
            '{attr} 必须是整数并且大于0!',
            '{attr} 必须是整数并且最小值是 {min}',
            '{attr} 必须是整数并且值的范围必须在 {min} ~ {max}',
        ],
        // 'bool', 'boolean',
        'boolean'    => '{attr} 必须是布尔类型!',
        'float'      => '{attr} 必须是浮点数!',
        'url'        => '{attr} is not a url address!',
        'email'      => '{attr} is not a email address!',
        'date'       => '{attr} is not a date format!',
        'dateFormat' => '{attr} is not in a {value0} date format!',
        'ip'         => '{attr} is not IP address!',
        'ipv4'       => '{attr} is not a IPv4 address!',
        'ipv6'       => '{attr} is not a IPv6 address!',
        'required'   => 'parameter {attr} is required!',
        'requiredIf' => 'parameter {attr} is required!',
        'length'     => [
            '{attr} length validation is not through!',
            '{attr} must be an string/array and minimum length is {min}',
            '{attr} must be an string/array and length range {min} ~ {max}',
        ],
        // 'range', 'between'
        'size'       => [
            '{attr} size validation is not through!',
            '{attr} must be an integer/string/array and minimum value/length is {min}',
            // '{attr} must be an integer/string/array and value/length range {min} ~ {max}',
            '{attr} must be in the range {min} ~ {max}',
        ],

        // 'lengthEq', 'sizeEq'
        'fixedSize'  => '{attr} length must is {value0}',

        'eq'       => '{attr} must be equals to {value0}',
        // 'different'
        'ne'       => '{attr} can not be equals to {value0}',
        'min'      => '{attr} minimum boundary is {value0}',
        'max'      => '{attr} maximum boundary is {value0}',
        'lt'       => '{attr} value must be less than {value0}',
        'lte'      => '{attr} value must be less than or equals to {value0}',
        'gt'       => '{attr} value must be greater than or equals to {value0}',
        'gte'      => '{attr} value must be greater than or equals to {value0}',

        // field compare
        'eqField'  => '{attr} value must be less than {value0}',
        'neqField' => '{attr} value must be less than {value0}',
        'ltField'  => '{attr} value must be less than {value0}',
        'lteField' => '{attr} value must be less than or equals to {value0}',
        'gtField'  => '{attr} value must be greater than {value0}',
        'gteField' => '{attr} value must be greater than or equals to {value0}',

        // 'in', 'enum',
        'enum'     => '{attr} must in ({value0})',
        'notIn'    => '{attr} cannot in ({value0})',

        'string' => [
            '{attr} must be a string',
            '{attr} must be a string and minimum length be {min}',
            '{attr} must be a string and length range must be {min} ~ {max}',
        ],

        // 'regex', 'regexp',
        'regexp' => '{attr} does not match the {value0} conditions',

        'mustBe' => '{attr} must be equals to {value0}',
        'notBe'  => '{attr} can not be equals to {value0}',

        'compare' => '{attr} must be equals to {value0}',
        'same'    => '{attr} must be equals to {value0}',

        'isArray' => '{attr} must be an array',
        'isMap'   => '{attr} must be an array and is key-value format',
        'isList'  => '{attr} must be an array of nature',
        'intList' => '{attr} must be an array and value is all integers',
        'numList' => '{attr} must be an array and value is all numbers',
        'strList' => '{attr} must be an array and value is all strings',
        'arrList' => '{attr} must be an array and value is all arrays',

        'each'     => '{attr} each value must be through the "{value0}" verify',
        'hasKey'   => '{attr} must be contains the key {value0}',
        'distinct' => 'there should not be duplicate keys in the {attr}',

        'json' => '{attr} must be an json string',

        'file'  => '{attr} must be an uploaded file',
        'image' => '{attr} must be an uploaded image file',

        'callback'  => '{attr} don\'t pass the test and verify!',
        '__default' => '{attr} validation is not through!',
    ];

    /**
     * register to global message
     */
    public static function register(): void
    {
        GlobalMessage::setMessages(self::$messages);
    }
}

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
    public static array $messages = [
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
        'url'        => '{attr} 不是url地址！',
        'email'      => '{attr} 不是电子邮件地址！',
        'date'       => '{attr} 不是日期格式！',
        'dateFormat' => '{attr} is not in a {value0} date format!',
        'ip'         => '{attr} 不是IP地址！',
        'ipv4'       => '{attr} 不是 IPv4 地址！',
        'ipv6'       => '{attr} 不是IPv6地址！',
        'required'   => '字段 {attr} 是必须的！',
        'requiredIf' => '字段 {attr} 是必须的！',
        'length'     => [
            '{attr} 长度验证未通过！',
            '{attr} 必须是一个字符串/数组，最小长度是 {min}',
            '{attr} 值不合法,长度范围只允许是 {min} ~ {max}',
        ],
        // 'range', 'between'
        'size'       => [
            '{attr} size validation is not through!',
            '{attr} 值不合法，最小是 {min}',
            // '{attr} must be an integer/string/array and value/length range {min} ~ {max}',
            '{attr} 值不合法，大小范围只允许是 {min} ~ {max}',
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
        'gt'       => '{attr} value must be greater than {value0}',
        'gte'      => '{attr} value must be greater than or equals to {value0}',

        // field compare
        'eqField'  => '{attr} 值必须等于 {value0} 的值',
        'neqField' => '{attr} 值不能等于 {value0} 的值',
        'ltField'  => '{attr} 值必须小于 {value0} 的值',
        'lteField' => '{attr} 值必须小于或等于 {value0} 的值',
        'gtField'  => '{attr} 值必须大于 {value0} 的值',
        'gteField' => '{attr} 值必须大于或等于 {value0} 的值',

        // 'in', 'enum',
        'enum'     => '{attr} must in ({value0})',
        'notIn'    => '{attr} cannot in ({value0})',

        'string' => [
            '{attr} 必须是字符串',
            '{attr} 必须是字符串并且最小长度为 {min}',
            '{attr} 必须是字符串并且长度范围必须是 {min} ~ {max}',
        ],

        // 'regex', 'regexp',
        'regexp' => '{attr} does not match the {value0} conditions',

        'mustBe' => '{attr} must be equals to {value0}',
        'notBe'  => '{attr} can not be equals to {value0}',

        'compare' => '{attr} must be equals to {value0}',
        'same'    => '{attr} must be equals to {value0}',

        'isArray' => '{attr} 必须是数组',
        'isMap'   => '{attr} 必须是数组并且是键-值对格式',
        'isList'  => '{attr} 必须是自然数组',
        'intList' => '{attr} 必须是一个数组并且值都是整数',
        'numList' => '{attr} 必须是一个数组并且值都是大于0的数字',
        'strList' => '{attr} 必须是一个数组并且值都是字符串',
        'arrList' => '{attr} 必须是一个数组，并且值也都是数组',

        'each'     => '{attr} each value must be through the "{value0}" verify',
        'hasKey'   => '{attr} 必须包含键字段 {value0}',
        'distinct' => '{attr} 中不应该有重复的值',

        'json' => '{attr} 必须是 json 字符串',

        'file'  => '{attr} 必须是上传的文件',
        'image' => '{attr} 必须是上传的图片文件',

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

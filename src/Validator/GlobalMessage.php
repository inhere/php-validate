<?php declare(strict_types=1);

namespace Inhere\Validate\Validator;

/**
 * Class GlobalMessage
 * - global error message storage
 */
final class GlobalMessage
{
    /**
     * Default error messages
     *
     * @var array
     */
    public static $messages = [
        // 'int' 'integer'
        'integer'    => [
            '{attr} must be an integer!',
            '{attr} must be an integer and minimum value is {min}',
            '{attr} must be an integer and value range {min} ~ {max}',
        ],
        // 'num'
        'number'     => [
            '{attr} must be an integer greater than 0!',
            '{attr} must be an integer and minimum value is {min}',
            '{attr} must be an integer and in the range {min} ~ {max}',
        ],
        // 'bool', 'boolean',
        'boolean'    => '{attr} must be an boolean!',
        'float'      => '{attr} must be an float!',
        'url'        => '{attr} must be a URL address!',
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
        'ne'       => '{attr} cannot be equals to {value0}',
        'min'      => '{attr} minimum boundary is {value0}',
        'max'      => '{attr} maximum boundary is {value0}',
        'lt'       => '{attr} value must be less than {value0}',
        'lte'      => '{attr} value must be less than or equals to {value0}',
        'gt'       => '{attr} value must be greater than {value0}',
        'gte'      => '{attr} value must be greater than or equals to {value0}',

        // field compare
        'eqField'  => '{attr} value must be equals to {value0}',
        'neqField' => '{attr} value cannot be equals to {value0}',
        'ltField'  => '{attr} value must be less than {value0}',
        'lteField' => '{attr} value must be less than or equals to {value0}',
        'gtField'  => '{attr} value must be greater than {value0}',
        'gteField' => '{attr} value must be greater than or equals to {value0}',
        'inField'  => '{attr} value must be exists in {value0}',

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
     * @param string $key
     *
     * @return string|array
     */
    public static function get(string $key)
    {
        return self::$messages[$key] ?? '';
    }

    /**
     * @param string       $key
     * @param string|array $msg
     */
    public static function set(string $key, $msg): void
    {
        if ($key && $msg) {
            self::$messages[$key] = $msg;
        }
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public static function has(string $key): bool
    {
        return isset(self::$messages[$key]);
    }

    /**
     * @return string
     */
    public static function getDefault(): string
    {
        return self::$messages['__default'];
    }

    /**
     * @param array $messages
     */
    public static function setMessages(array $messages): void
    {
        foreach ($messages as $key => $value) {
            self::set($key, $value);
        }
    }

    /**
     * @return array
     */
    public static function getMessages(): array
    {
        return self::$messages;
    }
}

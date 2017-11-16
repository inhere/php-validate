<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-03-17
 * Time: 11:26
 */

namespace Inhere\Validate\Utils;

/**
 * Class ErrorMessage
 * @package Inhere\Validate\Utils
 */
class ErrorMessage
{
    /**
     * 默认的错误提示信息
     * @var array
     */
    public static $messages = [
        'int' => '{attr} must be an integer!',
        'integer' => '{attr} must be an integer!',
        'num' => '{attr} must be an integer greater than 0!',
        'number' => '{attr} must be an integer greater than 0!',
        'bool' => '{attr} must be is boolean!',
        'boolean' => '{attr} must be is boolean!',
        'float' => '{attr} must be is float!',
        'url' => '{attr} is not a url address!',
        'email' => '{attr} is not a email address!',
        'date' => '{attr} is not a date format!',
        'dateFormat' => '{attr} is not in a {value0} date format !',
        'ip' => '{attr} is not IP address!',
        'ipv4' => '{attr} is not a IPv4 address!',
        'ipv6' => '{attr} is not a IPv6 address!',
        'required' => 'parameter {attr} is required!',
        'length' => [
            '{attr} length validation is not through!',
            '{attr} must be an string/array and minimum length is {min}',
            '{attr} must be an string/array and length range {min} ~ {max}',
        ],
        'size' => [
            '{attr} size validation is not through!',
            '{attr} must be an integer/string/array and minimum value/length is {min}',
            '{attr} must be an integer/string/array and value/length range {min} ~ {max}',
        ],
        'range' => [
            '{attr} range validation is not through!',
            '{attr} must be an integer/string/array and minimum value/length is {min}',
            '{attr} must be an integer/string/array and value/length range {min} ~ {max}',
        ],
        'min' => '{attr} minimum boundary is {value0}',
        'max' => '{attr} maximum boundary is {value0}',
        'in' => '{attr} must in ({value0})',
        'notIn' => '{attr} cannot in ({value0})',
        'string' => [
            '{attr} must be a string',
            '{attr} must be a string and minimum length be {min}',
            '{attr} must be a string and length range must be {min} ~ {max}',
        ],
        'regexp' => '{attr} does not match the {value0} conditions',
        'compare' => '{attr} must be equals to {value0}',
        'same' => '{attr} must be equals to {value0}',
        'isArray' => '{attr} must be an array',
        'isMap' => '{attr} must be an array and is key-value format',
        'isList' => '{attr} must be an array of nature',
        'intList' => '{attr} must be an array and value is all integers',
        'strList' => '{attr} must be an array and value is all strings',
        'json' => '{attr} must be an json string',
        'callback' => '{attr} don\'t pass the test and verify!',
        '_' => '{attr} validation is not through!',
    ];
}

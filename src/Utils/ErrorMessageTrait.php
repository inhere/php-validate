<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-03-17
 * Time: 11:26
 */

namespace Inhere\Validate\Utils;

/**
 * trait ErrorMessageTrait
 * @package Inhere\Validate\Utils
 */
trait ErrorMessageTrait
{
    /**
     * @var array
     */
    private static $validatorAliases = [
        'int' => 'integer',
        'num' => 'number',
        'bool' => 'boolean',
        'in' => 'enum',
        'range' => 'size',
        'between' => 'size',
        'lengthEq' => 'fixedSize',
        'sizeEq' => 'fixedSize',
        'different' => 'notEqual',
    ];

    /**
     * 默认的错误提示信息
     * @var array
     */
    public static $messages = [
        // 'int' 'integer'
        'integer' => '{attr} must be an integer!',
        // 'num'
        'number' => [
            '{attr} must be an integer greater than 0!',
            '{attr} must be an integer and minimum value is {min}',
            '{attr} must be an integer and in the range {min} ~ {max}',
       ],
        // 'bool', 'boolean',
        'boolean' => '{attr} must be is boolean!',
        'float' => '{attr} must be is float!',
        'url' => '{attr} is not a url address!',
        'email' => '{attr} is not a email address!',
        'date' => '{attr} is not a date format!',
        'dateFormat' => '{attr} is not in a {value0} date format!',
        'ip' => '{attr} is not IP address!',
        'ipv4' => '{attr} is not a IPv4 address!',
        'ipv6' => '{attr} is not a IPv6 address!',
        'required' => 'parameter {attr} is required!',
        'length' => [
            '{attr} length validation is not through!',
            '{attr} must be an string/array and minimum length is {min}',
            '{attr} must be an string/array and length range {min} ~ {max}',
        ],
        // 'range', 'between'
        'size' => [
            '{attr} size validation is not through!',
            '{attr} must be an integer/string/array and minimum value/length is {min}',
            // '{attr} must be an integer/string/array and value/length range {min} ~ {max}',
            '{attr} must be in the range {min} ~ {max}',
        ],

        // 'lengthEq', 'sizeEq'
        'fixedSize' => '{attr} length must is {value0}',

        'min' => '{attr} minimum boundary is {value0}',
        'max' => '{attr} maximum boundary is {value0}',

        // 'in', 'enum',
        'enum' => '{attr} must in ({value0})',
        'notIn' => '{attr} cannot in ({value0})',

        'string' => [
            '{attr} must be a string',
            '{attr} must be a string and minimum length be {min}',
            '{attr} must be a string and length range must be {min} ~ {max}',
        ],

        // 'regex', 'regexp',
        'regexp' => '{attr} does not match the {value0} conditions',

        'mustBe' => '{attr} must be equals to {value0}',
        'notBe' => '{attr} can not be equals to {value0}',

        'compare' => '{attr} must be equals to {value0}',
        'same' => '{attr} must be equals to {value0}',
        'equal' => '{attr} must be equals to {value0}',

        // 'different'
        'notEqual' => '{attr} can not be equals to {value0}',

        'isArray' => '{attr} must be an array',
        'isMap' => '{attr} must be an array and is key-value format',
        'isList' => '{attr} must be an array of nature',
        'intList' => '{attr} must be an array and value is all integers',
        'numList' => '{attr} must be an array and value is all numbers',
        'strList' => '{attr} must be an array and value is all strings',
        'arrList' => '{attr} must be an array and value is all arrays',

        'each' => '{attr} must be through the {value0} verify',
        'hasKey' => '{attr} must be contains the key {value0}',
        'distinct' => 'there should not be duplicate keys in the {attr}',

        'json' => '{attr} must be an json string',

        'file' => '{attr} must be an uploaded file',
        'image' => '{attr} must be an uploaded image file',

        'callback' => '{attr} don\'t pass the test and verify!',
        '_' => '{attr} validation is not through!',
    ];

    /**
     * attribute field translate list
     * @var array
     */
    private $_translates = [];

    /**
     * 保存所有的验证错误信息
     * @var array[]
     * [
     *     ['name' => 'field', 'msg' => 'error Message1' ],
     *     ['name' => 'field2', 'msg' => 'error Message2' ],
     * ]
     */
    private $_errors = [];

    /**
     * Whether there is error stop validation 是否出现验证失败就立即停止验证
     * True  -- 出现一个验证失败即停止验证,并退出
     * False -- 全部验证并将错误信息保存到 {@see $_errors}
     * @var boolean
     */
    private $_stopOnError = true;

    /*******************************************************************************
     * Errors Information
     ******************************************************************************/

    /**
     * 是否有错误
     * @return boolean
     */
    public function hasError(): bool
    {
        return $this->isFail();
    }

    /**
     * @return bool
     */
    public function isFail(): bool
    {
        return \count($this->_errors) > 0;
    }

    /**
     * @return bool
     */
    public function fail(): bool
    {
        return $this->isFail();
    }

    /**
     * @return bool
     */
    public function failed(): bool
    {
        return $this->isFail();
    }

    /**
     * @return bool
     */
    public function ok(): bool
    {
        return !$this->isFail();
    }

    /**
     * @return bool
     */
    public function isOk(): bool
    {
        return !$this->isFail();
    }

    /**
     * @deprecated will delete
     * @return bool
     */
    public function passed(): bool
    {
        return !$this->isFail();
    }

    /**
     * @return bool
     */
    public function isPassed(): bool
    {
        return !$this->isFail();
    }

    /**
     * check field whether in the errors
     * @param string $field
     * @return bool
     */
    public function inError(string $field): bool
    {
        foreach ($this->_errors as $item) {
            if ($field === $item['name']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $field
     * @param string $msg
     */
    public function addError(string $field, string $msg)
    {
        $this->_errors[] = [
            'name' => $field,
            'msg' => $msg,
        ];
    }

    /**
     * @param string|null $field Only get errors of the field.
     * @return array
     */
    public function getErrors(string $field = null): array
    {
        if ($field) {
            $errors = [];

            foreach ($this->_errors as $item) {
                if ($field === $item['name']) {
                    $errors[] = $item['msg'];
                }
            }

            return $errors;
        }

        return $this->_errors;
    }

    /**
     * @return $this
     */
    public function clearErrors()
    {
        $this->_errors = [];

        return $this;
    }

    /**
     * 得到第一个错误信息
     * @author inhere
     * @param bool $onlyMsg
     * @return array|string
     */
    public function firstError($onlyMsg = true)
    {
        $e = $this->_errors;
        $first = array_shift($e);

        return $onlyMsg ? $first['msg'] : $first;
    }

    /**
     * 得到最后一个错误信息
     * @author inhere
     * @param bool $onlyMsg
     * @return array|string
     */
    public function lastError($onlyMsg = true)
    {
        $e = $this->_errors;
        $last = array_pop($e);

        return $onlyMsg ? $last['msg'] : $last;
    }

    /**
     * @param bool|null $stopOnError
     * @return $this
     */
    public function setStopOnError($stopOnError = null)
    {
        if (null !== $stopOnError) {
            $this->_stopOnError = (bool)$stopOnError;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isStopOnError(): bool
    {
        return $this->_stopOnError;
    }

    /*******************************************************************************
     * Error Messages
     ******************************************************************************/

    /**
     * @return array
     */
    public static function getDefaultMessages(): array
    {
        return self::$messages;
    }

    /**
     * @param string $key
     * @param string $msg
     */
    public static function setDefaultMessage(string $key, $msg)
    {
        if ($key && $msg) {
            self::$messages[$key] = $msg;
        }
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return array_merge(self::getDefaultMessages(), $this->messages());
    }

    /**
     * @param array $messages
     * @return $this
     */
    public function setMessages(array $messages)
    {
        foreach ($messages as $key => $value) {
            self::setDefaultMessage($key, $value);
        }

        return $this;
    }

    /**
     * 各个验证器的提示消息
     * @author inhere
     * @date   2015-09-27
     * @param  string|\Closure $validator 验证器
     * @param  string $field
     * @param  array $args
     * @param  string|array $message 自定义提示消息
     * @return string
     */
    public function getMessage($validator, $field, array $args = [], $message = null)
    {
        $rawName = \is_string($validator) ? $validator : 'callback';
        $validator = self::getValidatorName($rawName);

        // get message from default dict.
        if (!$message) {
            // allow define a message for a validator. eg: 'username.required' => 'some message ...'
            $fullKey = $field . '.' . $rawName;
            $messages = $this->getMessages();

            if (isset($messages[$fullKey])) {
                $message = $messages[$fullKey];
            } elseif (isset($messages[$rawName])) {
                $message = $messages[$rawName];
            } else {
                $message = $messages[$validator] ?? $messages['_'];
            }

            // is array. It's defined multi error messages
        } elseif (\is_array($message) && isset($message[$rawName])) {
            $message = $message[$rawName];
        }

        if (\is_string($message) && false === strpos($message, '{')) {
            return $message;
        }

        $params = [
            '{attr}' => $this->getTranslate($field)
        ];

        foreach ($args as $key => $value) {
            $key = \is_int($key) ? "value{$key}" : $key;
            $params['{' . $key . '}'] = \is_array($value) ? implode(',', $value) : $value;
        }

        // @see self::$messages['size']
        if (\is_array($message)) {
            $msgKey = \count($params) - 1;
            $message = $message[$msgKey] ?? $message[0];
        }

        return strtr($message, $params);
    }


    /**
     * set the attrs translation data
     * @param array $fieldTrans
     * @return $this
     */
    public function setTranslates(array $fieldTrans)
    {
        $this->_translates = $fieldTrans;

        return $this;
    }

    /**
     * @return array
     */
    public function getTranslates(): array
    {
        static $translates;

        if (!$translates) {
            $translates = array_merge($this->translates(), $this->_translates);
        }

        return $translates;
    }

    /**
     * @param string $field
     * @return string
     */
    public function getTranslate(string $field): string
    {
        $trans = $this->getTranslates();

        return $trans[$field] ?? Helper::beautifyFieldName($field);
    }

    /**
     * @param string $validator
     * @return string
     */
    public static function getValidatorName(string $validator)
    {
        return self::$validatorAliases[$validator] ?? $validator;
    }

    /**
     * @return array
     */
    public static function getValidatorAliases(): array
    {
        return self::$validatorAliases;
    }
}

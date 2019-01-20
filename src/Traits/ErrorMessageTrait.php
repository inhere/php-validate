<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-03-17
 * Time: 11:26
 */

namespace Inhere\Validate\Traits;

use Inhere\Validate\Helper;
use Inhere\Validate\Validator\Messages;
use Inhere\Validate\Validators;

/**
 * trait ErrorMessageTrait
 * @author inhere
 * @package Inhere\Validate\Traits
 */
trait ErrorMessageTrait
{
    /**
     * error messages map
     * @var array
     */
    private $_messages = [];

    /**
     * attribute field translate list
     * @var array
     */
    private $_translates = [];

    /**
     * Save all validation error messages
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

    /**
     * prettify field name on get error message
     * @var bool
     */
    private $_prettifyName = true;

    /*******************************************************************************
     * Errors Information
     ******************************************************************************/

    /**
     * @return bool
     */
    protected function shouldStop(): bool
    {
        return $this->isFail() && $this->_stopOnError;
    }

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
            'msg'  => $msg,
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
    public function clearErrors(): self
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
        $errors = $this->_errors;
        $first  = \array_shift($errors);

        return $onlyMsg ? $first['msg'] : $first;
    }

    /**
     * 得到最后一个错误信息
     * @param bool $onlyMsg
     * @return array|string
     */
    public function lastError($onlyMsg = true)
    {
        $e    = $this->_errors;
        $last = \array_pop($e);

        return $onlyMsg ? $last['msg'] : $last;
    }

    /**
     * @param bool|null $_stopOnError
     * @return $this
     */
    public function setStopOnError($_stopOnError = null): self
    {
        if (null !== $_stopOnError) {
            $this->_stopOnError = (bool)$_stopOnError;
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

    protected function prepareValidation()
    {
        $this->_translates = \array_merge($this->translates(), $this->_translates);
        // error message
        $this->_messages = \array_merge($this->messages(), $this->_messages);
    }

    /*******************************************************************************
     * Error Messages
     ******************************************************************************/

    /**
     * @param string       $key
     * @param string|array $message
     */
    public function setMessage(string $key, $message)
    {
        if ($key && $message) {
            $this->_messages[$key] = $message;
        }
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->_messages;
    }

    /**
     * @param array $messages
     * @return $this
     */
    public function setMessages(array $messages): self
    {
        foreach ($messages as $key => $value) {
            $this->setMessage($key, $value);
        }

        return $this;
    }

    /**
     * 各个验证器的提示消息
     * @param  string|\Closure $validator 验证器
     * @param  string          $field
     * @param  array           $args
     * @param  string|array    $message 自定义提示消息
     * @return string
     */
    public function getMessage($validator, string $field, array $args = [], $message = null): string
    {
        $rawName = \is_string($validator) ? $validator : 'callback';
        $params  = [
            '{attr}' => $this->getTranslate($field)
        ];

        // get message from built in dict.
        if (!$message) {
            $message = $this->findMessage($field, $rawName) ?: Messages::getDefault();
            // is array. It's defined multi error messages
        } elseif (\is_array($message)) {
            $message = $message[$rawName] ?? $this->findMessage($field, $rawName);

            if (!$message) { // use default
                return \strtr(Messages::getDefault(), $params);
            }
        } else {
            $message = (string)$message;
        }

        /** @see Messages::$messages['size'] */
        if (\is_array($message)) {
            $msgKey  = \count($params) - 1;
            $message = $message[$msgKey] ?? $message[0];
        }

        if (\is_string($message) && false === \strpos($message, '{')) {
            return $message;
        }

        foreach ($args as $key => $value) {
            $key = \is_int($key) ? "value{$key}" : $key;
            // build params
            $params['{' . $key . '}'] = \is_array($value) ? \implode(',', $value) : $value;
        }

        return \strtr($message, $params);
    }

    /**
     * @param string $field
     * @param string $rawName
     * @return string|array
     */
    protected function findMessage(string $field, string $rawName)
    {
        // allow define a message for a validator.
        // eg: 'username.required' => 'some message ...'
        $fullKey  = $field . '.' . $rawName;
        $realName = Validators::getRealName($rawName);

        if (isset($this->_messages[$fullKey])) {
            $message = $this->_messages[$fullKey];
            // eg 'required' => 'some message ...'
        } elseif (isset($this->_messages[$rawName])) {
            $message = $this->_messages[$rawName];
        } elseif (isset($this->_messages[$realName])) {
            $message = $this->_messages[$realName];
        } else { // get from default
            $message = Messages::get($realName);
        }

        return $message;
    }

    /**
     * set the attrs translation data
     * @param array $fieldTrans
     * @return $this
     */
    public function setTranslates(array $fieldTrans): self
    {
        $this->_translates = $fieldTrans;
        return $this;
    }

    /**
     * add the attrs translation data
     * @param array $fieldTrans
     * @return $this
     */
    public function addTranslates(array $fieldTrans): self
    {
        $this->_translates = \array_merge($this->_translates, $fieldTrans);
        return $this;
    }

    /**
     * @return array
     */
    public function getTranslates(): array
    {
        return $this->_translates;
    }

    /**
     * get field translate string.
     * @param string $field
     * @return string
     */
    public function getTranslate(string $field): string
    {
        $trans = $this->getTranslates();

        if (isset($trans[$field])) {
            return $trans[$field];
        }

        if ($this->_prettifyName) {
            return Helper::prettifyFieldName($field);
        }

        return $field;
    }

    /**
     * @return bool
     */
    public function isPrettifyName(): bool
    {
        return $this->_prettifyName;
    }

    /**
     * @param bool $prettifyName
     */
    public function setPrettifyName(bool $prettifyName): void
    {
        $this->_prettifyName = $prettifyName;
    }
}

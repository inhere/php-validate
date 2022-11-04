<?php declare(strict_types=1);

namespace Inhere\Validate\Traits;

use Inhere\Validate\Helper;
use Inhere\Validate\Validator\GlobalMessage;
use Inhere\Validate\Validators;
use function array_merge;
use function array_pop;
use function array_shift;
use function count;
use function implode;
use function is_array;
use function is_int;
use function is_string;
use function strtr;

/**
 * trait ErrorMessageTrait
 *
 * @author  inhere
 * @package Inhere\Validate\Traits
 */
trait ErrorMessageTrait
{
    /**
     * error messages map
     *
     * @var array
     */
    private array $_messages = [];

    /**
     * attribute field translate list
     *
     * @var array
     */
    private array $_translates = [];

    /**
     * Save all validation error messages
     *
     *  [
     *      ['name' => 'field', 'msg' => 'error Message1' ],
     *      ['name' => 'field2', 'msg' => 'error Message2' ],
     *  ]
     *
     * @var array[]
     */
    private array $_errors = [];

    /**
     * Whether there is error stop validation 是否出现验证失败就立即停止验证
     * True  -- 出现一个验证失败即停止验证,并退出
     * False -- 全部验证并将错误信息保存到 {@see $_errors}
     *
     * @var boolean
     */
    private bool $_stopOnError = true;

    /**
     * Prettify field name on get error message
     *
     * @var bool
     */
    private bool $_prettifyName = true;

    protected function prepareValidation(): void
    {
        // error message
        $this->_messages = array_merge($this->messages(), $this->_messages);
        // field translate
        $this->_translates = array_merge($this->translates(), $this->_translates);
    }

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
     * Is there an error?
     *
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
        return count($this->_errors) > 0;
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
     * @deprecated will delete, please use isOk() or isPassed() instead
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
     * @return bool
     * @deprecated will delete, please use isOk() or isPassed() instead
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
     *
     * @param string $field
     *
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
    public function addError(string $field, string $msg): void
    {
        $this->_errors[] = [
            'name' => $field,
            'msg'  => $msg,
        ];
    }

    /**
     * @param string $field Only get errors of the field.
     *
     * @return array
     */
    public function getErrors(string $field = ''): array
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
     * clear errors
     */
    public function clearErrors(): void
    {
        $this->_errors = [];
    }

    /**
     * Get the first error message
     *
     * @param bool $onlyMsg
     *
     * @return array|string
     */
    public function firstError(bool $onlyMsg = true): array|string
    {
        if (!$errors = $this->_errors) {
            return $onlyMsg ? '' : [];
        }

        $first = array_shift($errors);
        return $onlyMsg ? $first['msg'] : $first;
    }

    /**
     * Get the last error message
     *
     * @param bool $onlyMsg
     *
     * @return array|string
     */
    public function lastError(bool $onlyMsg = true): array|string
    {
        if (!$errors = $this->_errors) {
            return $onlyMsg ? '' : [];
        }

        $last = array_pop($errors);
        return $onlyMsg ? $last['msg'] : $last;
    }

    /**
     * @param mixed|null $_stopOnError
     *
     * @return static
     */
    public function setStopOnError(mixed $_stopOnError = null): static
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

    /*******************************************************************************
     * Error Messages
     ******************************************************************************/

    /**
     * @param string       $key
     * @param array|string $message
     */
    public function setMessage(string $key, array|string $message): void
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
     *
     * @return static
     */
    public function setMessages(array $messages): static
    {
        foreach ($messages as $key => $value) {
            $this->setMessage($key, $value);
        }
        return $this;
    }

    /**
     * 各个验证器的提示消息
     *
     * @param callable|string $validator 验证器
     * @param string            $field
     * @param array             $args
     * @param array|string|null $message   自定义提示消息
     *
     * @return string
     */
    public function getMessage(callable|string $validator, string $field, array $args = [], array|string $message = null): string
    {
        $rawName = is_string($validator) ? $validator : 'callback';
        $params  = [
            '{attr}' => $this->getTranslate($field)
        ];

        // get message from built in dict.
        if (!$message) {
            $message = $this->findMessage($field, $rawName) ?: GlobalMessage::getDefault();
            // is array. It's defined multi error messages
        } elseif (is_array($message)) {
            $message = $message[$field] ?? $message[$rawName] ?? $this->findMessage($field, $rawName);

            if (!$message) { // use default
                return strtr(GlobalMessage::getDefault(), $params);
            }
        }

        /** @see GlobalMessage::$messages['size'] */
        if (is_array($message)) {
            $msgKey  = count($args);
            $message = $message[$msgKey] ?? $message[0];
        }

        if (!str_contains($message, '{')) {
            return $message;
        }

        foreach ($args as $key => $value) {
            $key = is_int($key) ? "value$key" : $key;
            // build params
            $params['{' . $key . '}'] = is_array($value) ? implode(',', $value) : $value;
        }

        return strtr($message, $params);
    }

    /**
     * @param string $field
     * @param string $rawName
     *
     * @return string|array
     */
    protected function findMessage(string $field, string $rawName): array|string
    {
        // allow define a message for a validator.
        // eg: 'username.required' => 'some message ...'
        $fullKey  = $field . '.' . $rawName;
        $realName = Validators::realName($rawName);

        // get from default
        if (!$this->_messages) {
            return GlobalMessage::get($realName);
        }

        if (isset($this->_messages[$fullKey])) {
            $message = $this->_messages[$fullKey];
            // eg 'required' => 'some message ...'
        } elseif (isset($this->_messages[$rawName])) {
            $message = $this->_messages[$rawName];
        } elseif (isset($this->_messages[$realName])) {
            $message = $this->_messages[$realName];
        } else { // get from default
            $message = GlobalMessage::get($realName);
        }

        return $message;
    }

    /**
     * set the attrs translation data
     *
     * @param array $fieldTrans
     *
     * @return static
     */
    public function setTranslates(array $fieldTrans): static
    {
        return $this->addTranslates($fieldTrans);
    }

    /**
     * add the attrs translation data
     *
     * @param array $fieldTrans
     *
     * @return static
     */
    public function addTranslates(array $fieldTrans): static
    {
        foreach ($fieldTrans as $field => $tran) {
            $this->_translates[$field] = $tran;
        }
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
     *
     * @param string $field
     *
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
     * @return array
     */
    public function clearTranslates(): array
    {
        return $this->_translates = [];
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
    public function setPrettifyName(bool $prettifyName = true): void
    {
        $this->_prettifyName = $prettifyName;
    }
}

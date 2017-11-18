<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-11-17
 * Time: 11:26
 */

namespace Inhere\Validate\Utils;

/**
 * trait ErrorInformationTrait
 * @package Inhere\Validate\Utils
 */
trait ErrorInformationTrait
{
    /**
     * 保存所有的验证错误信息
     * @var array[]
     * [
     *     [ field => errorMessage1 ],
     *     [ field => errorMessage2 ],
     *     [ field2 => errorMessage3 ]
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
     * Errors
     ******************************************************************************/

    /**
     * @return $this
     */
    public function clearErrors()
    {
        $this->_errors = [];

        return $this;
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
     * @param string $attr
     * @param string $msg
     */
    public function addError(string $attr, string $msg)
    {
        $this->_errors[] = [$attr => $msg];
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->_errors;
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

        return $onlyMsg ? array_values($first)[0] : $first;
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

        return $onlyMsg ? array_values($last)[0] : $last;
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
}

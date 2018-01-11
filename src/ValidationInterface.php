<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-11-07
 * Time: 10:23
 */

namespace Inhere\Validate;

/**
 * Interface ValidationInterface
 * @package Inhere\Validate
 */
interface ValidationInterface
{
    /**
     * @return array
     */
    public function rules();

    /**
     * define attribute field translate list
     * @return array
     */
    public function translates();

    /**
     * 自定义验证器的默认错误消息格式
     * custom validator's message, to override default message.
     * @return array
     */
    public function messages();

    /**
     * 进行数据验证
     * @author inhere
     * @date   2015-08-11
     * @param  array $onlyChecked 可以设置此次需要验证的字段
     * @param  bool|null $stopOnError 是否出现错误即停止验证
     * @return static
     * @throws \RuntimeException
     */
    public function validate(array $onlyChecked = [], $stopOnError = null);

    /**
     * @return bool
     */
    public function fail(): bool;

    /**
     * alias of the fail()
     * @return bool
     */
    public function isFail(): bool;

    /**
     * @return bool
     */
    public function ok(): bool;

    /**
     * @return bool
     */
    public function isPassed(): bool;

    /**
     * @return array
     */
    public function getErrors(): array;

    /**
     * 得到第一个错误信息
     * @author inhere
     * @param bool $onlyMsg Only return message string.
     * @return array|string
     */
    public function firstError($onlyMsg = true);

    /**
     * 得到最后一个错误信息
     * @author inhere
     * @param bool $onlyMsg
     * @return array|string
     */
    public function lastError($onlyMsg = true);

    /**
     * @return array
     */
    public function getMessages(): array;

    /**
     * @return array|\stdClass
     */
    public function getSafeData();
}
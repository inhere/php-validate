<?php declare(strict_types=1);
/**
 * @author inhere
 * @date   : 2015-08-11
 */

namespace Inhere\Validate;

use stdClass;

/**
 * Interface ValidationInterface
 *
 * @package Inhere\Validate
 */
interface ValidationInterface
{
    /**
     * @return array
     */
    public function rules(): array;

    /**
     * custom validator's message, to override default message.
     *
     * @return array
     */
    public function messages(): array;

    /**
     * define attribute field translate list
     *
     * @return array
     */
    public function translates(): array;

    /**
     * Data validation
     *
     * @param array     $onlyChecked 可以设置此次需要验证的字段
     * @param bool|null $stopOnError 是否出现错误即停止验证
     *
     * @return static
     */
    public function validate(array $onlyChecked = [], bool $stopOnError = null);

    /**
     * alias of the fail()
     *
     * @return bool
     */
    public function isFail(): bool;

    /**
     * @return bool
     */
    public function isPassed(): bool;

    /**
     * @param string $field
     *
     * @return array
     */
    public function getErrors(string $field = ''): array;

    /**
     * Get the first error message
     *
     * @param bool $onlyMsg Only return message string.
     *
     * @return array|string
     */
    public function firstError(bool $onlyMsg = true);

    /**
     * Get the last error message
     *
     * @param bool $onlyMsg
     *
     * @return array|string
     */
    public function lastError(bool $onlyMsg = true);

    /**
     * @return array
     */
    public function getMessages(): array;

    /**
     * @param bool $asObject
     *
     * @return array|stdClass
     */
    public function getSafeData(bool $asObject = false);
}

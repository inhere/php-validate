<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-03-17
 * Time: 11:26
 */

namespace Inhere\Validate\Utils;

/**
 * trait RequiredValidatorsTrait
 * @package Inhere\Validate\Utils
 */
trait RequiredValidatorsTrait
{
    /**
     * 验证字段必须存在输入数据，且不为空。字段符合下方任一条件时即为「空」
     * - 该值为 null.
     * - 该值为空字符串。
     * - 该值为空数组
     * @param  string $field
     * @return bool
     */
    public function required($field)
    {
        if (!isset($this->data[$field])) {
            return false;
        }

        $val = $this->data[$field];

        return $val !== '' && $val !== null && $val !== false && $val !== [];
    }

    /**
     * 如果指定的其它字段（ anotherField ）值等于任何一个 value 时，此字段为 必填
     * @from laravel
     * @param  string $field
     * @param  string $anotherField
     * @param  array|string $values
     * @return bool
     */
    public function requiredIf($field, $anotherField, $values)
    {
        if (!isset($this->data[$anotherField])) {
            return false;
        }

        $val = $this->data[$anotherField];

        if (in_array($val, (array)$values, true)) {
            return $this->required($field);
        }

        return false;
    }

    /**
     * 如果指定的其它字段（ anotherField ）值等于任何一个 value 时，此字段为 不必填
     * @from laravel
     * @param  string $field
     * @param  string $anotherField
     * @param  array|string $values
     * @return bool
     */
    public function requiredUnless($field, $anotherField, $values)
    {
        if (!isset($this->data[$anotherField])) {
            return false;
        }

        if (in_array($this->data[$anotherField], (array)$values, true)) {
            return true;
        }

        return $this->required($field);
    }

    /**
     * 如果指定的字段中的 任意一个 有值且不为空，则此字段为必填
     * @from laravel
     * @param  string $field
     * @param  array|string $fields
     * @return bool
     */
    public function requiredWith($field, $fields)
    {
        foreach ((array)$fields as $name) {
            if ($this->required($name)) {
                return $this->required($field);
            }
        }

        return true;
    }

    /**
     * 如果指定的 所有字段 都有值，则此字段为必填。
     * @from laravel
     * @param  string $field
     * @param  array|string $fields
     * @return bool
     */
    public function requiredWithAll($field, $fields)
    {
        $allHasValue = true;

        foreach ((array)$fields as $name) {
            if (!$this->required($name)) {
                $allHasValue = false;
                break;
            }
        }

        return $allHasValue ? $this->required($field) : true;
    }

    /**
     * 如果缺少 任意一个 指定的字段值，则此字段为必填。
     * @from laravel
     * @param  string $field
     * @param  array|string $fields
     * @return bool
     */
    public function requiredWithout($field, $fields)
    {
        $allHasValue = true;

        foreach ((array)$fields as $name) {
            if (!$this->required($name)) {
                $allHasValue = false;
                break;
            }
        }

        return $allHasValue ? true : $this->required($field);
    }

    /**
     * 如果所有指定的字段 都没有 值，则此字段为必填。
     * @from laravel
     * @param  string $field
     * @param  array|string $fields
     * @return bool
     */
    public function requiredWithoutAll($field, $fields)
    {
        $allNoValue = true;

        foreach ((array)$fields as $name) {
            if ($this->required($name)) {
                $allNoValue = false;
                break;
            }
        }

        return $allNoValue ? $this->required($field) : true;
    }
}

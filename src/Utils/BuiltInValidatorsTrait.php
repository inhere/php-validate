<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-03-17
 * Time: 11:26
 */

namespace Inhere\Validate\Utils;

/**
 * trait BuiltInValidatorsTrait
 * @package Inhere\Validate\Utils
 */
trait BuiltInValidatorsTrait
{
    /**
     * custom add's validator by addValidator()
     * @var array
     */
    protected static $_validators = [];

    /**
     * custom add's filter by addFilter()
     * @var array
     */
    protected static $_filters = [];

    /*******************************************************************************
     * required* validators
     ******************************************************************************/

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

        if (\in_array($val, (array)$values, true)) {
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

        if (\in_array($this->data[$anotherField], (array)$values, true)) {
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

    /*******************************************************************************
     * Special validators
     ******************************************************************************/

    /**
     * @param mixed $val
     * @param string $compareField
     * @return bool
     */
    public function compare($val, $compareField)
    {
        return $compareField && ($val === $this->get($compareField));
    }
    public function same($val, $compareField)
    {
        return $this->compare($val, $compareField);
    }
    public function equal($val, $compareField)
    {
        return $this->compare($val, $compareField);
    }

    /*******************************************************************************
     * custom filters
     ******************************************************************************/

    /**
     * @param string $name
     * @param callable $filter
     * @return $this
     */
    public function addFilter(string $name, callable $filter)
    {
        self::$_filters[$name] = $filter;

        return $this;
    }

    /**
     * @param string $name
     * @param callable $filter
     */
    public static function setFilter(string $name, callable $filter)
    {
        self::$_filters[$name] = $filter;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function delFilter(string $name)
    {
        if (isset(self::$_filters[$name])) {
            unset(self::$_filters[$name]);
        }

        return $this;
    }
    /**
     * @return array
     */
    public static function getFilters(): array
    {
        return self::$_filters;
    }

    /**
     * @param array $filters
     */
    public static function setFilters(array $filters)
    {
        self::$_filters = $filters;
    }

    /*******************************************************************************
     * custom validators
     ******************************************************************************/

    /**
     * add a custom validator
     * ```
     * $vd = ValidatorClass::make($_POST)
     *     ->addValidator('name',function($val [, $arg1, $arg2 ... ]){
     *           return $val === 23;
     *     });
     * $vd->validate();
     * ```
     * @param string $name
     * @param callable $callback
     * @param string $msg
     * @return $this
     */
    public function addValidator(string $name, callable $callback, string $msg = '')
    {
        self::setValidator($name, $callback, $msg);

        return $this;
    }

    /**
     * add a custom validator
     * @param string $name
     * @param callable $callback
     * @param string $msg
     */
    public static function setValidator(string $name, callable $callback, string $msg = null)
    {
        self::$_validators[$name] = $callback;

        if ($msg) {
            self::setDefaultMessage($name, $msg);
        }
    }

    /**
     * @param string $name
     * @return null|\Closure
     */
    public static function getValidator($name)
    {
        if (isset(self::$_validators[$name])) {
            return self::$_validators[$name];
        }

        return null;
    }

    /**
     * @param string $name
     * @return bool|\Closure
     */
    public static function delValidator($name)
    {
        $cb = false;

        if (isset(self::$_validators[$name])) {
            $cb = self::$_validators[$name];
            unset(self::$_validators[$name]);
        }

        return $cb;
    }

    /**
     * @param array $validators
     */
    public static function setValidators(array $validators)
    {
        self::$_validators = array_merge(self::$_validators, $validators);
    }

    /**
     * @return array
     */
    public static function getValidators(): array
    {
        return self::$_validators;
    }
}

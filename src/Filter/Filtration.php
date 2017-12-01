<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/3 0003
 * Time: 23:19
 */

namespace Inhere\Validate\Filter;

use Inhere\Validate\Utils\DataFiltersTrait;
use Inhere\Validate\Utils\Helper;

/**
 * Class Filtration
 * @package Inhere\Validate\Filter
 * usage:
 *
 * $data = Filtration::make($_POST, [
 *   ['tagId,userId,freeTime', 'int'],
 *   ['name', 'string|trim', 'default' => 'tom'],
 *   ['email', 'string|email'],
 * ])->filtering();
 */
class Filtration
{
    use DataFiltersTrait;

    /**
     * @var array
     */
    private $_data;

    /**
     * the rules is by setRules()
     * @var array
     */
    private $_rules;

    /**
     * @param array $data
     * @param array $rules
     * @return Filtration
     */
    public static function make(array $data = [], array $rules = [])
    {
        return new self($data, $rules);
    }

    /**
     * Filtration constructor.
     * @param array $data
     * @param array $rules
     */
    public function __construct(array $data = [], array $rules = [])
    {
        $this->_data = $data;
        $this->_rules = $rules;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function load(array $data)
    {
        $this->_data = $data;

        return $this;
    }

    /**
     * @param array $rules
     * @return array
     * @throws \InvalidArgumentException
     */
    public function filtering(array $rules = [])
    {
        return $this->applyRules($rules);
    }

    /**
     * 对数据应用给的一系列过滤规则
     * @param array $rules
     * @param array $data
     * @return array 返回过滤后的数据
     * @throws \InvalidArgumentException
     */
    public function applyRules(array $rules = [], array $data = [])
    {
        $data = $data ?: $this->_data;
        $rules = $rules ?: $this->_rules;
        $filtered = [];

        foreach ($rules as $rule) {
            if (!isset($rule[0], $rule[1])) {
                continue;
            }

            if (!$fields = $rule[0]) {
                continue;
            }

            $fields = \is_string($fields) ? Helper::explode($fields) : (array)$fields;

            foreach ($fields as $field) {
                if (!isset($data[$field])) {
                    $filtered[$field] = $rule['default'] ?? null;
                } else {
                    $filtered[$field] = $this->valueFiltering($data[$field], $rule[1]);
                }
            }
        }

        return $filtered;
    }

    /**
     * value sanitize 直接对给的值进行过滤
     * @param  mixed $value
     * @param  string|array $filters
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function sanitize($value, $filters)
    {
        return $this->valueFiltering($value, $filters);
    }

    /**
     * get a field value from {@see $data}
     * @param string|int $field
     * @param string|array $filters
     * @param mixed $default
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get($field, $filters = null, $default = null)
    {
        if (!isset($this->_data[$field])) {
            return $default;
        }

        $value = $this->_data[$field];

        if (!$filters) {
            return $value;
        }

        return $this->valueFiltering($value, $filters);
    }

    /**
     * @param bool $clearFilters
     * @return $this
     */
    public function reset($clearFilters = false)
    {
        $this->_data = $this->_rules = [];

        if ($clearFilters) {
            self::clearFilters();
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->_data;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->_data;
    }

    /**
     * @param array $rules
     * @return self
     */
    public function setRules(array $rules)
    {
        $this->_rules = $rules;

        return $this;
    }

    /**
     * @return array
     */
    public function getRules(): array
    {
        return $this->_rules;
    }
}

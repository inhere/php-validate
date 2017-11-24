<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/3 0003
 * Time: 23:19
 */

namespace Inhere\Validate\Filter;
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
    /**
     * custom add's filter by addFilter()
     * @var array
     */
    private static $_filters = [];

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

            $fields = \is_string($fields) ? array_map('trim', explode(',', $fields)) : (array)$fields;

            foreach ($fields as $field) {
                if (!isset($data[$field])) {
                    $filtered[$field] = $rule['default'] ?? null;
                } else {
                    $filtered[$field] = $this->sanitize($data[$field], $rule[1]);
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
        $filters = \is_string($filters) ? array_map('trim', explode('|', $filters)) : $filters;

        foreach ($filters as $filter) {
            if (\is_object($filter) && method_exists($filter, '__invoke')) {
                $value = $filter($value);
            } elseif (\is_string($filter)) {
                // if $filter is a custom add callback in the property {@see $_filters}.
                if (isset(self::$_filters[$filter])) {
                    $callback = self::$_filters[$filter];
                    $value = $callback($value);

                    // if $filter is a custom method of the subclass.
                } elseif (method_exists($this, $filter)) {
                    $value = $this->$filter($value);

                    // $filter is a method of the class 'FilterList'
                } elseif (method_exists(FilterList::class, $filter)) {
                    $value = FilterList::$filter($value);

                    // it is function name
                } elseif (\function_exists($filter)) {
                    $value = $filter($value);
                } else {
                    throw new \InvalidArgumentException("The filter [$filter] don't exists!");
                }
            } else {
                $value = Helper::call($filter, $value);
            }
        }

        return $value;
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

        return $this->sanitize($value, $filters);
    }

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
     * @param bool $clearFilters
     * @return $this
     */
    public function reset($clearFilters = false)
    {
        $this->_data = $this->_rules = [];

        if ($clearFilters) {
            self::$_filters = [];
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

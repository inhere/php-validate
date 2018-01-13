<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2017/11/26 0026
 * Time: 00:51
 */

namespace Inhere\Validate\Utils;

use Inhere\Validate\Filter\Filters;

/**
 * Trait DataFiltersTrait
 * @package Inhere\Validate\Utils
 */
trait DataFiltersTrait
{
    /**
     * custom add's filter by addFilter()
     * @var array
     */
    private static $_filters = [];

    /**
     * value sanitize 直接对给的值进行过滤
     * @param  mixed $value
     * @param  string|array $filters
     * string:
     *  'string|trim|upper'
     * array:
     *  [
     *      'string',
     *      'trim',
     *      ['Class', 'method'],
     *      // 追加额外参数. 传入时，第一个参数总是要过滤的字段值，其余的依次追加
     *      'myFilter' => ['arg1', 'arg2'],
     *      function($val) {
     *          return str_replace(' ', '', $val);
     *      },
     *  ]
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function valueFiltering($value, $filters)
    {
        $filters = \is_string($filters) ? Helper::explode($filters, '|') : $filters;

        foreach ($filters as $key => $filter) {
            // key is a filter. ['myFilter' => ['arg1', 'arg2']]
            if (\is_string($key)) {
                $args = (array)$filter;
                $value = $this->callStringCallback($key, $value, ...$args);

                // closure
            } elseif (\is_object($filter) && method_exists($filter, '__invoke')) {
                $value = $filter($value);
                // string, trim, ....
            } elseif (\is_string($filter)) {
                $value = $this->callStringCallback($filter, $value);

                // e.g ['Class', 'method'],
            } else {
                $value = Helper::call($filter, $value);
            }
        }

        return $value;
    }

    /**
     * @param mixed $filter
     * @param array ...$args
     * @return mixed
     */
    protected function callStringCallback($filter, ...$args)
    {
        // if $filter is a custom add callback in the property {@see $_filters}.
        if (isset(self::$_filters[$filter])) {
            $callback = self::$_filters[$filter];
            $value = $callback(...$args);

            // if $filter is a custom method of the subclass.
        } elseif (method_exists($this, $filter . 'Filter')) {
            $filter .= 'Filter';
            $value = $this->$filter(...$args);

            // $filter is a method of the class 'FilterList'
        } elseif (method_exists(Filters::class, $filter)) {
            $value = Filters::$filter(...$args);

            // it is function name
        } elseif (\function_exists($filter)) {
            $value = $filter(...$args);
        } else {
            throw new \InvalidArgumentException("The filter [$filter] don't exists!");
        }

        return $value;
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
     * clear Filters
     */
    public static function clearFilters()
    {
        self::$_filters = [];
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
}

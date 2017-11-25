<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2017/11/26 0026
 * Time: 00:51
 */

namespace Inhere\Validate\Utils;

use Inhere\Validate\Filter\FilterList;

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
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function valueFiltering($value, $filters)
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

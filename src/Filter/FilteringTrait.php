<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2017/11/26 0026
 * Time: 00:51
 */

namespace Inhere\Validate\Filter;

use Inhere\Validate\Helper;
use InvalidArgumentException;
use function function_exists;
use function is_array;
use function is_object;
use function is_string;
use function method_exists;
use function trim;

/**
 * Trait FilteringTrait
 *
 * @package Inhere\Validate\Filter
 */
trait FilteringTrait
{
    /** @var array user custom filters */
    private array $_filters = [];

    /**
     * value sanitize 直接对给的值进行过滤
     *
     * filters:
     *   string:
     *          'string|trim|upper'
     *   array:
     *      [
     *          'string',
     *          'trim',
     *          ['Class', 'method'],
     *          // 追加额外参数. 传入时，第一个参数总是要过滤的字段值，其余的依次追加
     *          'myFilter' => ['arg1', 'arg2'],
     *          function($val) {
     *              return str_replace(' ', '', $val);
     *          },
     *     ]
     *
     * @param mixed $value
     * @param array|string|callable $filters
     *
     * @return mixed
     */
    protected function valueFiltering(mixed $value, array|string|callable $filters): mixed
    {
        if (is_string($filters)) {
            $filters = Filters::explode($filters, '|');
        } elseif (!is_array($filters)) {
            $filters = [$filters];
        }

        foreach ($filters as $key => $filter) {
            // key is a filter. ['myFilter' => ['arg1', 'arg2']]
            if (is_string($key)) {
                $args  = (array)$filter;
                $value = $this->callStringCallback($key, $value, ...$args);

                // closure
            } elseif (is_object($filter) && method_exists($filter, '__invoke')) {
                $value = $filter($value);
                // string, trim, ....
            } elseif (is_string($filter)) {
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
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function callStringCallback(string $filter, ...$args): mixed
    {
        // if is alias name
        $filterName = Filters::realName($filter);

        // if $filter is a custom by addFiler()
        if ($callback = $this->getFilter($filter)) {
            $value = $callback(...$args);
            // if $filter is a custom method of the subclass.
        } elseif (method_exists($this, $filter . 'Filter')) {
            $filter .= 'Filter';
            $value  = $this->$filter(...$args);

            // if $filter is a custom add callback in the property {@see $_filters}.
        } elseif ($callback = UserFilters::get($filter)) {
            $value = $callback(...$args);

            // if $filter is a custom add callback in the property {@see $_filters}.
            // $filter is a method of the class 'FilterList'
        } elseif (method_exists(Filters::class, $filterName)) {
            $value = Filters::$filterName(...$args);

            // it is function name
        } elseif (function_exists($filter)) {
            $value = $filter(...$args);
        } else {
            throw new InvalidArgumentException("The filter [$filter] don't exists!");
        }

        return $value;
    }

    /*******************************************************************************
     * custom filters
     ******************************************************************************/

    /**
     * @param string $name
     *
     * @return callable|null
     */
    public function getFilter(string $name): ?callable
    {
        return $this->_filters[$name] ?? null;
    }

    /**
     * @param string $name
     * @param callable $filter
     *
     * @return static
     */
    public function addFilter(string $name, callable $filter): static
    {
        return $this->setFilter($name, $filter);
    }

    /**
     * @param string $name
     * @param callable $filter
     *
     * @return static
     */
    public function setFilter(string $name, callable $filter): static
    {
        if ($name = trim($name)) {
            $this->_filters[$name] = $filter;
        }
        return $this;
    }

    /**
     * @param string $name
     */
    public function delFilter(string $name): void
    {
        if (isset($this->_filters[$name])) {
            unset($this->_filters[$name]);
        }
    }

    /**
     * clear filters
     */
    public function clearFilters(): void
    {
        $this->_filters = [];
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->_filters;
    }

    /**
     * @param array $filters
     */
    public function addFilters(array $filters): void
    {
        $this->setFilters($filters);
    }

    /**
     * @param array $filters
     */
    public function setFilters(array $filters): void
    {
        foreach ($filters as $name => $filter) {
            $this->setFilter($name, $filter);
        }
    }
}

<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/3 0003
 * Time: 23:19
 */

namespace Inhere\Validate\Filter;

use function array_merge;
use function is_string;

/**
 * Class Filtration
 *
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
    use FilteringTrait;

    /** @var array raw data */
    private array $_data;

    /** @var array the rules is by setRules() */
    private array $_rules;

    /**
     * @param array $data
     * @param array $rules
     *
     * @return Filtration
     */
    public static function make(array $data = [], array $rules = []): self
    {
        return new self($data, $rules);
    }

    /**
     * Filtration constructor.
     *
     * @param array $data
     * @param array $rules
     */
    public function __construct(array $data = [], array $rules = [])
    {
        $this->_data  = $data;
        $this->_rules = $rules;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function load(array $data): self
    {
        $this->_data = array_merge($this->_data, $data);
        return $this;
    }

    /**
     * @param array $rules
     *
     * @return array
     */
    public function filtering(array $rules = []): array
    {
        return $this->applyRules($rules);
    }

    /**
     * Apply a series of filtering rules to the input data
     *
     * @param array $rules
     * @param array $data
     *
     * @return array Return filtered data
     */
    public function applyRules(array $rules = [], array $data = []): array
    {
        $data  = $data ?: $this->_data;
        $rules = $rules ?: $this->_rules;
        // save clean data
        $filtered = [];

        foreach ($rules as $rule) {
            if (!isset($rule[0], $rule[1])) {
                continue;
            }

            if (!$fields = $rule[0]) {
                continue;
            }

            $fields = is_string($fields) ? Filters::explode($fields) : (array)$fields;

            foreach ($fields as $field) {
                if (!isset($data[$field]) && isset($rule['default'])) {
                    $filtered[$field] = $rule['default'];
                } else {
                    $filtered[$field] = $this->valueFiltering($data[$field], $rule[1]);
                }
            }
        }

        return $filtered;
    }

    /**
     * value sanitize Filter the value directly
     *
     * @param mixed        $value
     * @param array|string $filters
     *
     * @return mixed
     */
    public function sanitize(mixed $value, array|string $filters): mixed
    {
        return $this->valueFiltering($value, $filters);
    }

    /**
     * get a field value from {@see $data}
     *
     * @param int|string $field
     * @param array|string|null $filters
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function get(int|string $field, array|string $filters = null, mixed $default = null): mixed
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
     * @param int|string $field
     *
     * @return bool
     */
    public function has(int|string $field): bool
    {
        return isset($this->_data[$field]);
    }

    /**
     * @param bool $clearFilters
     *
     * @return $this
     */
    public function reset(bool $clearFilters = false): self
    {
        $this->_data = $this->_rules = [];

        if ($clearFilters) {
            $this->clearFilters();
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
     *
     * @return self
     */
    public function setRules(array $rules): self
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

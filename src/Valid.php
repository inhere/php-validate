<?php declare(strict_types=1);

namespace Inhere\Validate;

use Inhere\Validate\Exception\ValidateException;
use function array_merge;
use function count;
use function is_int;
use function is_numeric;
use function is_scalar;
use function is_string;

/**
 * Class Valid - Simple Data Validator TODO
 *
 * @package Inhere\Validate
 */
class Valid
{
    /**
     * @var array
     */
    protected static $data = [];

    /**
     * @param array $data
     */
    public static function load(array $data): void
    {
        self::$data = $data;
    }

    /**
     * @param array $data
     */
    public static function append(array $data): void
    {
        self::$data = array_merge(self::$data, $data);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return self::$data;
    }

    /*************************************************************************
     * =========== validate data field value and return
     *************************************************************************/

    /**
     * @param string   $field
     * @param null|int $min
     * @param null|int $max
     * @param null|int $default
     *
     * @return int
     */
    public static function getInt(string $field, int $min = null, int $max = null, int $default = null): int
    {
        if (!isset(self::$data[$field])) {
            if ($default === null) {
                throw new ValidateException($field, 'is required and must be int');
            }

            return $default;
        }

        $val = self::$data[$field];
        if (is_numeric($val)) {
            $val = (int)$val;
        }

        if (!is_int($val)) {
            throw new ValidateException($field, 'must be int value');
        }

        // check min and max value
        if ($min !== null && $val < $min) {
            throw new ValidateException($field, "must be greater or equal to $min");
        }
        if ($max !== null && $val > $max) {
            throw new ValidateException($field, "must be less than or equal to $max");
        }

        return $val;
    }

    public static function getInts(string $field, int $min = null, int $max = null, array $default = null): array
    {
        return [];
    }

    public static function getString(string $field, int $minLen = null, int $maxLen = null, string $default = null): int
    {
        return 0;
    }

    /**
     * @param string     $field
     * @param int|null   $min The value length must be >= $min
     * @param int|null   $max The value length must be <= $min
     * @param array|null $default
     *
     * @return array
     */
    public static function getStrings(string $field, int $min = null, int $max = null, array $default = null): array
    {
        if (!isset(self::$data[$field])) {
            if ($default === null) {
                throw new ValidateException($field, 'is required and must be string array');
            }

            return $default;
        }

        if (!$val = self::$data[$field]) {
            if ($default === null) {
                throw new ValidateException($field, 'is required and must be string array');
            }

            return $default;
        }

        if (is_scalar($val)) {
            return [(string)$val];
        }

        return self::checkArrayValues($val, $field, $min, $max);
    }

    /**
     * @param mixed    $val
     * @param string   $field
     * @param int|null $min
     * @param int|null $max
     *
     * @return array
     */
    protected static function checkArrayValues($val, string $field, int $min = null, int $max = null): array
    {
        $arr = (array)$val;
        $len = count($arr);

        // check min and max value
        if ($min !== null && $len < $min) {
            throw new ValidateException($field, "length must be greater or equal to $min");
        }
        if ($max !== null && $len > $max) {
            throw new ValidateException($field, "length must be less than or equal to $max");
        }

        return $arr;
    }

    /**
     * @param string     $field
     * @param int|null   $min
     * @param int|null   $max
     * @param array|null $default
     *
     * @return array
     */
    public static function getArray(string $field, int $min = null, int $max = null, array $default = null): array
    {
        if (!isset(self::$data[$field])) {
            if ($default === null) {
                throw new ValidateException($field, 'is required and must be array');
            }

            return $default;
        }

        if (!$val = self::$data[$field]) {
            if ($default === null) {
                throw new ValidateException($field, 'is required and must be array');
            }

            return $default;
        }

        if (is_scalar($val)) {
            return [$val];
        }

        return self::checkArrayValues($val, $field, $min, $max);
    }

    /**
     * @param string     $field
     * @param int|null   $min
     * @param int|null   $max
     * @param array|null $default
     *
     * @return array
     */
    public static function getArrayByJSON(string $field, int $min = null, int $max = null, array $default = null): array
    {
        if (!isset(self::$data[$field])) {
            if ($default === null) {
                throw new ValidateException($field, 'is required and must be JSON string');
            }

            return $default;
        }

        if (!$val = self::$data[$field]) {
            if ($default === null) {
                throw new ValidateException($field, 'is required and must be JSON string');
            }

            return $default;
        }

        if (!is_string($val)) {
            throw new ValidateException($field, 'must be an string');
        }

        return [];
    }
}

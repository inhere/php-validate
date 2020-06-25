<?php declare(strict_types=1);

namespace Inhere\Validate;

use Inhere\Validate\Exception\ValidateException;
use function array_merge;
use function is_int;
use function is_numeric;

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

    public static function getStrings(string $field, int $min = null, int $max = null, array $default = null): array
    {
        return [];
    }
}

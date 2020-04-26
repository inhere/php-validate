<?php

namespace Inhere\Validate;

use function array_merge;

/**
 * Class Valid - Simple Data Validator
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

    /**********************************************************************************************
     * =========== validate data field value and return
     *********************************************************************************************/

    /**
     * @param string $field
     * @param null|int   $min
     * @param null|int   $max
     * @param null|int    $default
     *
     * @return int
     */
    public static function getInt(string $field, int $min = null, int $max = null, int $default = null): int
    {
        return 0;
    }

    public static function getInts(string $field, int $min = null, int $max = null, int $default = 0): int
    {
        return 0;
    }

    public static function getString(string $field, int $minLen = null, int $maxLen = null, string $default = null): int
    {
        return 0;
    }

    public static function getStrings(string $field, int $min = null, int $max = null, array $default = null): int
    {
        return 0;
    }

}

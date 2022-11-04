<?php declare(strict_types=1);

namespace Inhere\Validate\Simple;

use Inhere\Validate\Exception\ValidateException;
use Inhere\Validate\Validators;
use Toolkit\Stdlib\Str;
use function array_map;
use function array_merge;
use function count;
use function is_numeric;
use function is_scalar;
use function is_string;
use function json_decode;
use function json_last_error;
use function trim;
use const JSON_ERROR_NONE;

/**
 * Class ValidData - Simple Data Validator
 *
 * @package Inhere\Validate\Simple
 */
class ValidData
{
    /**
     * @var array
     */
    protected static array $data = [];

    /**
     * @var int
     */
    private static int $throwCode = 404;

    /**
     * @var string
     * @psalm-var class-string
     */
    private static string $throwClass = ValidateException::class;

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
    public static function getData(): array
    {
        return self::$data;
    }

    public static function reset(): void
    {
        self::$data = [];
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
    public static function getInt(string $field, int $min = null, ?int $max = null, ?int $default = null): int
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

        // check min and max value
        if (!Validators::integer($val, $min, $max)) {
            throw static::newEx($field, self::fmtMinMaxToMsg('must be int value', $min, $max));
        }

        return $val;
    }

    /**
     * @param string     $field
     * @param int|null   $min
     * @param int|null   $max
     * @param array|null $default
     *
     * @return array
     */
    public static function getInts(string $field, int $min = null, int $max = null, array $default = null): array
    {
        $arr = self::getArray($field, $min, $max, $default);

        return $arr ? array_map('intval', $arr) : [];
    }

    /**
     * @param string     $field
     * @param int|null   $min
     * @param int|null   $max
     * @param array|null $default
     * @param string     $sep
     *
     * @return array
     */
    public static function getIntsBySplit(string $field, int $min = null, int $max = null, array $default = null, string $sep = ','): array
    {
        $arr = self::getArrayBySplit($field, $min, $max, $default, $sep);

        return $arr ? array_map('intval', $arr) : [];
    }

    /**
     * @param string      $field
     * @param float|null    $min
     * @param float|null    $max
     * @param float|null $default
     *
     * @return float
     */
    public static function getFloat(string $field, float $min = null, float $max = null, float $default = null): float
    {
        if (!isset(self::$data[$field])) {
            if ($default === null) {
                throw self::newEx($field, 'is required and must be float');
            }
            return $default;
        }

        $val = self::$data[$field];
        if (is_numeric($val)) {
            $val = (float)$val;

            // check min and max
            if (!Validators::float($val, $min, $max)) {
                throw static::newEx($field, self::fmtMinMaxToMsg('must be float', $min, $max));
            }

            return $val;
        }

        throw self::newEx($field, 'required and must be float value');
    }

    /**
     * @param string      $field
     * @param int|null    $minLen
     * @param int|null    $maxLen
     * @param string|null $default
     *
     * @return string
     */
    public static function getString(string $field, int $minLen = null, int $maxLen = null, string $default = null): string
    {
        if (!isset(self::$data[$field])) {
            if ($default === null) {
                throw self::newEx($field, 'is required and must be string');
            }
            return $default;
        }

        $val = self::$data[$field];
        if (!$val || !is_string($val)) {
            if ($default === null) {
                throw self::newEx($field, 'must be string value');
            }
            return $default;
        }

        // check min and max length
        if (!Validators::size($val = trim($val), $minLen, $maxLen)) {
            throw static::newEx($field, self::fmtMinMaxToMsg('must be string and length', $minLen, $maxLen));
        }

        return $val;
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
                throw static::newEx($field, 'is required and must be string array');
            }
            return $default;
        }

        if (!$val = self::$data[$field]) {
            if ($default === null) {
                throw static::newEx($field, 'is required and must be string array');
            }
            return $default;
        }

        $arr = is_scalar($val) ? [trim((string)$val)] : (array)$val;

        if (!Validators::strList($arr)) {
            throw static::newEx($field, 'must be string array');
        }

        // check min and max value
        if (!Validators::integer(count($arr), $min, $max)) {
            throw static::newEx($field, self::fmtMinMaxToMsg('must be array value and length', $min, $max));
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
                throw static::newEx($field, 'is required and must be array');
            }

            return $default;
        }

        if (!$val = self::$data[$field]) {
            if ($default === null) {
                throw static::newEx($field, 'is required and must be array');
            }
            return $default;
        }

        $arr = is_scalar($val) ? [$val] : (array)$val;

        // check min and max value
        if (!Validators::integer(count($arr), $min, $max)) {
            throw static::newEx($field, self::fmtMinMaxToMsg('must be array value and length', $min, $max));
        }

        return $arr;
    }

    /**
     * @param string     $field
     * @param int|null   $min
     * @param int|null   $max
     * @param array|null $default
     * @param string     $sep
     *
     * @return array
     */
    public static function getArrayBySplit(string $field, int $min = null, int $max = null, array $default = null, string $sep = ','): array
    {
        if (!isset(self::$data[$field])) {
            if ($default === null) {
                throw self::newEx($field, 'is required and must be string');
            }
            return $default;
        }

        $val = self::$data[$field];
        if (!$val || !is_string($val)) {
            if ($default === null) {
                throw self::newEx($field, 'is required and must be string');
            }
            return $default;
        }

        $arr = Str::explode($val, $sep);

        // check min and max value
        if (!Validators::integer(count($arr), $min, $max)) {
            throw static::newEx($field, self::fmtMinMaxToMsg('array value length', $min, $max));
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
    public static function getArrayByJSON(string $field, int $min = null, int $max = null, array $default = null): array
    {
        if (!isset(self::$data[$field])) {
            if ($default === null) {
                throw new ValidateException($field, 'is required and must be JSON string');
            }

            return $default;
        }

        $val = self::$data[$field];
        if (!$val || !is_string($val)) {
            if ($default === null) {
                throw self::newEx($field, 'is required and must be JSON string');
            }
            return $default;
        }

        // must start with: { OR [
        if ('[' !== $val[0] && '{' !== $val[0]) {
            throw self::newEx($field, 'must be valid JSON string');
        }

        $arr = json_decode($val, true, 512, JSON_THROW_ON_ERROR);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw self::newEx($field, 'must be valid JSON string');
        }

        // check min and max value
        if (!Validators::integer(count($arr), $min, $max)) {
            throw static::newEx($field, self::fmtMinMaxToMsg('array value length', $min, $max));
        }

        return $arr;
    }

    /**
     * @param string   $prefix
     * @param int|null $min
     * @param int|null $max
     * @param string   $sepMsg
     *
     * @return string
     */
    public static function fmtMinMaxToMsg(string $prefix, int $min = null, int $max = null, string $sepMsg = 'and'): string
    {
        if ($min !== null && $max !== null) {
            return "$prefix $sepMsg must be >= $min and <= $max";
        }

        // check min and max value
        if ($min !== null) {
            return "$prefix $sepMsg must be greater or equal to $min";
        }

        if ($max !== null) {
            return "$prefix $sepMsg must be less than or equal to $max";
        }

        return $prefix;
    }

    /**
     * @param string $field
     * @param string $errMsg
     *
     * @return ValidateException
     */
    public static function newEx(string $field, string $errMsg): ValidateException
    {
        /** @psalm-var class-string $class */
        $class = self::$throwClass ?: ValidateException::class;
        return new $class($field, $errMsg, self::$throwCode);
    }

    /**
     * @return int
     */
    public static function getThrowCode(): int
    {
        return self::$throwCode;
    }

    /**
     * @param int $throwCode
     */
    public static function setThrowCode(int $throwCode): void
    {
        self::$throwCode = $throwCode;
    }

    /**
     * @return string
     */
    public static function getThrowClass(): string
    {
        return self::$throwClass;
    }

    /**
     * @param string $throwClass
     */
    public static function setThrowClass(string $throwClass): void
    {
        self::$throwClass = $throwClass;
    }
}

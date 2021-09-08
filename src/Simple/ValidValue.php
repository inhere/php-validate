<?php declare(strict_types=1);

namespace Inhere\Validate\Simple;

use InvalidArgumentException;

/**
 * Class ValidValue
 *
 * @package Inhere\Validate\Simple
 */
class ValidValue
{
    /**
     * @var int
     */
    private static $throwCode = 404;

    /**
     * @var string
     */
    private static $throwClass = InvalidArgumentException::class;

    /**
     * Validate and return valid string
     *
     * ```php
     * $opt = [
     *  'default' => ?string,
     *  'message' => string,
     * ];
     * ```
     *
     * @param string      $value
     * @param int|null    $min
     * @param int|null    $max
     * @param array       $opt
     *
     * @return string
     */
    public static function getString(string $value, ?int $min, ?int $max, array $opt = []): string
    {

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

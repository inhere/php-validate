<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2019-01-20
 * Time: 00:03
 */

namespace Inhere\Validate\Validator;

use function trim;

/**
 * Class UserValidators - user custom add global validators
 *
 * @package Inhere\Validate\Validator
 */
final class UserValidators
{
    /**
     * @var array user custom added validators (global)
     */
    private static array $validators = [];

    /**
     * add a custom validator
     *
     * @param string   $name
     * @param callable $callback
     */
    public static function set(string $name, callable $callback): void
    {
        if ($name = trim($name)) {
            self::$validators[$name] = $callback;
        }
    }

    /**
     * @param string $name
     *
     * @return null|callable
     */
    public static function get(string $name): ?callable
    {
        return self::$validators[$name] ?? null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function has(string $name): bool
    {
        return isset(self::$validators[$name]);
    }

    /**
     * @param array $validators
     */
    public static function setValidators(array $validators): void
    {
        self::$validators = [];
        self::addValidators($validators);
    }

    /**
     * @param array $validators
     */
    public static function addValidators(array $validators): void
    {
        foreach ($validators as $name => $validator) {
            self::set($name, $validator);
        }
    }

    /**
     * @return array
     */
    public static function getValidators(): array
    {
        return self::$validators;
    }

    /**
     * @param string $name
     */
    public static function remove(string $name): void
    {
        if (isset(self::$validators[$name])) {
            unset(self::$validators[$name]);
        }
    }

    /**
     * clear all validators
     */
    public static function removeAll(): void
    {
        self::$validators = [];
    }
}

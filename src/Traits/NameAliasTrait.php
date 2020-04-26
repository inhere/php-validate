<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2019-01-24
 * Time: 00:11
 */

namespace Inhere\Validate\Traits;

/**
 * Trait NameAliasTrait
 *
 * @package Inhere\Validate\Traits
 */
trait NameAliasTrait
{
    /** @var array Name aliases map. please define on main-class */
    // protected static $aliases = [];

    /**
     * get real validator name by alias name
     *
     * @param string $name
     *
     * @return string
     */
    public static function realName(string $name): string
    {
        return static::$aliases[$name] ?? $name;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function hasAlias(string $name): bool
    {
        return isset(static::$aliases[$name]);
    }

    /**
     * @return array
     */
    public static function getAliases(): array
    {
        return static::$aliases;
    }

    /**
     * @param array $aliases
     */
    public static function setAliases(array $aliases): void
    {
        foreach ($aliases as $name => $alias) {
            static::$aliases[$name] = $alias;
        }
    }
}

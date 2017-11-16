<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-11-07
 * Time: 10:18
 */

namespace Inhere\Validate;

/**
 * Class Factory
 * @package Inhere\Validate
 */
final class Factory
{
    const RULES = 1;
    const FIELDS = 2;

    public static function make(
        array $data = [], array $rules = [], array $translates = [],
        $type = self::FIELDS, $startValidate = false
    )
    {

    }
}

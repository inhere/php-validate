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
    const FIELD_TO_RULES = 1;
    const RULE_TO_FIELDS = 2;

    public static function make(
        array $data = [], array $rules = [], array $translates = [], $type = self::RULE_TO_FIELDS, $startValidate = false
    )
    {

    }
}
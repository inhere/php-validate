<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-11-07
 * Time: 10:04
 */

namespace Inhere\Validate;

/**
 * Class RuleValidation
 * - alias of the Validation
 * - one rule to many fields. like Yii 1/2 framework
 * ```php
 * [
 *  ['field1, field2, ... ', 'validator', ...],
 *  ['field1, field3, ... ', 'validator', ...]
 * ]
 * ```
 * @package Inhere\Validate
 */
class RuleValidation extends Validation
{

}

<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-11-07
 * Time: 10:04
 */

namespace Inhere\Validate;

/**
 * Class AbstractValidation
 * - one field to many rules. like Laravel framework
 * ```php
 * [
 *  ['field1', 'rule1, rule2, ...', ...],
 *  ['field2', 'rule1, rule3, ...', ...],
 * ]
 * ```
 * @package Inhere\Validate
 */
abstract class AbstractValidation implements ValidationInterface
{

}

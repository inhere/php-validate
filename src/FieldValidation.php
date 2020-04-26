<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-11-07
 * Time: 10:04
 */

namespace Inhere\Validate;

use Inhere\Validate\Traits\MultipleRulesTrait;

/**
 * Class FieldValidation
 * - one field to multiple rules. like Laravel framework
 *
 * ```php
 * $vd = FieldValidation::make($data, $rules, ...);
 * $vd->validate();
 * ```
 *
 * @package Inhere\Validate
 */
class FieldValidation extends AbstractValidation
{
    use MultipleRulesTrait;

    /* examples:
    public function rules()
    {
        return [
            ['field', 'required|string:5,10|...', ...],
            ['field0', ['required', 'string:5,10'], ...],
            ['field1', 'rule1|rule2|...', ...],
            ['field2', 'rule1|rule3|...', ...],
            ['field3', function($val) {}, ...],
        ];
    }
    */
}

<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-11-07
 * Time: 10:04
 */

namespace Inhere\Validate;

/**
 * Class FieldValidation
 * - one field to many rules. like Laravel framework
 * ```php
 * [
 *  ['field1', 'rule1, rule2, ...', ...],
 *  ['field2', 'rule1, rule3, ...', ...],
 * ]
 * ```
 * @package Inhere\Validate
 */
class FieldValidation implements ValidationInterface
{
    use ValidationTrait {
        //set as traitSet;
        get as traitGet;// Methods to define an alias, can be used in the current class.
    }

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param array $data
     * @param array $rules
     * @param array $translates
     * @param string $scene
     * @param bool $startValidate 立即开始验证
     */
    public function __construct(
        array $data = [], array $rules = [], array $translates = [],
        $scene = '', $startValidate = false
    )
    {
        $this->data = $data;

        $this
            ->setRules($rules)
            ->setScene($scene)
            ->setTranslates($translates);

        if ($startValidate) {
            $this->validate();
        }
    }
}

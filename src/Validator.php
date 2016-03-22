<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/3 0003
 * Time: 23:19
 */

namespace slimExtend\validate;

/**
 * Class Validator
 * @package slimExtend\validate
 */
class Validator
{
    use ValidatorTrait {
        set as traitSet;
        get as traitGet;// Methods to define an alias, can be used in the current class.
    }

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param array $data
     * @param array $rules
     * @param array $attrTrans
     * @param string $scene
     * @param bool $startValidate 立即开始验证
     */
    public function __construct(array $data=[], array $rules = [], array $attrTrans = [], $scene='', $startValidate=false)
    {
        $this->data = $data;
        $this->setRules($rules)->setScene($scene)->setAttrTrans($attrTrans);

        if ( $startValidate ) {
            $this->validate();
        }
    }

    /**
     * @param array $data
     * @param array $rules
     * @param array $attrTrans
     * @param string $scene
     * @param bool $startValidate 立即开始验证
     * @return static
     */
    public static function make(array $data, array $rules=[], array $attrTrans = [], $scene='', $startValidate=false)
    {
        return new static($data, $rules, $attrTrans, $scene, $startValidate);
    }

//    public function get($key, $value=null)
//    {
//        return self::traitGet($key, $value);
//    }

}
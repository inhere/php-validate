<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/3 0003
 * Time: 23:19
 */

namespace Inhere\Validate;

/**
 * Class Validation
 * @package Inhere\Validate
 *
 * usage:
 *
 * $vd = Validation::make($_POST, [
 *  ['tagId,userId,name,email,freeTime', 'required'],
 *  ['email', 'email'],
 *  ['userId', 'number'],
 *  ['name', 'regexp' ,'/^[a-z]\w{2,12}$/'],
 * ])->validate();
 *
 * $vd->fail();// bool
 * $vd->firstError(); // get first error message.
 * $vd->passed();// bool
 *
 */
class Validation
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
    public function __construct(array $data = [], array $rules = [], array $translates = [], $scene = '', $startValidate = false)
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

    /**
     * @param array $data
     * @param array $rules
     * @param array $translates
     * @param string $scene
     * @param bool $startValidate 立即开始验证
     * @return static
     */
    public static function make(array $data, array $rules = [], array $translates = [], $scene = '', $startValidate = false)
    {
        return new static($data, $rules, $translates, $scene, $startValidate);
    }

    /**
     * @param $key
     * @param null $value
     * @return mixed
     */
    public function get($key, $value = null)
    {
        return $this->traitGet($key, $value);
    }

}

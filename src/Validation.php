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
 * usage:
 * $vd = Validation::make($_POST, [
 *      ['tagId,userId,name,email,freeTime', 'required'],
 *      ['email', 'email'],
 *      ['userId', 'number'],
 *      ['name', 'regexp' ,'/^[a-z]\w{2,12}$/'],
 * ])->validate();
 * $vd->fail();// bool
 * $vd->firstError(); // get first error message.
 * $vd->passed();// bool
 */
class Validation extends AbstractValidation
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            // ['fields', 'validator', arg0, arg1, something ...]
            // ['tagId,userId,name,email,freeTime', 'required'],
            // ['userId', 'number'],
        ];
    }

    /**
     * @param string $key
     * @param null $value
     * @return mixed
     */
    public function get(string $key, $value = null)
    {
        return $this->traitGet($key, $value);
    }
}

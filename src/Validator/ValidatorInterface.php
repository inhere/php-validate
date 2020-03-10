<?php

namespace Inhere\Validate\Validator;

/**
 *
 * 验证器借口
 */
interface ValidatorInterface 
{

    /**
     * 验证方法，进行验证返回bool类型
     * @param type $value 当前值
     * @param type $data 全部的值
     * @return bool
     */
    public function validate($value, $data): bool;

}

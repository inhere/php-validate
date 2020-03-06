<?php

namespace Inhere\ValidateTest\Validator;

/**
 * Class ClassValidator
 * @package Inhere\ValidateTest\Validator
 */
class AdemoValidatorTest extends \Inhere\Validate\Validator\AbstractValidator 
{

   
    public function validate($value, $data): bool 
    {
        if ($value == 1) {
            return true;
        }
        return false;
    }

}

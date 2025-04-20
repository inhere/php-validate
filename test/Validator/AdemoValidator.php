<?php declare(strict_types=1);

namespace Inhere\ValidateTest\Validator;

use Inhere\Validate\Validator\AbstractValidator;

/**
 * Class ClassValidator
 *
 * @package Inhere\ValidateTest\Validator
 */
class AdemoValidator extends AbstractValidator
{
    /**
     * @param mixed $value
     * @param array $data
     *
     * @return bool
     */
    public function validate(mixed $value, array $data): bool
    {
        return $value === 1;
    }
}

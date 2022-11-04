<?php declare(strict_types=1);

namespace Inhere\Validate\Validator;

/**
 * Interface ValidatorInterface
 */
interface ValidatorInterface
{
    /**
     * Verification method, verify and return bool type
     *
     * @param mixed $value current value for field
     * @param array $data  all data in the validation
     *
     * @return bool
     */
    public function validate(mixed $value, array $data): bool;
}

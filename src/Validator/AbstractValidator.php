<?php declare(strict_types=1);

namespace Inhere\Validate\Validator;

abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * 魔术方法,在试图函数式使用对象是调用
     *
     * @param type $value
     * @param type $data
     *
     * @return bool
     */
    public function __invoke($value, $data): bool
    {
        return (bool)$this->validate($value, $data);
    }
}

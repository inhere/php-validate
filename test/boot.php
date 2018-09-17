<?php
/**
 * phpunit --bootstrap test/boot.php test
 */

require dirname(__DIR__) . '/example/simple-loader.php';

class FieldSample extends \Inhere\Validate\FieldValidation
{
    public function rules(): array
    {
        return [
            ['user', 'required|string:1,12'],
            ['pwd', 'required|string:6,16'],
            ['code', 'lengthEq:4'],
        ];
    }

    public function scenarios(): array
    {
        return [
            'create' => ['user', 'pwd', 'code'],
            'update' => ['user', 'pwd'],
        ];
    }
}

class RuleSample extends \Inhere\Validate\Validation
{

}

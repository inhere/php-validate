<?php


use PHPUnit\Framework\TestCase;
use Inhere\Validate\FieldValidation;

/**
 * @covers FieldValidation
 */
class FieldValidationTest extends TestCase
{
    public $data = [
        // 'userId' => 234,
        'userId' => 'is not an integer',
        'tagId' => '234535',
        // 'freeTime' => '1456767657', // filed not exists
        'note' => '',
        'name' => 'Ajohn',
        'existsField' => 'test',
        'passwd' => 'password',
        'repasswd' => 'repassword',
        'insertTime' => '1456767657',
        'goods' => [
            'apple' => 34,
            'pear' => 50,
        ],
    ];

    public function testValidate()
    {
        $rules = [
            ''
        ];
    }
}

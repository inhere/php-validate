<?php

use PHPUnit\Framework\TestCase;
use Inhere\Validate\FieldValidation;

/**
 * @covers \Inhere\Validate\FieldValidation
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
        'status' => 2,
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
            ['freeTime', 'required'],
            ['userId', 'required|int'],
            ['tagId', 'size:0,50'],
            ['status', 'enum:1,2'],
            ['goods.pear', 'max:30'],
        ];

        $v = FieldValidation::make($this->data, $rules)
            ->setMessages([
                'freeTime.required' => 'freeTime is required!!!!'
            ])
           ->validate([], false);

        $this->assertFalse($v->isOk());
        $this->assertTrue($v->failed());

        $errors = $v->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertCount(3, $errors);

        // var_dump($errors);
    }
}

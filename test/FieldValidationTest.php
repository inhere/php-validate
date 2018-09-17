<?php

use Inhere\Validate\FieldValidation;
use PHPUnit\Framework\TestCase;

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

        $v = FieldValidation::check($this->data, [
            ['userId', 'required|int'],
            ['userId', 'min:1'],
        ]);

        $this->assertFalse($v->isOk());
        $errors = $v->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertCount(1, $errors);
    }

    public function testScenarios()
    {
        $data = [
            'user' => 'inhere',
            'pwd' => '123456',
            'code' => '1234',
        ];

        $v = FieldSample::quick($data,'create')->validate();
        $this->assertTrue($v->isOk());
        $this->assertEmpty($v->getErrors());

        $data = [
            'user' => 'inhere',
            'pwd' => '123456',
            'code' => '12345',
        ];

        $v = FieldSample::quick($data,'create')->validate();
        $this->assertFalse($v->isOk());
        $this->assertEquals('code length must is 4', $v->firstError());

        $v = FieldSample::quick($data,'update')->validate();
        $this->assertTrue($v->isOk());
        $this->assertEmpty($v->getErrors());
    }
}

<?php

namespace Inhere\ValidateTest;

use Inhere\Validate\FieldValidation;
use Inhere\ValidateTest\Sample\FieldSample;
use PHPUnit\Framework\TestCase;

class FieldValidationTest extends TestCase
{
    public $data = [
        // 'userId' => 234,
        'userId'      => 'is not an integer',
        'tagId'       => '234535',
        // 'freeTime' => '1456767657', // filed not exists
        'note'        => '',
        'name'        => 'Ajohn',
        'status'      => 2,
        'existsField' => 'test',
        'passwd'      => 'password',
        'repasswd'    => 'repassword',
        'insertTime'  => '1456767657',
        'goods'       => [
            'apple' => 34,
            'pear'  => 50,
        ],
    ];

    public function testValidateField(): void
    {
        $rules = [
            ['freeTime', 'required'],
            ['userId', 'required|int'],
            ['tagId', 'size:0,50'],
            ['status', 'enum:1,2'],
            ['goods.pear', 'max:30'],
        ];

        $v = FieldValidation::make($this->data, $rules);
        $v->setMessages([
            'freeTime.required' => 'freeTime is required!!!!'
        ])
          ->validate([], false);

        $this->assertFalse($v->isOk());
        $this->assertTrue($v->failed());

        $errors = $v->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertCount(3, $errors);
        $this->assertSame('freeTime is required!!!!', $v->getErrors('freeTime')[0]);

        $v = FieldValidation::check($this->data, [
            ['userId', 'required|int'],
            ['userId', 'min:1'],
        ]);

        $this->assertFalse($v->isOk());
        $errors = $v->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertCount(1, $errors);

        $v = FieldValidation::check([
            'title' => 'hello',
        ], [
            ['title', 'required|string:2,8']
        ]);
        $this->assertTrue($v->isOk());

        $v = FieldValidation::check([
            'title' => 'hello',
        ], [
            ['title', 'required|string:1,3']
        ]);
        $this->assertTrue($v->isFail());
        $this->assertSame(
            'title must be a string and length range must be 1 ~ 3',
            $v->firstError()
        );
    }

    public function testScenarios(): void
    {
        $data = [
            'user' => 'inhere',
            'pwd'  => '123456',
            'code' => '1234',
        ];

        $v = FieldSample::quick($data, 'create')->validate();
        $this->assertTrue($v->isOk());
        $this->assertEmpty($v->getErrors());

        $data = [
            'user' => 'inhere',
            'pwd'  => '123456',
            'code' => '12345',
        ];

        $v = FieldSample::quick($data, 'create')->validate();
        $this->assertFalse($v->isOk());
        $this->assertEquals('code length must is 4', $v->firstError());

        $v = FieldSample::quick($data, 'update')->validate();
        $this->assertTrue($v->isOk());
        $this->assertEmpty($v->getErrors());
    }
}

<?php declare(strict_types=1);

namespace Inhere\ValidateTest;

use Inhere\Validate\FieldValidation;
use Inhere\Validate\FV;
use Inhere\ValidateTest\Example\FieldExample;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Class FieldValidationTest
 *
 * @package Inhere\ValidateTest
 */
class FieldValidationTest extends TestCase
{
    public array $data = [
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

    public function testRuleCollectError(): void
    {
        $rv = FieldValidation::make(['name' => 'inhere'], [
            []
        ]);
        try {
            $rv->validate();
        } catch (Throwable $e) {
            $this->assertSame('Please setting the field(string) to wait validate! position: rule[0]', $e->getMessage());
        }

        $rv = FieldValidation::make(['name' => 'inhere'], [
            ['name']
        ]);
        try {
            $rv->validate();
        } catch (Throwable $e) {
            $this->assertSame(
                'Please setting the validator(s) for validate field! position: rule[1]',
                $e->getMessage()
            );
        }
    }

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
        ])->validate([], false);

        $this->assertFalse($v->isOk());
        $this->assertTrue($v->failed());

        $errors = $v->getErrors();
        $this->assertNotEmpty($errors);

        $this->assertCount(4, $errors);
        $this->assertSame('freeTime is required!!!!', $v->getErrors('freeTime')[0]);

        $v = FieldValidation::check($this->data, [
            ['goods.pear', 'required|int|min:30|max:60']
        ]);
        $this->assertTrue($v->isOk());

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
        $this->assertSame('title must be a string and length range must be 1 ~ 3', $v->firstError());
    }

    public function testOnScene(): void
    {
        $data = [
            'user' => 'inhere',
            'pwd'  => '123456',
            'code' => '1234',
        ];

        $v = FieldValidation::make($data, [
            ['user', 'required|string', 'on' => 's1'],
            ['code', 'required|int', 'filter' => 'int', 'on' => 's2'],
        ]);

        $v->atScene('s1')->validate();

        $this->assertCount(1, $v->getUsedRules());
    }

    public function testScenarios(): void
    {
        $data = [
            'user' => 'inhere',
            'pwd'  => '123456',
            'code' => '1234',
        ];

        $v = FieldExample::quick($data, 'create')->validate();
        $this->assertTrue($v->isOk());
        $this->assertEmpty($v->getErrors());

        $data = [
            'user' => 'inhere',
            'pwd'  => '123456',
            'code' => '12345',
        ];

        $v = FieldExample::quick($data, 'create')->validate();
        $this->assertFalse($v->isOk());
        $this->assertEquals('code length must is 4', $v->firstError());

        $v = FieldExample::quick($data, 'update')->validate();
        $this->assertTrue($v->isOk());
        $this->assertEmpty($v->getErrors());
    }

    /**
     * @link https://github.com/inhere/php-validate/issues/22
     */
    public function testIssues22(): void
    {
        $rs = [
            ['id', 'required'],
            ['name', 'required|string:5,10', 'msg' => '5~10位的字符串'],
            ['sex', 'required|enum:0,1'],
            ['age', 'requiredIf:sex,0|int']
        ];

        $v = FV::check([
            'id'   => 1,
            'name' => '12345',
            'sex'  => 0,
            'age'  => 25,
        ], $rs);

        $this->assertTrue($v->isOk());

        $v = FV::check([
            'id'   => 1,
            'name' => '12345',
            'sex'  => 1,
            // 'age' => 25,
        ], $rs);

        $this->assertTrue($v->isOk());

        $v = FV::check([
            'id'   => 1,
            'name' => '12345',
            'sex'  => 0,
            // 'age' => 25,
            // 'age' => 'string',
        ], $rs);

        $this->assertFalse($v->isOk());
        $this->assertSame('parameter age is required!', $v->firstError());

        $v = FV::check([
            'id'   => 1,
            'name' => '12345',
            'sex'  => 0,
            'age'  => 'string',
        ], $rs);

        $this->assertFalse($v->isOk());
        $this->assertSame('age must be an integer!', $v->firstError());
    }

    /**
     * @link https://github.com/inhere/php-validate/issues/36
     */
    public function testIssues36(): void
    {
        $params = [];

        $v = FieldValidation::check($params, [
            ['owner', 'required', 'msg' => ['owner' => 'owner 缺失']],
        ]);

        $this->assertTrue($v->isFail());
        //$this->assertSame('parameter owner is required!', $v->firstError());

        $v = FieldValidation::check($params, [
            ['owner', 'required', 'msg' => ['required' => 'owner 缺失']],
        ]);

        $this->assertTrue($v->isFail());
        $this->assertSame('owner 缺失', $v->firstError());
    }

    /**
     * @link https://github.com/inhere/php-validate/issues/55
     */
    public function testIssues55(): void
    {
        $data = ['title' => '', 'name' => ''];
        $msg = ['title' => '标题不能为空。', 'name' => '姓名不能为空。'];

        $validator = \Inhere\Validate\Validation::make($data, [
                ['title,name', 'required', 'msg' => $msg],
            ])->validate([], false);

        $this->assertTrue($validator->isFail());
        $this->assertSame(
            [[
                'name' => 'title',
                'msg' => $msg['title'],
            ], [
                'name' => 'name',
                'msg' => $msg['name'],
            ]],
            $validator->getErrors()
        );
    }
}

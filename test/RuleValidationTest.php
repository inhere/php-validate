<?php

use Inhere\Validate\RuleValidation;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Inhere\Validate\RuleValidation
 */
class RuleValidationTest extends TestCase
{
    public function testRequired()
    {
        $data = [
            'userId' => 0,
            'tagId' => 10,
            'goods' => [
                'apple' => 34,
                'pear' => 50,
            ],
        ];

        $v = RuleValidation::makeAndValidate($data, [
            ['userId, tagId, goods.apple', 'required']
        ]);

        $this->assertCount(0, $v->getErrors());
    }

    /**
     * 如果指定的其它字段（ anotherField ）等于任何一个 value 时，被验证的字段必须存在且不为空。
     */
    public function testRequiredIf()
    {
        $data = [
            'userId' => 0,
            'targetId' => null,
            'status' => 10,
        ];

        $v = RuleValidation::makeAndValidate($data, [
            ['userId, targetId', 'requiredIf', 'status', [10]]
        ]);

        $this->assertCount(1, $v->getErrors());
        $this->assertFalse($v->inError('userId'));
        $this->assertTrue($v->inError('targetId'));
    }

    public function testRequiredUnless()
    {
        $data = [
            'userId' => null,
            'targetId' => null,
            'status' => 10,
        ];

        $v = RuleValidation::makeAndValidate($data, [
            ['targetId', 'requiredUnless', 'status', [10]],
            ['userId', 'requiredUnless', 'status', [11]],
        ]);

        $this->assertCount(1, $v->getErrors());
        $this->assertFalse($v->inError('targetId'));
        $this->assertTrue($v->inError('userId'));
    }

    /**
     * 只要在指定的其他字段中有任意一个字段存在时，被验证的字段就必须存在并且不能为空。
     */
    public function testRequiredWith()
    {
        $data = [
            'userId' => null,
            'targetId' => 2,
            'status' => 10,
        ];

        $v = RuleValidation::makeAndValidate($data, [
            ['targetId', 'requiredWith', 'status'],
            ['userId', 'requiredWith', ['status', 'someField']],
        ]);

        // var_dump($v->getErrors());

        $this->assertCount(1, $v->getErrors());
        $this->assertFalse($v->inError('targetId'));
        $this->assertTrue($v->inError('userId'));
    }

    /**
     * 只有当所有的 其他指定字段 全部存在时，被验证的字段才 必须存在并且不能为空。
     */
    public function testRequiredWithAll()
    {
        $data = [
            'userId' => null,
            'targetId' => null,
            'status' => 10,
        ];

        $v = RuleValidation::makeAndValidate($data, [
            ['targetId', 'requiredWithAll', 'status'],
            ['userId', 'requiredWithAll', ['status', 'someField']],
        ]);

        // var_dump($v->getErrors());

        $this->assertCount(1, $v->getErrors());
        $this->assertTrue($v->inError('targetId'));
        $this->assertFalse($v->inError('userId'));
    }

    /**
     * 只要在其他指定的字段中 有任意一个字段不存在，被验证的字段就 必须存在且不为空。
     */
    public function testRequiredWithout()
    {
        $data = [
            'userId' => null,
            'targetId' => null,
            'status' => 10,
        ];

        $v = RuleValidation::makeAndValidate($data, [
            ['targetId', 'requiredWithout', 'status'],
            ['userId', 'requiredWithout', ['status', 'someField']],
        ]);

        // var_dump($v->getErrors());

        $this->assertCount(1, $v->getErrors());
        $this->assertTrue($v->inError('userId'));
        $this->assertFalse($v->inError('targetId'));
    }

    /**
     * 只有当所有的 其他指定的字段 都不存在时，被验证的字段才 必须存在且不为空。
     */
    public function testRequiredWithoutAll()
    {
        $data = [
            'userId' => null,
            'targetId' => null,
            'status' => 10,
        ];

        $v = RuleValidation::makeAndValidate($data, [
            ['targetId', 'requiredWithoutAll', 'someField'],
            ['userId', 'requiredWithoutAll', ['status', 'someField']],
        ]);

        // var_dump($v->getErrors());

        $this->assertCount(1, $v->getErrors());
        $this->assertTrue($v->inError('targetId'));
        $this->assertFalse($v->inError('userId'));
    }

    public function testCollectRules()
    {
        $data = [
            'userId' => 234,
            'tagId' => 35,
            'freeTime' => '1456767657',
            'status' => 2,
            'name' => '1234a2',
            'goods' => [
                'apple' => 34,
                'pear' => 50,
            ],

        ];

        $rules = [
            ['tagId,userId,freeTime', 'required'],
            ['tagId,userId,freeTime', 'number', 'on' => 's1'],
            ['tagId', 'size', 'max' => 567, 'min' => 4, 'on' => 's2'],
            ['name', 'string', 'on' => 's2'],
            ['goods.pear', 'max', 60],
        ];

        $v = RuleValidation::make($data, $rules)->validate();

        $this->assertTrue($v->isOk());
        $this->assertCount(2, $v->getUsedRules());

        $v = RuleValidation::make($data, $rules)->atScene('s1')->validate();

        $this->assertTrue($v->isOk());
        $this->assertCount(3, $v->getUsedRules());

        $v = RuleValidation::make($data, $rules)->atScene('s2')->validate();

        $this->assertTrue($v->isOk());
        $this->assertCount(4, $v->getUsedRules());
    }

    public $data = [
        // 'userId' => 234,
        'userId' => 'is not an integer',
        'tagId' => '35',
        // 'freeTime' => '1456767657', // filed not exists
        'note' => '',
        'status' => 2,
        'name' => '1234a2',
        'existsField' => 'test',
        'passwd' => 'password',
        'repasswd' => 'repassword',
        'insertTime' => '1456767657',
        'goods' => [
            'apple' => 34,
            'pear' => 50,
        ],
    ];

    public function testValidatePassed()
    {
        $data = $this->data;
        $data['userId'] = '456';
        $rules = [
            // ['tagId,userId,freeTime', 'required'],// set message
            ['tagId,userId,freeTime', 'number', 'filter' => 'int'],
            ['tagId', 'size', 'max' => 567, 'min' => 4, 'filter' => 'int'], // 4<= tagId <=567
            // ['goods', 'isList'],
            ['goods.pear', 'max', 60],
        ];
        $v = RuleValidation::make($data, $rules)
            ->setTranslates([
                'goods.pear' => '梨子'
            ])
            ->setMessages([
                'freeTime.required' => 'freeTime is required!!!!'
            ])
            ->validate([], false);

        $this->assertTrue($v->isOk());
        $this->assertFalse($v->failed());
        $this->assertEmpty($v->getErrors());

        $this->assertSame($v->getSafe('userId'), 456);
        $this->assertSame($v->getSafe('tagId'), 35);
    }

    public function testValidateFailed()
    {
        $rules = $this->someRules();
        $v = RuleValidation::make($this->data, $rules)
            ->setTranslates([
                'goods.pear' => '梨子'
            ])
            ->setMessages([
                'freeTime.required' => 'freeTime is required!!!!'
            ])
            ->validate([], false);

        $this->assertFalse($v->isOk());
        $this->assertTrue($v->failed());

        $errors = $v->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertTrue(count($errors) > 3);
        $this->assertEquals($v->getSafe('tagId'), null);
    }

    public function testValidateString()
    {
        $val = '123482';
        $v = RuleValidation::make([
            'user_name' => $val
        ], [
            ['user_name', 'string', 'min' => 6],
            ['user_name', 'string', 'max' => 17],
        ])->validate();

        $this->assertTrue($v->isOk());
        $this->assertFalse($v->failed());

        $errors = $v->getErrors();
        $this->assertEmpty($errors);
        $this->assertCount(0, $errors);
        $this->assertEquals($v->getSafe('user_name'), $val);
    }

    public function testValidateJson()
    {
        $v = RuleValidation::make([
            'log_level' => 'debug',
            'log_data' => '[23]',
            'log_data1' => '234',
        ], [
            ['log_level, log_data', 'required'],
            ['log_level, log_data', 'string'],
            ['log_data', 'json'],
            ['log_data1', 'json', false],
        ])->validate();

        // var_dump($v->getErrors());
        $this->assertTrue($v->isOk());
        $this->assertFalse($v->failed());

        $errors = $v->getErrors();
        $this->assertEmpty($errors);
        $this->assertCount(0, $errors);
    }

    protected function someRules()
    {
        return [
            ['tagId,userId,freeTime', 'required'],// set message
            ['tagId,userId,freeTime', 'number'],
            ['note', 'email', 'skipOnEmpty' => false], // set skipOnEmpty is false.
            ['insertTime', 'email', 'scene' => 'otherScene'],// set scene. will is not validate it on default.
            ['tagId', 'size', 'max' => 567, 'min' => 4,], // 4<= tagId <=567
            ['passwd', 'compare', 'repasswd'], //

            ['name', 'regexp', '/^[a-z]\w{2,12}$/'],

            ['goods.pear', 'max', 30], //

            ['goods', 'isList'], //

            ['notExistsField1', 'requiredWithout', 'notExistsField2'], //
            //    ['notExistsField1', 'requiredWithout', 'existsField'], //

            [
                'freeTime',
                'size',
                'min' => 4,
                'max' => 567,
                'when' => function () {
                    echo "  use when pre-check\n";

                    // $valid is current validation instance.

                    return true;
                }
            ], // 4<= tagId <=567

            [
                'userId',
                function () {
                    echo "  use custom validate to check userId \n";

                    // var_dump($value, $data);
                    // echo __LINE__ . "\n";

                    return false;
                },
                'msg' => 'userId check failure by closure!'
            ],
        ];
    }

    public function testArrayValidate()
    {
        $data = [
            'options' => [
                'opt1' => true,
                'opt2' => 34,
                'opt3' => 'string',
                'opt4' => '0',
            ],
            'key1' => [23, '56'],
            'key2' => [23, 56],
            'key3' => ['23', 'str'],
        ];

        $v = RuleValidation::makeAndValidate($data, [
            ['options, key1, key2, key3', 'isArray'],
            ['options', 'isMap'],
            ['key1', 'isList'],
            ['key1, key2', 'intList'],
            ['key3', 'strList'],
            ['options.opt2', 'num', 'min' => 30, 'max' => 50],
            ['options.opt3', 'string', 'min' => 3, 'max' => 12],
            ['options.opt1, options.opt4', 'bool'],
            ['options.opt1, options.opt4', 'in', [true, false]],
        ]);
        // var_dump($v->getErrors());die;

        $this->assertTrue($v->isOk());
        $this->assertFalse($v->failed());
    }

    /**
     * 验证的 字段值 必须存在于另一个字段（anotherField）的值中。
     */
    public function testInField()
    {
        $v = RuleValidation::makeAndValidate([
            'status' => 3,
            'some' => 30,
            'allowed' => [3, 4, 5],
        ], [
            ['status', 'inField', 'allowed'],
            ['some', 'inField', 'allowed'],
        ]);

        $this->assertFalse($v->isOk());
        $this->assertCount(1, $v->getErrors());
        $this->assertTrue($v->inError('some'));
    }

    /**
     * 验证的 字段值 必须存在于另一个字段（anotherField）的值中。
     */
    public function testRange()
    {
        $v = RuleValidation::make([
            'num' => 3,
            'id' => 300,
        ], [
            ['num', 'range', 'min' => 1, 'max' => 100],
            ['id', 'range', 'min' => 1, 'max' => 100],
        ])
            ->setMessages([
                'id.range' => 'error message',
            ])
            ->validate();

        $this->assertFalse($v->isOk());
        $this->assertCount(1, $v->getErrors());
        $this->assertTrue($v->inError('id'));
        $this->assertStringEndsWith('message', $v->firstError());
    }

    public function testDistinct()
    {
        $v = RuleValidation::makeAndValidate([
            'tags' => [3, 4, 4],
            'goods' => ['apple', 'pear'],
            'users' => [
                ['id' => 34, 'name' => 'tom'],
                ['id' => 89, 'name' => 'john'],
            ],
        ], [
            ['tags', 'distinct'],
            ['goods.*', 'distinct'],
            ['users.*.id', 'distinct'],
        ]);

        // var_dump($v->getErrors());
        $this->assertFalse($v->isOk());
        $this->assertCount(1, $v->getErrors());
        $this->assertTrue($v->inError('tags'));
    }

    public function testEach()
    {
        $v = RuleValidation::makeAndValidate([
            'tags' => [3, 4, 5],
            'goods' => ['apple', 'pear'],
            'users' => [
                ['id' => 34, 'name' => 'tom'],
                ['id' => 89, 'name' => 'john'],
            ],
        ], [
            ['tags', 'each', 'number'],
            ['goods.*', 'each', 'string', 'min' => 4],
            ['users.*.id', 'each', 'required'],
            ['users.*.id', 'each', 'number', 'min' => 34],
            ['users.*.name', 'each', 'string', 'min' => 5],
        ]);

        // var_dump($v->getErrors());
        $this->assertFalse($v->isOk());
        $this->assertCount(1, $v->getErrors());
        $this->assertTrue($v->inError('users.*.name'));
    }
}

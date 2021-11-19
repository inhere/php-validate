<?php declare(strict_types=1);

namespace Inhere\ValidateTest;

use Inhere\Validate\RuleValidation;
use Inhere\Validate\RV;
use Inhere\Validate\Validation;
use Inhere\ValidateTest\Validator\AdemoValidatorTest as AdemoValidator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;
use Throwable;
use function version_compare;

/**
 * Class RuleValidationTest
 */
class RuleValidationTest extends TestCase
{
    public function testBasic(): void
    {
        $v = Validation::make([
            'key' => 'val',
        ]);

        $this->assertTrue($v->has('key'));
        $this->assertSame('val', $v->get('key'));
        $this->assertNotEmpty($v->all());

        $this->assertFalse($v->has('key1'));
        $v->setRaw('key1', 'val1');
        $this->assertTrue($v->has('key1'));

        $this->assertFalse($v->hasRule());

        $rv = RuleValidation::make(['name' => 'inhere'], [
            []
        ]);
        try {
            $rv->validate();
        } catch (Throwable $e) {
            $this->assertSame(
                'Please setting the fields(string|array) to wait validate! position: rule[0]',
                $e->getMessage()
            );
        }

        $rv = RuleValidation::make(['name' => 'inhere'], [
            ['name']
        ]);
        try {
            $rv->validate();
        } catch (Throwable $e) {
            $this->assertSame('The rule validator is must be setting! position: rule[1]', $e->getMessage());
        }
    }

    public function testRequired(): void
    {
        $data = [
            'userId' => 0,
            'tagId'  => 10,
            'goods'  => [
                'apple' => 34,
                'pear'  => 50,
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
    public function testRequiredIf(): void
    {
        $data = [
            'userId'   => 0,
            'targetId' => null,
            'status'   => 10,
        ];

        $v = RuleValidation::makeAndValidate($data, [
            ['userId, targetId', 'requiredIf', 'status', [10]]
        ]);

        $this->assertCount(1, $v->getErrors());
        $this->assertFalse($v->inError('userId'));
        $this->assertTrue($v->inError('targetId'));

        $v = RuleValidation::check($data, [
            ['userId, targetId', 'requiredIf', 'status', 5]
        ]);

        $this->assertCount(0, $v->getErrors());
        $this->assertCount(0, $v->getSafeData());
    }

    /**
     * 如果指定的另一个字段（ anotherField ）值等于任何一个 value 时，此字段为 不必填
     */
    public function testRequiredUnless(): void
    {
        $data = [
            'userId'   => null,
            'targetId' => null,
            'status'   => 10,
        ];

        $v = RuleValidation::check($data, [
            ['targetId', 'requiredUnless', 'status', [10]],
            ['userId', 'requiredUnless', 'status', [11]],
            ['userId', 'requiredUnless', 'not-exists', [11]],
        ]);

        $this->assertCount(1, $v->getErrors());
        $this->assertFalse($v->inError('targetId'));
        $this->assertTrue($v->inError('userId'));
    }

    /**
     * 只要在指定的其他字段中有任意一个字段存在时，被验证的字段就必须存在并且不能为空。
     */
    public function testRequiredWith(): void
    {
        $data = [
            'userId'   => null,
            'targetId' => 2,
            'status'   => 10,
        ];

        $v = RuleValidation::check($data, [
            ['targetId', 'requiredWith', 'status'],
            ['userId', 'requiredWith', ['status', 'someField']],
        ]);

        $this->assertCount(1, $v->getErrors());
        $this->assertFalse($v->inError('targetId'));
        $this->assertTrue($v->inError('userId'));
    }

    /**
     * 只有当所有的 其他指定字段 全部存在时，被验证的字段才 必须存在并且不能为空。
     */
    public function testRequiredWithAll(): void
    {
        $data = [
            'userId'   => null,
            'targetId' => null,
            'status'   => 10,
        ];

        $v = RuleValidation::check($data, [
            ['targetId', 'requiredWithAll', 'status'],
            ['userId', 'requiredWithAll', ['status', 'someField']],
        ]);

        $this->assertCount(1, $v->getErrors());
        $this->assertTrue($v->inError('targetId'));
        $this->assertFalse($v->inError('userId'));
    }

    /**
     * 只要在其他指定的字段中 有任意一个字段不存在，被验证的字段就 必须存在且不为空。
     */
    public function testRequiredWithout(): void
    {
        $data = [
            'userId'   => null,
            'targetId' => null,
            'status'   => 10,
        ];

        $v = RuleValidation::check($data, [
            ['targetId', 'requiredWithout', 'status'],
            ['userId', 'requiredWithout', ['status', 'someField']],
        ]);

        $this->assertCount(1, $v->getErrors());
        $this->assertTrue($v->inError('userId'));
        $this->assertFalse($v->inError('targetId'));
    }

    /**
     * 只有当所有的 其他指定的字段 都不存在时，被验证的字段才 必须存在且不为空。
     */
    public function testRequiredWithoutAll(): void
    {
        $data = [
            'userId'   => null,
            'targetId' => null,
            'status'   => 10,
        ];

        $v = RuleValidation::makeAndValidate($data, [
            ['targetId', 'requiredWithoutAll', 'someField'],
            ['userId', 'requiredWithoutAll', ['status', 'someField']],
        ]);

        $this->assertCount(1, $v->getErrors());
        $this->assertTrue($v->inError('targetId'));
        $this->assertFalse($v->inError('userId'));
    }

    public function testCollectRules(): void
    {
        $data = [
            'userId'   => 234,
            'tagId'    => 35,
            'freeTime' => '1456767657',
            'status'   => 2,
            'name'     => '1234a2',
            'goods'    => [
                'apple' => 34,
                'pear'  => 50,
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
        'userId'      => 'is not an integer',
        'tagId'       => '35',
        // 'freeTime' => '1456767657', // filed not exists
        'note'        => '',
        'status'      => 2,
        'name'        => '1234a2',
        'existsField' => 'test',
        'passwd'      => 'password',
        'repasswd'    => 'repassword',
        'insertTime'  => '1456767657',
        'goods'       => [
            'apple' => 34,
            'pear'  => 50,
        ],
    ];

    public function testValidatePassed(): void
    {
        $data = $this->data;
        // change value
        $data['userId'] = '456';

        $rules = [
            // ['tagId,userId,freeTime', 'required'],// set message
            ['tagId,userId,freeTime', 'number', 'filter' => 'int'],
            ['tagId', 'size', 'max' => 567, 'min' => 4, 'filter' => 'int'], // 4<= tagId <=567
            // ['goods', 'isList'],
            ['goods.pear', 'max', 60],
            ['insertTime', 'safe']
        ];

        $v = RuleValidation::make($data, $rules);
        $v->setTranslates([
            'goods.pear' => '梨子'
        ])->setMessages([
                'freeTime.required' => 'freeTime is required!!!!'
            ])->validate([], false);

        $this->assertTrue($v->isOk());
        $this->assertFalse($v->failed());
        $this->assertEmpty($v->getErrors());

        $this->assertSame($v->getSafe('userId'), 456);
        $this->assertSame($v->getSafe('tagId'), 35);
    }

    public function testValidateFailed(): void
    {
        $rules = $this->someRules();
        ob_start();
        $v = RuleValidation::make($this->data, $rules);
        $v->setTranslates([
            'goods.pear' => '梨子'
        ])->setMessages([
                'freeTime.required' => 'freeTime is required!!!!'
            ])->validate([], false);

        $out = ob_get_clean();

        $needle = 'use when pre-check';
        if (version_compare(Version::id(), '7.0.0', '<')) {
            $this->assertContains($needle, $out);
        } else {
            $this->assertStringContainsString($needle, $out);
        }

        $needle = 'use custom validate';
        if (version_compare(Version::id(), '7.0.0', '<')) {
            $this->assertContains($needle, $out);
        } else {
            $this->assertStringContainsString($needle, $out);
        }

        $this->assertFalse($v->isOk());
        $this->assertTrue($v->failed());

        $errors = $v->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertTrue(count($errors) > 3);
        $this->assertEquals($v->getSafe('tagId'), null);
    }

    public function testValidateRegex(): void
    {
        $v = RuleValidation::check([
            'text1' => 'hello-world',
            'text2' => 'hello world中文',
        ], [
            ['text1, text2', 'string'],
            ['text1', 'regex', '/^[\w-]+$/'],
            ['text2', 'regex', '/[\x{4e00}-\x{9fa5}]+/u'],
        ]);

        $this->assertTrue($v->isOk());
        $this->assertFalse($v->isFail());

        $errors = $v->getErrors();
        $this->assertEmpty($errors);

        $safeData = $v->getSafeData();
        $this->assertArrayHasKey('text2', $safeData);
    }

    public function testValidateString(): void
    {
        $val = '123482';
        $v   = RuleValidation::make([
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

    public function testValidateJson(): void
    {
        $v = RuleValidation::make([
            'log_level' => 'debug',
            'log_data'  => '[23]',
            'log_data1' => '234',
        ], [
            ['log_level, log_data', 'required'],
            ['log_level, log_data', 'string'],
            ['log_data', 'json'],
            ['log_data1', 'json', false],
        ])->validate();

        $this->assertTrue($v->isOk());
        $this->assertFalse($v->failed());

        $errors = $v->getErrors();
        $this->assertEmpty($errors);
        $this->assertCount(0, $errors);
    }

    protected function someRules(): array
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
                'min'  => 4,
                'max'  => 567,
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

                    // echo __LINE__ . "\n";

                    return false;
                },
                'msg' => 'userId check failure by closure!'
            ],

        ];
    }

    /**
     * 测试自定义验证器
     */
    public function testValidator(): void
    {
        $rule       = [
            [
                'user',
                new AdemoValidator(),
                'msg' => 'userId check failure by closure!'
            ],
        ];
        $data       = [
            'user' => 1
        ];
        $validation = Validation::makeAndValidate($data, $rule);
        $this->assertTrue($validation->isOk());
        $validation = Validation::makeAndValidate(['user' => 2], $rule);
        $this->assertTrue($validation->isFail());
    }

    public function testArrayValidate(): void
    {
        $data = [
            'options' => [
                'opt1' => true,
                'opt2' => 34,
                'opt3' => 'string',
                'opt4' => '0',
            ],
            'key1'    => [23, '56'],
            'key2'    => [23, 56],
            'key3'    => ['23', 'str'],
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

        $this->assertTrue($v->isOk());
        $this->assertFalse($v->failed());
    }

    /**
     * 验证的 字段值 必须存在于另一个字段（anotherField）的值中。
     */
    public function testInField(): void
    {
        $v = RuleValidation::check([
            'status'  => 3,
            'some'    => 30,
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
    public function testRange(): void
    {
        $v = RuleValidation::make([
            'num' => 3,
            'id'  => 300,
        ], [
            ['num', 'range', 'min' => 1, 'max' => 100],
            ['id', 'range', 'min' => 1, 'max' => 100],
        ])->setMessages([
                'id.range' => 'range error message',
            ])->validate();

        $this->assertFalse($v->isOk());
        $this->assertCount(1, $v->getErrors());
        $this->assertTrue($v->inError('id'));
        $this->assertEquals('range error message', $v->firstError());
    }

    public function testDistinct(): void
    {
        $v = RuleValidation::makeAndValidate([
            'tags'  => [3, 4, 4],
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

        $this->assertFalse($v->isOk());
        $this->assertCount(1, $v->getErrors());
        $this->assertTrue($v->inError('tags'));
    }

    public function testEach(): void
    {
        $v = RuleValidation::check([
            'tags'  => [3, 4, 5],
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

        $this->assertFalse($v->isOk());
        $this->assertCount(1, $v->getErrors());
        $this->assertTrue($v->inError('users.*.name'));

        $v = RuleValidation::check([
            'users' => [
                ['id' => 34, 'name' => 'tom'],
                ['name' => 'john'],
            ],
        ], [
            ['users.*.id', 'each', 'required'],
            ['users.*.id', 'each', 'number', 'min' => 34],
        ]);

        $this->assertFalse($v->isOk());
        $this->assertCount(1, $v->getErrors());
        $this->assertTrue($v->inError('users.*.id'));
    }

    public function testMultiLevelData(): void
    {
        $v = RuleValidation::check([
            'prod' => [
                'key0' => 'val0',
                [
                    'attr' => [
                        'wid' => 1
                    ]
                ]
            ]
        ], [
            ['prod.*.attr', 'each', 'required'],
            // ['prod.*.attr.wid', 'each', 'required'],
            ['prod.0.attr.wid', 'number'],
        ]);

        $this->assertTrue($v->isOk());
        $this->assertNotEmpty($v->getSafeData());
    }

    public function testGetMessage(): void
    {
        $v = Validation::check([
            'inTest' => 3,
        ], [
            ['inTest', 'in', [1, 2]],
        ]);

        $this->assertFalse($v->isOk());
        $this->assertEquals('in test must in (1,2)', $v->firstError());
    }

    public function testValidatorAlias(): void
    {
        $v = Validation::check([
            'arrTest' => [12, 23],
        ], [
            ['arrTest', 'list'],
            ['arrTest', 'array'],
        ]);

        $this->assertTrue($v->isOk());

        $v = Validation::make([
            'arrVal'  => 'string',
            'listVal' => 'string',
        ], [
            ['arrVal', 'list'],
            ['listVal', 'array'],
        ]);
        $v->setStopOnError(false);
        $v->validate();

        $this->assertTrue($v->isFail());
        $this->assertFalse($v->isStopOnError());
        $this->assertCount(2, $v->getErrors());
        $this->assertTrue($v->inError('listVal'));
        $this->assertEquals('arr val must be an array of nature', $v->firstError());
        $this->assertEquals('list val must be an array', $v->lastError());
    }

    /**
     * @link https://github.com/inhere/php-validate/issues/13
     */
    public function testIssue13(): void
    {
        $rule = [
            ['goods_id', 'list', 'msg' => '商品id数组为空或不合法'],
            ['goods_id.*', 'each', 'integer', 'msg' => '商品分类id必须是一串数字']
        ];

        $v = Validation::check([
            'goods_id' => [
                // 1144181460261978556,
                114418146,
                1144
            ]
        ], $rule);

        $this->assertTrue($v->isOk());
        $this->assertFalse($v->isFail());

        // not array
        $v = Validation::check([
            'goods_id' => 'string'
        ], $rule);
        $this->assertFalse($v->isOk());
        $this->assertSame('商品id数组为空或不合法', $v->firstError());

        // not list
        $v = Validation::check([
            'goods_id' => ['k' => 'v']
        ], $rule);
        $this->assertFalse($v->isOk());
        $this->assertSame('商品id数组为空或不合法', $v->firstError());

        // value not int
        $v = Validation::check([
            'goods_id' => ['v']
        ], $rule);
        $this->assertFalse($v->isOk());
        $this->assertSame('商品分类id必须是一串数字', $v->firstError());
    }

    /**
     * @link https://github.com/inhere/php-validate/issues/17
     */
    public function testIssues17(): void
    {
        $data = [
            'users' => [
                ['id' => 12,],
                ['id' => 23,],
            ],
        ];

        $rules = [
            ['users.*.id', 'required'],
            ['users.*.id', 'each', 'required'],
        ];

        $v = RV::check($data, $rules);
        $this->assertTrue($v->isOk());

        $rules = [
            [
                'users.*.id',
                'each',
                'number',
                'min' => 34,
                'msg' => 'xxx error'
            ],
        ];

        $v = RV::check($data, $rules);
        $this->assertFalse($v->isOk());

        $this->assertSame('xxx error', $v->firstError());
    }

    /**
     * @link https://github.com/inhere/php-validate/issues/20
     */
    public function testIssues20(): void
    {
        $d = [
            'product' => [
                [
                    'sku_id' => 1,
                    'properties' => 'aaa'
                ],
                [
                    'sku_id' => 2,
                    'properties' => 'bbb'
                ]
            ]
        ];
        $r = [
            ['product.*.properties', 'each', 'string', 'max' => 40],
        ];

        $v = RV::check($d, $r);
        $this->assertTrue($v->isOk());

        $r = [
            ['product.*.properties', 'each', 'string', 'max' => 2],
        ];

        $v = RV::check($d, $r);
        $this->assertFalse($v->isOk());
        $this->assertSame('product.*.properties each value must be through the "string" verify', $v->firstError());
    }

    /**
     * @link https://github.com/inhere/php-validate/issues/21
     */
    public function testIssues21(): void
    {
        $d1 = [
            // all items missing 'id' field
            'users' => [
                ['name' => 'n1'],
                ['name' => 'n1'],
            ],
        ];
        $rs = [
            ['users.*.id', 'each', 'required'],
        ];

        $v = RV::check($d1, $rs);

        $this->assertFalse($v->isOk());
        $this->assertSame('users.*.id each value must be through the "required" verify', $v->firstError());

        $d2 = [
            'users' => [
                ['name' => 'n1'], // missing id field
                ['id' => 2, 'name' => 'n1'], // has id field
            ],
        ];
        $v = RV::check($d2, $rs);

        $this->assertFalse($v->isOk());
        $this->assertSame('users.*.id each value must be through the "required" verify', $v->firstError());

        $rs = [
            ['users.*.id', 'required'], // will not pass.
        ];

        $v = RV::check($d1, $rs);
        //parameter users.*.id is required!
        $this->assertFalse($v->isOk());

        $v = RV::check($d2, $rs);
        //parameter users.*.id is required!
        $this->assertFalse($v->isOk());

        $d3 = [
            'users' => [
                ['id' => 1, 'name' => 'n1'],
                ['id' => 2, 'name' => 'n1'],
            ],
        ];

        $rs[] = ['users.*.id', 'each', 'int', 'max' => 3];

        $v = RV::check($d3, $rs);

        $this->assertTrue($v->isOk());
    }

    /**
     * @link https://github.com/inhere/php-validate/issues/33
     */
    public function testIssues33(): void
    {
        $d = [
            'users' => [
                ['id' => 34, 'name' => 'tom'],
                ['id' => 89],
            ],
        ];

        $rs1 = [
            ['users.*.name', 'each', 'required'],
            ['users.*.name', 'each', 'string']
        ];
        $v2 = RuleValidation::check($d, $rs1);

        $this->assertTrue($v2->isFail());

        $rs2[] = ['users.*.name', 'each', 'string'];

        $v1 = RuleValidation::check($d, $rs2);
        $this->assertFalse($v1->isFail());
        $this->assertTrue($v1->isOk());
    }
}

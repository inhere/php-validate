<?php

namespace Inhere\ValidateTest;

use Inhere\Validate\Validation;
use Inhere\Validate\ValidationTrait;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Class ValidationTraitTest
 *
 * @package Inhere\ValidateTest
 */
class ValidationTraitTest extends TestCase
{
    public function testNoDataProperty(): void
    {
        $v = new class
        {
            use ValidationTrait;
        };

        // want data property
        $this->expectException(InvalidArgumentException::class);
        $v->validate();
    }

    public function testGetByPath(): void
    {
        $v = Validation::make([
            'prod' => [
                'key0' => 'val0',
                [
                    'attr' => [
                        'wid' => 1,
                    ]
                ],
                [
                    'attr' => [
                        'wid' => 2,
                    ]
                ],
                [
                    'attr' => [
                        'wid' => 3,
                    ]
                ],
            ]
        ]);

        $val = $v->getByPath('prod.key0');
        $this->assertSame('val0', $val);

        $val = $v->getByPath('prod.0.attr');
        $this->assertSame(['wid' => 1], $val);

        $val = $v->getByPath('prod.0.attr.wid');
        $this->assertSame(1, $val);

        $val = $v->getByPath('prod.*.attr');
        $this->assertSame([['wid' => 1], ['wid' => 2], ['wid' => 3]], $val);

        $val = $v->getByPath('prod.*.attr.wid');
        $this->assertSame([1, 2, 3], $val);
    }

    public function testIndexedArrayGetByPath(): void
    {
        $v = Validation::make([
            ['attr' => ['wid' => 1]],
            ['attr' => ['wid' => 2]],
            ['attr' => ['wid' => 3]],
        ]);

        $val = $v->GetByPath('0.attr');
        $this->assertSame(['wid' => 1], $val);

        $val = $v->getByPath('0.attr.wid');
        $this->assertSame(1, $val);

        $val = $v->getByPath('*.attr');
        $this->assertSame([['wid' => 1], ['wid' => 2], ['wid' => 3]], $val);

        $val = $v->getByPath('*.attr.wid');
        $this->assertSame([1, 2, 3], $val);
    }

    public function testMultidimensionalArray(): void
    {
        $v = Validation::make([
            'companies' => [
                [
                    'name' => 'ms',
                    'departments' => [
                        [
                            'name' => '111',
                            'employees' => [
                                [
                                    'name' => 'aaa',
                                    'manage' => false,
                                ],
                                [
                                    'name' => 'bbb',
                                    'manage' => true,
                                ],
                            ],
                        ],
                        [
                            'name' => '222',
                            'employees' => [
                                [
                                    'name' => 'ccc',
                                    'manage' => true,
                                ],
                                [
                                    'name' => 'ddd',
                                    'manage' => false,
                                ],
                            ],
                        ],
                        [
                            'name' => '333',
                            'employees' => [
                                [
                                    'name' => 'eee',
                                    'manage' => false,
                                ],
                                [
                                    'name' => 'fff',
                                    'manage' => true,
                                ],
                            ],
                        ],
                    ]
                ],
                [
                    'name' => 'google',
                    'departments' => [
                        [
                            'name' => '444',
                            'employees' => [
                                [
                                    'name' => 'xxx',
                                    'manage' => false,
                                ],
                                [
                                    'name' => 'yyy',
                                    'manage' => true,
                                ],
                            ],
                        ],
                    ]
                ],
            ],
        ]);

        $val = $v->getByPath('companies.*.name');
        $this->assertSame(['ms', 'google'], $val);

        $val = $v->getByPath('companies.0.departments.*.employees.0.manage');
        $this->assertSame([false, true, false], $val);

        $val = $v->getByPath('companies.0.departments.*.employees.*.manage');
        $this->assertSame([false, true, true, false, false, true], $val);

        $val = $v->getByPath('companies.*.departments.*.employees.*.name');
        $this->assertSame(['aaa', 'bbb', 'ccc', 'ddd', 'eee', 'fff', 'xxx', 'yyy'], $val);
    }

    public function testBeforeAndAfter(): void
    {
        $v = Validation::make(['name' => 'inhere'], [
            ['name', 'string', 'min' => 3, 'filter' => 'trim|upper']
        ]);

        $v->onBeforeValidate(function (Validation $v) {
            $this->assertSame('inhere', $v->getRaw('name'));
            $this->assertNull($v->getSafe('name'));

            return true;
        });

        $v->onAfterValidate(function (Validation $v) {
            $this->assertSame('INHERE', $v->getRaw('name'));
            $this->assertSame('INHERE', $v->getSafe('name'));
        });

        $v->validate();

        $this->assertTrue($v->isOk());
        $this->assertTrue($v->isValidated());

        $v->validate();

        $this->assertTrue($v->isValidated());
    }
}

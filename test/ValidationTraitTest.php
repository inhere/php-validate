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
                        'wid' => 1
                    ]
                ]
            ]
        ]);

        $val = $v->getByPath('prod.key0');
        $this->assertSame('val0', $val);

        $val = $v->getByPath('prod.0.attr');
        $this->assertSame(['wid' => 1], $val);

        $val = $v->getByPath('prod.0.attr.wid');
        $this->assertSame(1, $val);

        $val = $v->getByPath('prod.*.attr');
        $this->assertSame([['wid' => 1]], $val);

        // $val = $v->getByPath('prod.*.attr.wid');
        // $this->assertSame([1], $val);
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

<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2019-01-23
 * Time: 01:26
 */

namespace Inhere\ValidateTest;

use Inhere\Validate\Validation;
use PHPUnit\Framework\TestCase;

/**
 * Class ScopedValidatorsTest
 * @package Inhere\ValidateTest
 */
class ScopedValidatorsTest extends TestCase
{
    public function testUserValidators()
    {
        $v = Validation::make([]);
        $v->clearValidators();
        $v->addValidators([
            'name1' => function () {
            },
            'name2' => function () {
            },
            ''      => function () {
            },
        ]);

        $this->assertCount(2, $v->getValidators());
        $this->assertTrue($v->hasValidator('name1'));
        $this->assertFalse($v->hasValidator(''));

        $this->assertNotEmpty($v->getValidator('name2'));
        $this->assertEmpty($v->getValidator('name3'));

        $v->delValidator('name1');
        $this->assertFalse($v->hasValidator('name1'));

        $v->clearValidators();
        $this->assertCount(0, $v->getValidators());
    }

    /**
     * eq, neq, lt, lte, gt, gte field compare...
     */
    public function testFiledCompare()
    {
        $v = Validation::make([
            'name' => 'tom',
            'age'  => 34,
            'tags' => ['funny', 'smile'],
        ]);

        // eq
        $this->assertTrue($v->eqFieldValidator(34, 'age'));
        $this->assertFalse($v->eqFieldValidator(334, 'age'));

        // neq
        $this->assertTrue($v->neqFieldValidator(334, 'age'));
        $this->assertFalse($v->neqFieldValidator(34, 'age'));

        // lt
        $this->assertTrue($v->ltFieldValidator(23, 'age'));
        $this->assertFalse($v->ltFieldValidator('23', 'age'));
        $this->assertFalse($v->ltFieldValidator(34, 'age'));

        // lte
        $this->assertTrue($v->lteFieldValidator(23, 'age'));
        $this->assertTrue($v->lteFieldValidator(34, 'age'));
        $this->assertFalse($v->lteFieldValidator('34', 'age'));

        // gt
        $this->assertTrue($v->gtFieldValidator(45, 'age'));
        $this->assertFalse($v->gtFieldValidator('45', 'age'));
        $this->assertFalse($v->gtFieldValidator(23, 'age'));

        // gte
        $this->assertTrue($v->gteFieldValidator(43, 'age'));
        $this->assertTrue($v->gteFieldValidator(34, 'age'));
        $this->assertFalse($v->gteFieldValidator('34', 'age'));
    }

    public function testRequired()
    {
        $v = Validation::make([
            'age'      => 23,
            'zero'     => 0,
            'false'    => false,
            'null'     => null,
            'emptyStr' => '',
            'emptyArr' => [],
            'emptyObj' => new \stdClass(),
        ]);

        $samples = [
            ['age', true],
            ['zero', true],
            ['false', true],
            ['null', false],
            ['emptyStr', false],
            ['emptyArr', false],
            ['emptyObj', false],
            ['notExist', false],
        ];

        foreach ($samples as $item) {
            $this->assertSame($item[1], $v->required($item[0]));
        }
    }
}

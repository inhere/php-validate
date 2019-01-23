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
    /**
     * lt, lte, ...
     */
    public function testFiledCompare()
    {
        $v = Validation::make([
            'name' => 'tom',
            'age' => 34,
            'tags' => ['funny', 'smile'],
        ]);

        // lt
        $this->assertTrue($v->ltFieldValidator(23, 'age'));
        $this->assertFalse($v->ltFieldValidator('23', 'age'));
        $this->assertFalse($v->ltFieldValidator(34, 'age'));

        // lte
        $this->assertTrue($v->lteFieldValidator(23, 'age'));
        $this->assertTrue($v->lteFieldValidator(34, 'age'));
        $this->assertFalse($v->lteFieldValidator('34', 'age'));

        // gte
        $this->assertTrue($v->gteFieldValidator(43, 'age'));
        $this->assertTrue($v->gteFieldValidator(34, 'age'));
        $this->assertFalse($v->gteFieldValidator('34', 'age'));
    }
}

<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2019-01-23
 * Time: 01:27
 */

namespace Inhere\ValidateTest\Validator;

use Inhere\Validate\Validator\UserValidators;
use PHPUnit\Framework\TestCase;

/**
 * Class UserValidatorsTest
 *
 * @package Inhere\ValidateTest\Validator
 */
class UserValidatorsTest extends TestCase
{
    public function testBasic(): void
    {
        UserValidators::removeAll();
        UserValidators::setValidators([
            'name1' => function () {
            },
            'name2' => function () {
            },
            ''      => function () {
            },
        ]);

        $this->assertCount(2, UserValidators::getValidators());
        $this->assertTrue(UserValidators::has('name1'));
        $this->assertFalse(UserValidators::has(''));

        $this->assertNotEmpty(UserValidators::get('name2'));
        $this->assertEmpty(UserValidators::get('name3'));

        UserValidators::remove('name1');
        $this->assertFalse(UserValidators::has('name1'));

        UserValidators::removeAll();
        $this->assertCount(0, UserValidators::getValidators());
    }
}

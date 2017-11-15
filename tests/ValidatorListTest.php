<?php

use PHPUnit\Framework\TestCase;
use Inhere\Validate\ValidatorList;

/**
 * @covers ValidatorListTest
 */
class ValidatorListTest extends TestCase
{

    public function testIsEmpty()
    {
        $this->assertFalse(ValidatorList::isEmpty(1));
        $this->assertFalse(ValidatorList::isEmpty(0));

        $this->assertTrue(ValidatorList::isEmpty(null));
        $this->assertTrue(ValidatorList::isEmpty(false));
        $this->assertTrue(ValidatorList::isEmpty([]));
        $this->assertTrue(ValidatorList::isEmpty(''));
        $this->assertTrue(ValidatorList::isEmpty(' '));
    }

    public function testInteger()
    {
        $this->assertFalse(ValidatorList::integer(''));
        $this->assertFalse(ValidatorList::integer(null));
        $this->assertFalse(ValidatorList::integer(false));

        $this->assertTrue(ValidatorList::integer(0));
        $this->assertTrue(ValidatorList::integer(1));
        $this->assertTrue(ValidatorList::integer(-1));
        $this->assertTrue(ValidatorList::integer('1'));
    }

    public function testNumber()
    {
        $this->assertFalse(ValidatorList::number(''));
        $this->assertFalse(ValidatorList::number(-1));
        $this->assertFalse(ValidatorList::number(0));

        $this->assertTrue(ValidatorList::number(1));
    }

    public function testString()
    {
        $this->assertFalse(ValidatorList::string(false));
        $this->assertFalse(ValidatorList::string(null));
        $this->assertFalse(ValidatorList::string(true));
        $this->assertFalse(ValidatorList::string(0));
        $this->assertFalse(ValidatorList::string('test', 2, 3));
        $this->assertFalse(ValidatorList::string('test', 5));

        $this->assertTrue(ValidatorList::string(''));
        $this->assertTrue(ValidatorList::string('test', 2, 5));
    }
}

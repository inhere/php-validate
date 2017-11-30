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

    public function testAlpha()
    {
        $this->assertFalse(ValidatorList::alpha(5));
        $this->assertFalse(ValidatorList::alpha('5'));
        $this->assertTrue(ValidatorList::alpha('test'));
    }


    public function testAlphaNum()
    {
        $this->assertFalse(ValidatorList::alphaNum('='));
        $this->assertTrue(ValidatorList::alphaNum(5));
        $this->assertTrue(ValidatorList::alphaNum('5'));
        $this->assertTrue(ValidatorList::alphaNum('6787test'));
        $this->assertTrue(ValidatorList::alphaNum('test'));
    }

    public function testAlphaDash()
    {
        $this->assertFalse(ValidatorList::alphaDash('test='));

        $this->assertTrue(ValidatorList::alphaDash('sdf56-_'));
    }

    public function testSize()
    {
        $this->assertFalse(ValidatorList::size('test', 5));
        $this->assertFalse(ValidatorList::size(56, 20, 50));

        $this->assertTrue(ValidatorList::size(56, 20, 100));
        $this->assertTrue(ValidatorList::size('test', 2, 4));
        $this->assertTrue(ValidatorList::size([3, 'test', 'hi'], 1, 4));
    }

    public function testMin()
    {
        $this->assertFalse(ValidatorList::min('test', 5));
        $this->assertFalse(ValidatorList::min(56, 60));

        $this->assertTrue(ValidatorList::min(56, 20));
        $this->assertTrue(ValidatorList::min('test', 2));
        $this->assertTrue(ValidatorList::min([3, 'test', 'hi'], 2));
    }

    public function testMax()
    {
        $this->assertFalse(ValidatorList::max('test', 3));
        $this->assertFalse(ValidatorList::max(56, 50));

        $this->assertTrue(ValidatorList::max(56, 60));
        $this->assertTrue(ValidatorList::max('test', 5));
        $this->assertTrue(ValidatorList::max([3, 'test', 'hi'], 5));
    }

    public function testLength()
    {
        $this->assertFalse(ValidatorList::length('test', 5));
        $this->assertFalse(ValidatorList::length('test', 0, 3));
        $this->assertFalse(ValidatorList::length(56, 60));

        $this->assertTrue(ValidatorList::length('test', 3, 5));
        $this->assertTrue(ValidatorList::length([3, 'test', 'hi'], 2, 5));
    }
}

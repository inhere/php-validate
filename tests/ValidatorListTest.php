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
        $this->assertFalse(ValidatorList::isEmpty(false));

        $this->assertTrue(ValidatorList::isEmpty(null));
        $this->assertTrue(ValidatorList::isEmpty([]));
        $this->assertTrue(ValidatorList::isEmpty(''));
        $this->assertTrue(ValidatorList::isEmpty(' '));
    }

    public function testBool()
    {
        $this->assertFalse(ValidatorList::bool(null));
        $this->assertFalse(ValidatorList::bool([]));

        $this->assertTrue(ValidatorList::bool('1'));
        $this->assertTrue(ValidatorList::bool(1));
    }

    public function testFloat()
    {
        $this->assertFalse(ValidatorList::float(null));
        $this->assertFalse(ValidatorList::float(false));
        $this->assertFalse(ValidatorList::float(''));

        $this->assertTrue(ValidatorList::float('1'));
        $this->assertTrue(ValidatorList::float('1.0'));
        $this->assertTrue(ValidatorList::float(3.4));
        $this->assertTrue(ValidatorList::float(-3.4));
        $this->assertTrue(ValidatorList::float(3.4, 3.1));
        $this->assertTrue(ValidatorList::float(3.4, 3.1, 5.4));
        $this->assertTrue(ValidatorList::float(3.4, null, 5.4));
    }

    public function testInteger()
    {
        $this->assertFalse(ValidatorList::integer(''));
        $this->assertFalse(ValidatorList::integer(null));
        $this->assertFalse(ValidatorList::integer(false));
        $this->assertFalse(ValidatorList::integer(2, 5));

        $this->assertTrue(ValidatorList::integer(0));
        $this->assertTrue(ValidatorList::integer(1));
        $this->assertTrue(ValidatorList::integer(-1));
        $this->assertTrue(ValidatorList::integer('1'));
        $this->assertTrue(ValidatorList::integer(-2, -3, 1));
        $this->assertTrue(ValidatorList::integer(2, 2, 5));
        $this->assertTrue(ValidatorList::integer(2, null, 5));
    }

    public function testNumber()
    {
        $this->assertFalse(ValidatorList::number(''));
        $this->assertFalse(ValidatorList::number(-1));
        $this->assertFalse(ValidatorList::number(0));

        $this->assertTrue(ValidatorList::number(1));
        $this->assertTrue(ValidatorList::number(10, 5, 12));
        $this->assertTrue(ValidatorList::number(10, 12, 5));
        $this->assertTrue(ValidatorList::number(10, null, 12));
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

    public function testAccepted()
    {
        $this->assertFalse(ValidatorList::accepted(5));
        $this->assertFalse(ValidatorList::accepted('5'));
        $this->assertFalse(ValidatorList::accepted('no'));
        $this->assertFalse(ValidatorList::accepted('off'));
        $this->assertFalse(ValidatorList::accepted('OFF'));
        $this->assertFalse(ValidatorList::accepted('0'));
        $this->assertFalse(ValidatorList::accepted(0));
        $this->assertFalse(ValidatorList::accepted(false));
        $this->assertFalse(ValidatorList::accepted('false'));

        $this->assertTrue(ValidatorList::accepted('yes'));
        $this->assertTrue(ValidatorList::accepted('Yes'));
        $this->assertTrue(ValidatorList::accepted('on'));
        $this->assertTrue(ValidatorList::accepted('ON'));
        $this->assertTrue(ValidatorList::accepted('1'));
        $this->assertTrue(ValidatorList::accepted(1));
        $this->assertTrue(ValidatorList::accepted(true));
        $this->assertTrue(ValidatorList::accepted('true'));
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

    public function testRegexp()
    {
        $this->assertFalse(ValidatorList::regexp('test', '/^\d+$/'));
        $this->assertFalse(ValidatorList::regexp('test-dd', '/^\w+$/'));

        $this->assertTrue(ValidatorList::regexp('test56', '/^\w+$/'));
    }

    public function testUrl()
    {
        $this->assertFalse(ValidatorList::url('test'));
        $this->assertFalse(ValidatorList::url('/test56'));

        $this->assertTrue(ValidatorList::url('http://a.com/test56'));
    }

    public function testEmail()
    {
        $this->assertFalse(ValidatorList::email('test'));
        $this->assertFalse(ValidatorList::email('/test56'));

        $this->assertTrue(ValidatorList::email('abc@gmail.com'));
    }

    public function testIp()
    {
        $this->assertFalse(ValidatorList::ip('test'));
        $this->assertFalse(ValidatorList::ip('/test56'));

        $this->assertTrue(ValidatorList::ip('0.0.0.0'));
        $this->assertTrue(ValidatorList::ip('127.0.0.1'));
    }

    public function testIsArray()
    {
        $this->assertFalse(ValidatorList::isArray('test'));
        $this->assertFalse(ValidatorList::isArray(345));

        $this->assertTrue(ValidatorList::isArray([]));
        $this->assertTrue(ValidatorList::isArray(['a']));
    }

    public function testIsMap()
    {
        $this->assertFalse(ValidatorList::isMap('test'));
        $this->assertFalse(ValidatorList::isMap([]));
        $this->assertFalse(ValidatorList::isMap(['abc']));

        $this->assertTrue(ValidatorList::isMap(['a' => 'v']));
        $this->assertTrue(ValidatorList::isMap(['value', 'a' => 'v']));
    }

    public function testIsList()
    {
        $this->assertFalse(ValidatorList::isList('test'));
        $this->assertFalse(ValidatorList::isList([]));
        $this->assertFalse(ValidatorList::isList(['a' => 'v']));
        $this->assertFalse(ValidatorList::isList(['value', 'a' => 'v']));
        $this->assertFalse(ValidatorList::isList([3 => 'abc']));

        $this->assertTrue(ValidatorList::isList(['abc']));
        $this->assertTrue(ValidatorList::isList(['abc', 565, null]));
    }

    public function testIntList()
    {
        $this->assertFalse(ValidatorList::intList('test'));
        $this->assertFalse(ValidatorList::intList([]));
        $this->assertFalse(ValidatorList::intList(['a', 'v']));
        $this->assertFalse(ValidatorList::intList(['a', 456]));
        $this->assertFalse(ValidatorList::intList(['a' => 'v']));
        $this->assertFalse(ValidatorList::intList(['value', 'a' => 'v']));
        $this->assertFalse(ValidatorList::intList([2 => '343', 45]));

        $this->assertTrue(ValidatorList::intList(['343', 45]));
        $this->assertTrue(ValidatorList::intList([565, 3234, -56]));
    }

    public function testNumList()
    {
        $this->assertFalse(ValidatorList::numList('test'));
        $this->assertFalse(ValidatorList::numList([]));
        $this->assertFalse(ValidatorList::numList(['a', 'v']));
        $this->assertFalse(ValidatorList::numList(['a' => 'v']));
        $this->assertFalse(ValidatorList::numList(['value', 'a' => 'v']));
        $this->assertFalse(ValidatorList::numList([565, 3234, -56]));
        $this->assertFalse(ValidatorList::numList([2 => 56, 45]));

        $this->assertTrue(ValidatorList::numList(['343', 45]));
        $this->assertTrue(ValidatorList::numList([56, 45]));
    }

    public function testStrList()
    {
        $this->assertFalse(ValidatorList::strList('test'));
        $this->assertFalse(ValidatorList::strList([]));
        $this->assertFalse(ValidatorList::strList(['a' => 'v']));
        $this->assertFalse(ValidatorList::strList(['value', 'a' => 'v']));
        $this->assertFalse(ValidatorList::strList(['abc', 565]));
        $this->assertFalse(ValidatorList::strList(['abc', 565, null]));

        $this->assertTrue(ValidatorList::strList(['abc', 'efg']));
    }

    public function testJson()
    {
        $this->assertFalse(ValidatorList::json('test'));
        $this->assertFalse(ValidatorList::json([]));

        $this->assertFalse(ValidatorList::json(123));
        $this->assertFalse(ValidatorList::json('123'));
        $this->assertTrue(ValidatorList::json('123', false));

        $this->assertFalse(ValidatorList::json('{aa: 34}'));

        $this->assertTrue(ValidatorList::json('{}'));
        $this->assertTrue(ValidatorList::json('[]'));
        $this->assertTrue(ValidatorList::json('{"aa": 34}'));
    }

    public function testHasKey()
    {
        $this->assertFalse(ValidatorList::hasKey('hello, world', 'all'));
        $this->assertFalse(ValidatorList::hasKey(['a' => 'v0', 'b' => 'v1', 'c' => 'v2'], 'd'));
        $this->assertFalse(ValidatorList::hasKey(['a' => 'v0', 'b' => 'v1', 'c' => 'v2'], ['c', 'd']));

        $this->assertTrue(ValidatorList::hasKey(['a' => 'v0', 'b' => 'v1', 'c' => 'v2'], 'b'));
        $this->assertTrue(ValidatorList::hasKey(['a' => 'v0', 'b' => 'v1', 'c' => 'v2'], ['b', 'c']));
    }

    public function testContains()
    {
        $this->assertFalse(ValidatorList::contains('hello, world', 'all'));

        $this->assertTrue(ValidatorList::contains('hello, world', 'llo'));
        $this->assertTrue(ValidatorList::contains('hello, world', ['llo', 'wor']));
    }

    public function testStartWith()
    {
        $this->assertFalse(ValidatorList::startWith('hello, world', 'ell'));

        $this->assertTrue(ValidatorList::startWith('hello, world', 'hell'));
        $this->assertTrue(ValidatorList::startWith(['hello', 'world'], 'hello'));
    }

    public function testEndWith()
    {
        $this->assertFalse(ValidatorList::endWith('hello, world', 'ell'));

        $this->assertTrue(ValidatorList::endWith('hello, world', 'world'));
        $this->assertTrue(ValidatorList::endWith(['hello', 'world'], 'world'));
    }

    public function testDate()
    {
        $this->assertFalse(ValidatorList::date('hello'));

        $this->assertTrue(ValidatorList::date(170526));
        $this->assertTrue(ValidatorList::date('20170526'));
    }
}

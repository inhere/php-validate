<?php

use PHPUnit\Framework\TestCase;
use Inhere\Validate\Validators;

/**
 * @covers \Inhere\Validate\Validators
 */
class ValidatorsTest extends TestCase
{
    public function testIsEmpty()
    {
        $this->assertFalse(Validators::isEmpty(1));
        $this->assertFalse(Validators::isEmpty(0));
        $this->assertFalse(Validators::isEmpty(false));

        $this->assertTrue(Validators::isEmpty(null));
        $this->assertTrue(Validators::isEmpty([]));
        $this->assertTrue(Validators::isEmpty(new \stdClass()));
        $this->assertTrue(Validators::isEmpty(''));
        $this->assertTrue(Validators::isEmpty(' '));
    }

    public function testBool()
    {
        $this->assertFalse(Validators::bool(null));
        $this->assertFalse(Validators::bool([]));

        $this->assertTrue(Validators::bool('1'));
        $this->assertTrue(Validators::bool(1));
    }

    public function testFloat()
    {
        $this->assertFalse(Validators::float(null));
        $this->assertFalse(Validators::float(false));
        $this->assertFalse(Validators::float(''));

        $this->assertTrue(Validators::float('1'));
        $this->assertTrue(Validators::float('1.0'));
        $this->assertTrue(Validators::float(3.4));
        $this->assertTrue(Validators::float(-3.4));
        $this->assertTrue(Validators::float(3.4, 3.1));
        $this->assertTrue(Validators::float(3.4, 3.1, 5.4));
        $this->assertTrue(Validators::float(3.4, null, 5.4));
    }

    public function testInteger()
    {
        $this->assertFalse(Validators::integer(''));
        $this->assertFalse(Validators::integer(null));
        $this->assertFalse(Validators::integer(false));
        $this->assertFalse(Validators::integer(2, 5));

        $this->assertTrue(Validators::integer(0));
        $this->assertTrue(Validators::integer(1));
        $this->assertTrue(Validators::integer(-1));
        $this->assertTrue(Validators::integer('1'));
        $this->assertTrue(Validators::integer(-2, -3, 1));
        $this->assertTrue(Validators::integer(2, 2, 5));
        $this->assertTrue(Validators::integer(2, null, 5));
    }

    public function testNumber()
    {
        $this->assertFalse(Validators::number(''));
        $this->assertFalse(Validators::number(-1));
        $this->assertFalse(Validators::number(0));

        $this->assertTrue(Validators::number(1));
        $this->assertTrue(Validators::number(10, 5, 12));
        $this->assertTrue(Validators::number(10, 12, 5));
        $this->assertTrue(Validators::number(10, null, 12));
    }

    public function testString()
    {
        $this->assertFalse(Validators::string(false));
        $this->assertFalse(Validators::string(null));
        $this->assertFalse(Validators::string(true));
        $this->assertFalse(Validators::string(0));
        $this->assertFalse(Validators::string('test', 2, 3));
        $this->assertFalse(Validators::string('test', 5));

        $this->assertTrue(Validators::string(''));
        $this->assertTrue(Validators::string('test', 2, 5));
    }

    public function testAlpha()
    {
        $this->assertFalse(Validators::alpha(5));
        $this->assertFalse(Validators::alpha('5'));
        $this->assertTrue(Validators::alpha('test'));
    }

    public function testAccepted()
    {
        $this->assertFalse(Validators::accepted(5));
        $this->assertFalse(Validators::accepted('5'));
        $this->assertFalse(Validators::accepted('no'));
        $this->assertFalse(Validators::accepted('off'));
        $this->assertFalse(Validators::accepted('OFF'));
        $this->assertFalse(Validators::accepted('0'));
        $this->assertFalse(Validators::accepted(0));
        $this->assertFalse(Validators::accepted(false));
        $this->assertFalse(Validators::accepted('false'));

        $this->assertTrue(Validators::accepted('yes'));
        $this->assertTrue(Validators::accepted('Yes'));
        $this->assertTrue(Validators::accepted('on'));
        $this->assertTrue(Validators::accepted('ON'));
        $this->assertTrue(Validators::accepted('1'));
        $this->assertTrue(Validators::accepted(1));
        $this->assertTrue(Validators::accepted(true));
        $this->assertTrue(Validators::accepted('true'));
    }

    public function testAlphaNum()
    {
        $this->assertFalse(Validators::alphaNum('='));
        $this->assertTrue(Validators::alphaNum(5));
        $this->assertTrue(Validators::alphaNum('5'));
        $this->assertTrue(Validators::alphaNum('6787test'));
        $this->assertTrue(Validators::alphaNum('test'));
    }

    public function testAlphaDash()
    {
        $this->assertFalse(Validators::alphaDash('test='));

        $this->assertTrue(Validators::alphaDash('sdf56-_'));
    }

    public function testSize()
    {
        $this->assertFalse(Validators::size('test', 5));
        $this->assertFalse(Validators::size(56, 20, 50));

        $this->assertTrue(Validators::size(56, 20, 100));
        $this->assertTrue(Validators::size('test', 2, 4));
        $this->assertTrue(Validators::size([3, 'test', 'hi'], 1, 4));
    }

    public function testMin()
    {
        $this->assertFalse(Validators::min('test', 5));
        $this->assertFalse(Validators::min(56, 60));

        $this->assertTrue(Validators::min(56, 20));
        $this->assertTrue(Validators::min('test', 2));
        $this->assertTrue(Validators::min([3, 'test', 'hi'], 2));
    }

    public function testMax()
    {
        $this->assertFalse(Validators::max('test', 3));
        $this->assertFalse(Validators::max(56, 50));

        $this->assertTrue(Validators::max(56, 60));
        $this->assertTrue(Validators::max('test', 5));
        $this->assertTrue(Validators::max([3, 'test', 'hi'], 5));
    }

    public function testLength()
    {
        $this->assertFalse(Validators::length('test', 5));
        $this->assertFalse(Validators::length('test', 0, 3));
        $this->assertFalse(Validators::length(56, 60));

        $this->assertTrue(Validators::length('test', 3, 5));
        $this->assertTrue(Validators::length([3, 'test', 'hi'], 2, 5));
    }

    public function testRegexp()
    {
        $this->assertFalse(Validators::regexp('test', '/^\d+$/'));
        $this->assertFalse(Validators::regexp('test-dd', '/^\w+$/'));

        $this->assertTrue(Validators::regexp('test56', '/^\w+$/'));
    }

    public function testUrl()
    {
        $this->assertFalse(Validators::url('test'));
        $this->assertFalse(Validators::url('/test56'));

        $this->assertTrue(Validators::url('http://a.com/test56'));
    }

    public function testEmail()
    {
        $this->assertFalse(Validators::email('test'));
        $this->assertFalse(Validators::email('/test56'));

        $this->assertTrue(Validators::email('abc@gmail.com'));
    }

    public function testIp()
    {
        $this->assertFalse(Validators::ip('test'));
        $this->assertFalse(Validators::ip('/test56'));

        $this->assertTrue(Validators::ip('0.0.0.0'));
        $this->assertTrue(Validators::ip('127.0.0.1'));
    }

    public function testIsArray()
    {
        $this->assertFalse(Validators::isArray('test'));
        $this->assertFalse(Validators::isArray(345));

        $this->assertTrue(Validators::isArray([]));
        $this->assertTrue(Validators::isArray(['a']));
    }

    public function testIsMap()
    {
        $this->assertFalse(Validators::isMap('test'));
        $this->assertFalse(Validators::isMap([]));
        $this->assertFalse(Validators::isMap(['abc']));

        $this->assertTrue(Validators::isMap(['a' => 'v']));
        $this->assertTrue(Validators::isMap(['value', 'a' => 'v']));
    }

    public function testIsList()
    {
        $this->assertFalse(Validators::isList('test'));
        $this->assertFalse(Validators::isList([]));
        $this->assertFalse(Validators::isList(['a' => 'v']));
        $this->assertFalse(Validators::isList(['value', 'a' => 'v']));
        $this->assertFalse(Validators::isList([3 => 'abc']));

        $this->assertTrue(Validators::isList(['abc']));
        $this->assertTrue(Validators::isList(['abc', 565, null]));
    }

    public function testIntList()
    {
        $this->assertFalse(Validators::intList('test'));
        $this->assertFalse(Validators::intList([]));
        $this->assertFalse(Validators::intList(['a', 'v']));
        $this->assertFalse(Validators::intList(['a', 456]));
        $this->assertFalse(Validators::intList(['a' => 'v']));
        $this->assertFalse(Validators::intList(['value', 'a' => 'v']));
        $this->assertFalse(Validators::intList([2 => '343', 45]));

        $this->assertTrue(Validators::intList(['343', 45]));
        $this->assertTrue(Validators::intList([565, 3234, -56]));
    }

    public function testNumList()
    {
        $this->assertFalse(Validators::numList('test'));
        $this->assertFalse(Validators::numList([]));
        $this->assertFalse(Validators::numList(['a', 'v']));
        $this->assertFalse(Validators::numList(['a' => 'v']));
        $this->assertFalse(Validators::numList(['value', 'a' => 'v']));
        $this->assertFalse(Validators::numList([565, 3234, -56]));
        $this->assertFalse(Validators::numList([2 => 56, 45]));

        $this->assertTrue(Validators::numList(['343', 45]));
        $this->assertTrue(Validators::numList([56, 45]));
    }

    public function testStrList()
    {
        $this->assertFalse(Validators::strList('test'));
        $this->assertFalse(Validators::strList([]));
        $this->assertFalse(Validators::strList(['a' => 'v']));
        $this->assertFalse(Validators::strList(['value', 'a' => 'v']));
        $this->assertFalse(Validators::strList(['abc', 565]));
        $this->assertFalse(Validators::strList(['abc', 565, null]));

        $this->assertTrue(Validators::strList(['abc', 'efg']));
    }

    public function testArrList()
    {
        $this->assertFalse(Validators::arrList('test'));
        $this->assertFalse(Validators::arrList([]));
        $this->assertFalse(Validators::arrList(['a' => 'v']));
        $this->assertFalse(Validators::arrList(['value', 'a' => 'v']));
        $this->assertFalse(Validators::arrList(['abc', 565]));
        $this->assertFalse(Validators::arrList([
            ['abc'], 'efg'
        ]));

        $this->assertTrue(Validators::arrList([
            ['abc'], ['efg']
        ]));
    }

    public function testDistinct()
    {
        $this->assertFalse(Validators::distinct([1,2,2]));
        $this->assertFalse(Validators::distinct([1,2,'2']));
        $this->assertFalse(Validators::distinct(['a','b','b']));

        $this->assertTrue(Validators::distinct([1,2,3]));
        $this->assertTrue(Validators::distinct(['a','b','c']));
    }

    public function testJson()
    {
        $this->assertFalse(Validators::json('test'));
        $this->assertFalse(Validators::json([]));

        $this->assertFalse(Validators::json(123));
        $this->assertFalse(Validators::json('123'));
        $this->assertTrue(Validators::json('123', false));

        $this->assertFalse(Validators::json('{aa: 34}'));

        $this->assertTrue(Validators::json('{}'));
        $this->assertTrue(Validators::json('[]'));
        $this->assertTrue(Validators::json('{"aa": 34}'));
    }

    public function testHasKey()
    {
        $this->assertFalse(Validators::hasKey('hello, world', 'all'));
        $this->assertFalse(Validators::hasKey(['a' => 'v0', 'b' => 'v1', 'c' => 'v2'], 'd'));
        $this->assertFalse(Validators::hasKey(['a' => 'v0', 'b' => 'v1', 'c' => 'v2'], ['c', 'd']));

        $this->assertTrue(Validators::hasKey(['a' => 'v0', 'b' => 'v1', 'c' => 'v2'], 'b'));
        $this->assertTrue(Validators::hasKey(['a' => 'v0', 'b' => 'v1', 'c' => 'v2'], ['b', 'c']));
    }

    public function testContains()
    {
        $this->assertFalse(Validators::contains('hello, world', 'all'));

        $this->assertTrue(Validators::contains('hello, world', 'llo'));
        $this->assertTrue(Validators::contains('hello, world', ['llo', 'wor']));
    }

    public function testStartWith()
    {
        $this->assertFalse(Validators::startWith('hello, world', 'ell'));

        $this->assertTrue(Validators::startWith('hello, world', 'hell'));
        $this->assertTrue(Validators::startWith(['hello', 'world'], 'hello'));
    }

    public function testEndWith()
    {
        $this->assertFalse(Validators::endWith('hello, world', 'ell'));

        $this->assertTrue(Validators::endWith('hello, world', 'world'));
        $this->assertTrue(Validators::endWith(['hello', 'world'], 'world'));
    }

    public function testDate()
    {
        $this->assertFalse(Validators::date('hello'));

        $this->assertTrue(Validators::date(170526));
        $this->assertTrue(Validators::date('20170526'));
    }

    public function testDateFormat()
    {
        $this->assertFalse(Validators::dateFormat('hello'));
        // $t = strtotime('20170526');
// var_dump($t, time(), date('ymd', $t));
        $this->assertFalse(Validators::dateFormat('170526', 'ymd'));
        $this->assertTrue(Validators::dateFormat('20170526', 'Ymd'));
    }
}

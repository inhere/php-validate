<?php declare(strict_types=1);

namespace Inhere\ValidateTest;

use Inhere\Validate\Validators;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Class ValidatorsTest
 * @package Inhere\ValidateTest
 */
class ValidatorsTest extends TestCase
{
    public function testAliases(): void
    {
        $this->assertSame('neqField', Validators::realName('diff'));
        $this->assertSame('not-exist', Validators::realName('not-exist'));
    }

    public function testIsEmpty(): void
    {
        $this->assertFalse(Validators::isEmpty(1));
        $this->assertFalse(Validators::isEmpty(0));
        $this->assertFalse(Validators::isEmpty(false));

        $this->assertTrue(Validators::isEmpty(null));
        $this->assertTrue(Validators::isEmpty([]));
        $this->assertTrue(Validators::isEmpty(new stdClass()));
        $this->assertTrue(Validators::isEmpty(''));
        $this->assertTrue(Validators::isEmpty(' '));
    }

    public function testBool(): void
    {
        $this->assertFalse(Validators::bool(null));
        $this->assertFalse(Validators::bool([]));

        $this->assertTrue(Validators::bool('1'));
        $this->assertTrue(Validators::bool(1));
        $this->assertTrue(Validators::bool(''));
    }

    public function testFloat(): void
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
        $this->assertTrue(Validators::float(3.4, 4.1, 2.4));
        $this->assertTrue(Validators::float(3.4, null, 5.4));
        $this->assertTrue(Validators::float(0.8, 0, 4));
        $this->assertTrue(Validators::float(0.5, 0.3, 0.7));
    }

    public function testInteger(): void
    {
        $this->assertFalse(Validators::int(''));
        $this->assertFalse(Validators::integer(null));
        $this->assertFalse(Validators::integer(false));
        $this->assertFalse(Validators::integer(2, 5));

        $this->assertTrue(Validators::int(0));
        $this->assertTrue(Validators::integer(1));
        $this->assertTrue(Validators::integer(-1));
        $this->assertTrue(Validators::integer('1'));
        $this->assertTrue(Validators::integer(-2, -3, 1));
        $this->assertTrue(Validators::integer(2, 2, 5));
        $this->assertTrue(Validators::integer(2, null, 5));
    }

    public function testNumber(): void
    {
        $this->assertFalse(Validators::number(''));
        $this->assertFalse(Validators::number(-1));
        $this->assertFalse(Validators::number(0));

        $this->assertTrue(Validators::num(1));
        $this->assertTrue(Validators::number(10, 5, 12));
        $this->assertTrue(Validators::number(10, 12, 5));
        $this->assertTrue(Validators::number(10, null, 12));
    }

    public function testString(): void
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

    public function testAlpha(): void
    {
        $this->assertFalse(Validators::alpha(5));
        $this->assertFalse(Validators::alpha('5'));
        $this->assertTrue(Validators::alpha('test'));
    }

    public function testAccepted(): void
    {
        $samples = [
            [5, false],
            ['5', false],
            ['no', false],
            ['off', false],
            ['OFF', false],
            [0, false],
            ['0', false],
            [false, false],
            ['false', false],
            [[], false],

            ['yes', true],
            ['Yes', true],
            ['YES', true],
            ['on', true],
            ['ON', true],
            ['1', true],
            [1, true],
            [true, true],
            ['true', true],
        ];

        foreach ($samples as [$val, $want]) {
            $this->assertSame($want, Validators::accepted($val));
        }
    }

    public function testAlphaNum(): void
    {
        $this->assertFalse(Validators::alphaNum('='));
        $this->assertFalse(Validators::alphaNum(null));
        $this->assertFalse(Validators::alphaNum(true));
        $this->assertFalse(Validators::alphaNum([]));
        $this->assertTrue(Validators::alphaNum(5));
        $this->assertTrue(Validators::alphaNum('5'));
        $this->assertTrue(Validators::alphaNum('6787test'));
        $this->assertTrue(Validators::alphaNum('test'));
    }

    public function testAlphaDash(): void
    {
        $this->assertFalse(Validators::alphaDash('='));
        $this->assertFalse(Validators::alphaDash(null));
        $this->assertFalse(Validators::alphaDash('test='));

        $this->assertTrue(Validators::alphaDash('sdf56-_'));
    }

    public function testMin(): void
    {
        $this->assertFalse(Validators::min('test', 5));
        $this->assertFalse(Validators::min(56, 60));

        $this->assertTrue(Validators::min(56, 20));
        $this->assertTrue(Validators::min('test', 2));
        $this->assertTrue(Validators::min([3, 'test', 'hi'], 2));
    }

    public function testMax(): void
    {
        $this->assertFalse(Validators::max('test', 3));
        $this->assertFalse(Validators::max(56, 50));

        $this->assertTrue(Validators::max(56, 60));
        $this->assertTrue(Validators::max('test', 5));
        $this->assertTrue(Validators::max([3, 'test', 'hi'], 5));
    }

    // eq neq gt gte lt lte
    public function testValueCompare(): void
    {
        // eq
        $this->assertTrue(Validators::same(6, 6));
        $this->assertTrue(Validators::eq(true, true));
        $this->assertTrue(Validators::eq(null, null));
        $this->assertFalse(Validators::eq(false, null));
        $this->assertFalse(Validators::eq(5, '5'));
        $this->assertTrue(Validators::eq(5, '5', false));
        $this->assertFalse(Validators::eq(false, 0));
        $this->assertTrue(Validators::eq(false, 0, false));

        // neq
        $this->assertFalse(Validators::neq(5, '5', false));
        $this->assertTrue(Validators::neq(5, '5'));

        // gt
        $this->assertTrue(Validators::gt(6, 5));
        $this->assertTrue(Validators::gt('abc', 'ab'));
        $this->assertTrue(Validators::gt([1, 2], [2]));
        $this->assertFalse(Validators::gt(4, 4));
        $this->assertFalse(Validators::gt(3, 4));

        // gte
        $this->assertTrue(Validators::gte(6, 5));
        $this->assertTrue(Validators::gte('abc', 'ab'));
        $this->assertTrue(Validators::gte([1, 2], [2]));
        $this->assertTrue(Validators::gte(4, 4));
        $this->assertFalse(Validators::gte(2, 4));

        // lt
        $samples = [
            [5, 6, true],
            [[1], [1, 'a'], true],
            ['a', 'ab', true],
            [6, 6, false],
            [[1], [1], false],
            ['a', 'a', false],
            [1, 'a', false],
        ];
        foreach ($samples as $v) {
            $this->assertSame($v[2], Validators::lt($v[0], $v[1]));
        }

        // lte
        $samples = [
            [5, 6, true],
            [[1], [1, 'a'], true],
            ['a', 'ab', true],
            [6, 6, true],
            [[1], [1], true],
            ['a', 'a', true],
            [1, 'a', false],
            [6, 5, false],
        ];
        foreach ($samples as $v) {
            $this->assertSame($v[2], Validators::lte($v[0], $v[1]));
        }
    }

    public function testSize(): void
    {
        $this->assertFalse(Validators::size('test', 5));
        $this->assertFalse(Validators::size(null, 5));
        $this->assertFalse(Validators::range(new stdClass(), 5));
        $this->assertFalse(Validators::between(56, 20, 50));

        $this->assertTrue(Validators::size(56, 20, 100));
        $this->assertTrue(Validators::size('test', 2, 4));
        $this->assertTrue(Validators::size([3, 'test', 'hi'], 1, 4));
        $this->assertTrue(Validators::size(0.8, 0, 4));
    }

    public function testSize_float(): void
    {
        $this->assertTrue(Validators::size(0.8, 0, 4));
        $this->assertTrue(Validators::size(0.5, 0.3, 0.7));
    }

    public function testFixedSize(): void
    {
        $this->assertTrue(Validators::sizeEq([3, 'test', 'hi'], 3));
        $this->assertTrue(Validators::lengthEq('test', 4));
    }

    public function testLength(): void
    {
        $this->assertFalse(Validators::length('test', 5));
        $this->assertFalse(Validators::length('test', 0, 3));
        $this->assertFalse(Validators::length(56, 60));

        $this->assertTrue(Validators::length('test', 3, 5));
        $this->assertTrue(Validators::length([3, 'test', 'hi'], 2, 5));
    }

    public function testRegexp(): void
    {
        $this->assertFalse(Validators::regexp('test', '/^\d+$/'));
        $this->assertFalse(Validators::regexp('test-dd', '/^\w+$/'));

        $this->assertTrue(Validators::regexp('compose启动服务', '/[\x{4e00}-\x{9fa5}]+/u'));
        $this->assertTrue(Validators::regexp('test56', '/^\w+$/'));
    }

    public function testUrl(): void
    {
        $this->assertFalse(Validators::url('test'));
        $this->assertFalse(Validators::url('/test56'));

        $this->assertTrue(Validators::url('http://a.com/test56'));
    }

    public function testEmail(): void
    {
        $this->assertFalse(Validators::email('test'));
        $this->assertFalse(Validators::email('/test56'));

        $this->assertTrue(Validators::email('abc@gmail.com'));
    }

    public function testIp(): void
    {
        $this->assertFalse(Validators::ip('test'));
        $this->assertFalse(Validators::ip('/test56'));
        $this->assertFalse(Validators::ipv4('/test56'));
        $this->assertFalse(Validators::ipv6('/test56'));

        $this->assertTrue(Validators::ip('0.0.0.0'));
        $this->assertTrue(Validators::ip('127.0.0.1'));
        $this->assertTrue(Validators::ipv4('127.0.0.1'));
        $this->assertTrue(Validators::ipv6('2400:3200::1'));
        $this->assertTrue(Validators::ipv6('0:0:0:0:0:0:0:0'));
    }

    public function testEnglish(): void
    {
        $this->assertFalse(Validators::english(123));
        $this->assertFalse(Validators::english('123'));
        $this->assertTrue(Validators::english('test'));
    }

    public function testIsArray(): void
    {
        $this->assertFalse(Validators::isArray('test'));
        $this->assertFalse(Validators::isArray(345));

        $this->assertTrue(Validators::isArray([]));
        $this->assertTrue(Validators::isArray(['a']));
    }

    public function testIsMap(): void
    {
        $this->assertFalse(Validators::isMap('test'));
        $this->assertFalse(Validators::isMap([]));
        $this->assertFalse(Validators::isMap(['abc']));

        $this->assertTrue(Validators::isMap(['a' => 'v']));
        $this->assertTrue(Validators::isMap(['value', 'a' => 'v']));
    }

    public function testIsList(): void
    {
        $this->assertFalse(Validators::isList('test'));
        $this->assertFalse(Validators::isList([]));
        $this->assertFalse(Validators::isList(['a' => 'v']));
        $this->assertFalse(Validators::isList(['value', 'a' => 'v']));
        $this->assertFalse(Validators::isList([3 => 'abc']));
        $this->assertFalse(Validators::isList(['abc', 3 => 45]));

        $this->assertTrue(Validators::isList(['abc']));
        $this->assertTrue(Validators::isList(['abc', 565, null]));
    }

    public function testIntList(): void
    {
        $this->assertFalse(Validators::intList('test'));
        $this->assertFalse(Validators::intList([]));
        $this->assertFalse(Validators::intList(['a', 'v']));
        $this->assertFalse(Validators::intList(['a', 456]));
        $this->assertFalse(Validators::intList(['a' => 'v']));
        $this->assertFalse(Validators::intList(['value', 'a' => 'v']));
        $this->assertFalse(Validators::intList([2 => '343', 45]));
        $this->assertFalse(Validators::intList([45, 2 => '343']));

        $this->assertTrue(Validators::intList(['343', 45]));
        $this->assertTrue(Validators::intList([565, 3234, -56]));
    }

    public function testNumList(): void
    {
        $this->assertFalse(Validators::numList('test'));
        $this->assertFalse(Validators::numList([]));
        $this->assertFalse(Validators::numList(['a', 'v']));
        $this->assertFalse(Validators::numList(['a' => 'v']));
        $this->assertFalse(Validators::numList(['value', 'a' => 'v']));
        $this->assertFalse(Validators::numList([565, 3234, -56]));
        $this->assertFalse(Validators::numList([2 => 56, 45]));
        $this->assertFalse(Validators::numList([45, 2 => 56]));

        $this->assertTrue(Validators::numList(['343', 45]));
        $this->assertTrue(Validators::numList([56, 45]));
    }

    public function testStrList(): void
    {
        $this->assertFalse(Validators::strList('test'));
        $this->assertFalse(Validators::strList([]));
        $this->assertFalse(Validators::strList(['a' => 'v']));
        $this->assertFalse(Validators::strList(['value', 'a' => 'v']));
        $this->assertFalse(Validators::strList(['abc', 565]));
        $this->assertFalse(Validators::strList(['abc', 565, null]));

        $this->assertTrue(Validators::strList(['abc', 'efg']));
    }

    public function testArrList(): void
    {
        $this->assertFalse(Validators::arrList('test'));
        $this->assertFalse(Validators::arrList([]));
        $this->assertFalse(Validators::arrList(['a' => 'v']));
        $this->assertFalse(Validators::arrList(['value', 'a' => 'v']));
        $this->assertFalse(Validators::arrList(['abc', 565]));
        $this->assertFalse(Validators::arrList([
            ['abc'],
            'efg'
        ]));

        $this->assertTrue(Validators::arrList([
            ['abc'],
            ['efg']
        ]));
    }

    public function testHasKey(): void
    {
        $this->assertFalse(Validators::hasKey('hello, world', 'all'));
        $this->assertFalse(Validators::hasKey('hello, world', true));
        $this->assertFalse(Validators::hasKey(['a' => 'v0', 'b' => 'v1', 'c' => 'v2'], 'd'));
        $this->assertFalse(Validators::hasKey(['a' => 'v0', 'b' => 'v1', 'c' => 'v2'], ['c', 'd']));

        $this->assertTrue(Validators::hasKey(['a' => 'v0', 'b' => 'v1', 'c' => 'v2'], 'b'));
        $this->assertTrue(Validators::hasKey(['a' => 'v0', 'b' => 'v1', 'c' => 'v2'], ['b', 'c']));
    }

    public function testDistinct(): void
    {
        $this->assertFalse(Validators::distinct('string'));
        $this->assertFalse(Validators::distinct([1, 2, 2]));
        $this->assertFalse(Validators::distinct([1, 2, '2']));
        $this->assertFalse(Validators::distinct(['a', 'b', 'b']));

        $this->assertTrue(Validators::distinct([1, 2, 3]));
        $this->assertTrue(Validators::distinct(['a', 'b', 'c']));
    }

    public function testInANDNotIn(): void
    {
        $samples = [
            [true, 1, [1, 2, 3], false],
            [true, 1, [1, 2, 3], true],
            [true, '1', [1, 2, 3], false],
            [false, '1', [1, 2, 3], true],
            [true, '1', '1,2,3', true],
        ];

        foreach ($samples as [$want, $val, $dict, $strict]) {
            $this->assertSame($want, Validators::in($val, $dict, $strict));
            $this->assertSame(!$want, Validators::notIn($val, $dict, $strict));
        }
    }

    public function testJson(): void
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

    public function testContains(): void
    {
        $this->assertFalse(Validators::contains('hello, world', 'all'));
        $this->assertFalse(Validators::contains(null, 'all'));
        $this->assertFalse(Validators::contains([], 'all'));
        $this->assertFalse(Validators::contains('hello, world', false));

        $this->assertTrue(Validators::contains('123', 2));
        $this->assertTrue(Validators::contains('hello, world', 'llo'));
        $this->assertTrue(Validators::contains('hello, world', ['llo', 'wor']));
    }

    public function testStartWith(): void
    {
        $this->assertFalse(Validators::startWith(null, 'ell'));
        $this->assertFalse(Validators::startWith('hello, world', 'ell'));
        $this->assertFalse(Validators::startWith('hello, world', ''));

        $this->assertTrue(Validators::startWith('hello, world', 'hell'));
        $this->assertTrue(Validators::startWith(['hello', 'world'], 'hello'));
    }

    public function testEndWith(): void
    {
        $this->assertFalse(Validators::endWith('hello, world', 'ell'));

        $this->assertTrue(Validators::endWith('hello, world', 'world'));
        $this->assertTrue(Validators::endWith(['hello', 'world'], 'world'));
    }

    public function testDateCheck(): void
    {
        // date
        $this->assertFalse(Validators::date('hello'));
        $this->assertTrue(Validators::date(170526));
        $this->assertTrue(Validators::date('20170526'));

        // dateEquals
        $this->assertTrue(Validators::dateEquals('20170526', '20170526'));
        $this->assertTrue(Validators::dateEquals('2017-05-26', '20170526'));
        $this->assertFalse(Validators::dateEquals('20170525', '20170526'));

        // dateFormat
        $this->assertFalse(Validators::dateFormat('hello'));
        $this->assertFalse(Validators::dateFormat('170526', 'ymd'));
        $this->assertTrue(Validators::dateFormat('20170526', 'Ymd'));

        // beforeDate
        $this->assertTrue(Validators::beforeDate('20170524', '20170526'));
        $this->assertFalse(Validators::beforeDate('20170526', '20170526'));

        // beforeOrEqualDate
        $this->assertTrue(Validators::beforeOrEqualDate('20170524', '20170526'));
        $this->assertTrue(Validators::beforeOrEqualDate('20170526', '20170526'));
        $this->assertFalse(Validators::beforeOrEqualDate('20170527', '20170526'));

        // afterDate
        $this->assertTrue(Validators::afterDate('20170526', '20170524'));
        $this->assertFalse(Validators::afterDate('20170526', '20170526'));
        $this->assertFalse(Validators::afterDate('20170524', '20170526'));
        $this->assertFalse(Validators::afterDate([], '20170526'));

        // afterOrEqualDate
        $this->assertTrue(Validators::afterOrEqualDate('20170526', '20170526'));
        $this->assertTrue(Validators::afterOrEqualDate('20170526', '20170524'));
        $this->assertFalse(Validators::afterOrEqualDate('20170524', '20170526'));

        // isDate
        $this->assertTrue(Validators::isDate('2017-05-26'));
        $this->assertFalse(Validators::isDate('20170526'));
        // isDateFormat
        $this->assertTrue(Validators::isDateFormat('2017-05-26'));
        $this->assertFalse(Validators::isDateFormat('20170526'));
    }

    public function testPhone(): void
    {
        $this->assertTrue(Validators::phone('13555556666'));
        $this->assertFalse(Validators::phone('20170526'));
    }

    public function testPostCode(): void
    {
        $this->assertTrue(Validators::postCode('610000'));
        $this->assertFalse(Validators::postCode('20170526'));
    }

    public function testPrice(): void
    {
        $this->assertTrue(Validators::price('610.45'));
        $this->assertFalse(Validators::price('-201.26'));
        $this->assertFalse(Validators::price('abc'));

        $this->assertTrue(Validators::negativePrice('610.45'));
        $this->assertTrue(Validators::negativePrice('-201.26'));
        $this->assertFalse(Validators::negativePrice('abc'));
    }

    public function testOther(): void
    {
        // isFloat
        $this->assertFalse(Validators::isFloat([]));
        $this->assertFalse(Validators::isFloat('abc'));
        $this->assertTrue(Validators::isFloat('23.34'));
        $this->assertTrue(Validators::isFloat('-23.34'));

        // isUnsignedFloat
        $this->assertTrue(Validators::isUnsignedFloat('23.34'));
        $this->assertFalse(Validators::isUnsignedFloat('-23.34'));

        // isInt
        $this->assertTrue(Validators::isInt('23'));
        $this->assertTrue(Validators::isInt('-23'));
        $this->assertFalse(Validators::isInt('-23.34'));

        // isUnsignedInt
        $this->assertTrue(Validators::isUnsignedInt('23'));
        $this->assertFalse(Validators::isUnsignedInt('-23'));
        $this->assertFalse(Validators::isUnsignedInt('-23.34'));

        // macAddress
        $this->assertTrue(Validators::macAddress('01:23:45:67:89:ab'));
        $this->assertFalse(Validators::macAddress([]));
        $this->assertFalse(Validators::macAddress(null));
        $this->assertFalse(Validators::macAddress('123 abc'));

        // md5
        $this->assertFalse(Validators::md5('123 abc'));
        $this->assertFalse(Validators::md5(true));

        // sha1
        $this->assertFalse(Validators::sha1(true));
        $this->assertFalse(Validators::sha1('123 abc'));
    }
}

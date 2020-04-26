<?php declare(strict_types=1);

namespace Inhere\ValidateTest\Filter;

use Inhere\Validate\Filter\Filters;
use PHPUnit\Framework\TestCase;
use const FILTER_FLAG_ENCODE_HIGH;
use const FILTER_FLAG_ENCODE_LOW;
use const FILTER_FLAG_STRIP_HIGH;

/**
 * Class FiltersTest
 *
 * @package Inhere\ValidateTest\Filter
 */
class FiltersTest extends TestCase
{
    public function testBool(): void
    {
        $samples = [
            '1'     => true,
            'yes'   => true,
            'Yes'   => true,
            'YEs'   => true,
            'true'  => true,
            'True'  => true,
            '0'     => false,
            'no'    => false,
            'off'   => false,
            'false' => false,
            'False' => false,
            null    => false,
        ];

        foreach ($samples as $sample => $expected) {
            $this->assertSame($expected, Filters::bool($sample));
        }

        $this->assertFalse(Filters::bool([]));
    }

    public function testAliases(): void
    {
        $this->assertTrue(Filters::hasAlias('str2list'));
        $this->assertSame('explode', Filters::realName('str2list'));

        $this->assertFalse(Filters::hasAlias('not-exist'));
        $this->assertSame('not-exist', Filters::realName('not-exist'));

        $this->assertFalse(Filters::hasAlias('new-key'));
        Filters::setAliases([
            'new-key' => 'new-val',
        ]);
        $this->assertTrue(Filters::hasAlias('new-key'));
        $this->assertSame('new-val', Filters::realName('new-key'));
    }

    public function testInteger(): void
    {
        $this->assertSame(Filters::integer('456'), 456);
        $this->assertSame(Filters::integer('4df5dg6'), 456);

        $this->assertSame(Filters::integer(['34', '67gh']), [34, 67]);
    }

    public function testAbs(): void
    {
        $this->assertSame(Filters::abs('456'), 456);
        $this->assertSame(Filters::abs(-45), 45);
    }

    public function testFloat(): void
    {
        //        $this->assertSame(FilterList::float('4.45'), 4.45);
        $this->assertSame(Filters::float(45.78), 45.78);
        $this->assertSame(Filters::float(-45.78), -45.78);

        $this->assertSame(Filters::float(45.78678, 2), 45.79);
        $this->assertSame(Filters::float(457, 2), 457.00);
    }

    public function testString(): void
    {
        self::assertSame('1', Filters::string(1));
        self::assertSame('tom', Filters::string('tom'));
        self::assertSame('tom', Filters::stripped('tom'));
        self::assertSame('abc&amp;', Filters::string('abc&'));
        self::assertSame(['abc&amp;', '1'], Filters::string(['abc&', 1]));
    }

    public function testStringFilter(): void
    {
        // quotes
        $this->assertSame("O\'Reilly?", Filters::quotes("O'Reilly?"));

        // email
        $this->assertSame('', Filters::email([]));

        // url
        $this->assertSame('', Filters::url([]));
        $this->assertSame('abc/hi', Filters::url('abc/hi'));

        // unsafeRaw
        $this->assertSame('abc/hi', Filters::unsafeRaw('abc/hi'));

        // specialChars
        $this->assertSame('&#60;a&#62;link&#60;/a&#62; hello, a &#38; b', Filters::escape('<a>link</a> hello, a & b'));
        $this->assertSame('abc', Filters::specialChars('abc'));

        // fullSpecialChars
        $this->assertSame('hello, a &amp; b', Filters::fullSpecialChars('hello, a & b'));
    }

    public function testNl2br(): void
    {
        $this->assertSame('a<br/>b', Filters::nl2br("a\nb"));
        $this->assertSame('a<br/>b', Filters::nl2br("a\r\nb"));
    }

    public function testStringCut(): void
    {
        $this->assertSame('cDE', Filters::stringCute('abcDEFgh', 2, 3));
        $this->assertSame('abcDEFgh', Filters::cut('abcDEFgh'));
        $this->assertSame('cDEFgh', Filters::cut('abcDEFgh', 2));
        $this->assertSame('abcDEFgh', Filters::cut('abcDEFgh', 0, 12));
    }

    public function testClearXXX(): void
    {
        // clearSpace
        $samples = ['abc ', ' abc ', 'a bc', 'a b c', ' a b c'];

        foreach ($samples as $sample) {
            $this->assertSame('abc', Filters::clearSpace($sample));
        }

        // clearNewline
        self::assertSame('ab', Filters::clearNewline("a\nb"));
        self::assertSame('ab', Filters::clearNewline("a\r\nb"));
    }

    public function testTrim(): void
    {
        $this->assertEquals(Filters::trim(' test '), 'test');
        $this->assertEquals(Filters::trim([' test ', 'a ']), ['test', 'a']);
    }

    public function testChangeCase(): void
    {
        // lowercase
        $this->assertSame('test', Filters::lowercase('Test'));
        $this->assertSame('23', Filters::lowercase(23));

        // uppercase
        $this->assertSame('23', Filters::uppercase(23));
        $this->assertSame(Filters::upper('test'), 'TEST');
        $this->assertSame(Filters::uppercase('Test'), 'TEST');

        // ucfirst
        $this->assertSame('Abc', Filters::ucfirst('abc'));
        $this->assertSame('', Filters::ucfirst(''));
        $this->assertSame('', Filters::ucfirst([]));

        // ucwords
        $this->assertSame('Hello World', Filters::ucwords('hello world'));
        $this->assertSame('', Filters::ucwords(''));
        $this->assertSame('', Filters::ucwords([]));

        // snake case
        $this->assertSame('hello_world', Filters::snake('HelloWorld'));
        $this->assertSame('hello-world', Filters::snake('HelloWorld', '-'));
        $this->assertSame('', Filters::snake(''));
        $this->assertSame('', Filters::snake([]));

        // camel case
        $this->assertSame('helloWorld', Filters::camel('hello_world'));
        $this->assertSame('HelloWorld', Filters::camel('hello_world', true));
        $this->assertSame('', Filters::camel(''));
        $this->assertSame('', Filters::camel([]));
    }

    public function testTime(): void
    {
        $this->assertSame(1563811200, Filters::timestamp('2019-07-23'));
        $this->assertSame(0, Filters::timestamp(''));
        $this->assertSame(0, Filters::timestamp('invalid'));
    }

    public function testStr2list(): void
    {
        $samples = [
            '0,23'        => ['0', '23'],
            'a,b,c,'      => ['a', 'b', 'c'],
            'a, b ,c,'    => ['a', 'b', 'c'],
            ' a, b , c'   => ['a', 'b', 'c'],
            ' a,, b ,, c' => ['a', 'b', 'c'],
        ];

        foreach ($samples as $sample => $expected) {
            $this->assertSame($expected, Filters::str2array($sample));
            $this->assertSame($expected, Filters::explode($sample));
        }

        $this->assertSame([], Filters::explode(''));
        $this->assertSame([], Filters::str2list(' , '));
    }

    public function testUnique(): void
    {
        $this->assertSame([1], Filters::unique(1));
        $this->assertSame([1], Filters::unique([1, 1]));
        $this->assertSame(['a', 'b'], Filters::unique(['a', 'b', 'a']));
        $this->assertSame(['a', 2 => 'b'], Filters::unique(['a', 'a', 'b', 'a']));
    }

    public function testEncodeOrClearTag(): void
    {
        // clearTags
        $samples = [
            ''                   => '',
            '<p>text</p>'        => 'text',
            '<p>text'            => 'text',
            '<p><a>text</a></p>' => 'text',
        ];
        foreach ($samples as $sample => $expected) {
            $this->assertSame($expected, Filters::clearTags($sample));
        }

        $this->assertSame('<a>text</a>', Filters::clearTags('<p><a>text</a></p>', '<a>'));

        // encoded
        $this->assertSame('abc.com%3Fa%3D7%2B9', Filters::encoded('abc.com?a=7+9'));
        $this->assertSame('abc.com%3Fa%3D7%2B9%26b%3D', Filters::encoded('abc.com?a=7+9&b=你', FILTER_FLAG_STRIP_HIGH));
        $this->assertSame('abc.com%3Fa%3D7%2B9%26b%3D%E4%BD%A0', Filters::encoded('abc.com?a=7+9&b=你'));
        $this->assertSame(
            'abc.com%3Fa%3D7%2B9%26b%3D%E4%BD%A0',
            Filters::encoded('abc.com?a=7+9&b=你', FILTER_FLAG_ENCODE_LOW)
        );
        $this->assertSame(
            'abc.com%3Fa%3D7%2B9%26b%3D%E4%BD%A0',
            Filters::encoded('abc.com?a=7+9&b=你', FILTER_FLAG_ENCODE_HIGH)
        );

        // url
        $this->assertSame('', Filters::url(''));
        $this->assertSame('abc.com?a=7+9', Filters::url('abc.com?a=7+9'));
        $this->assertSame('abc.com?a=7+9&b=', Filters::url('abc.com?a=7+9&b=你'));

        // email
        $this->assertSame('', Filters::email(''));
        $this->assertSame('abc@email.com', Filters::email('abc@email.com'));
    }

    public function testCallback(): void
    {
        $this->assertSame('abc', Filters::callback('ABC', 'strtolower'));
        $this->assertSame('abc', Filters::callback('ABC', [Filters::class, 'lower']));
        $this->assertSame('abc', Filters::callback('ABC', Filters::class . '::' . 'lower'));
    }
}

<?php

use PHPUnit\Framework\TestCase;
use Inhere\Validate\Filter\Filters;

/**
 * @covers \Inhere\Validate\Filter\Filters
 */
class FiltersTest extends TestCase
{
    public function testInteger()
    {
        $this->assertSame(Filters::integer('456'), 456);
        $this->assertSame(Filters::integer('4df5dg6'), 456);

        $this->assertSame(Filters::integer(['34', '67gh']), [34, 67]);
    }

    public function testAbs()
    {
        $this->assertSame(Filters::abs('456'), 456);
        $this->assertSame(Filters::abs(-45), 45);
    }

    public function testFloat()
    {
//        $this->assertSame(FilterList::float('4.45'), 4.45);
        $this->assertSame(Filters::float(45.78), 45.78);
        $this->assertSame(Filters::float(-45.78), -45.78);

        $this->assertSame(Filters::float(45.78678, 2), 45.79);
        $this->assertSame(Filters::float(457, 2), 457.00);
    }

    public function testTrim()
    {
        $this->assertEquals(Filters::trim(' test '), 'test');

        $this->assertEquals(Filters::trim([' test ', 'a ']), ['test', 'a']);
    }

    public function testLowercase()
    {
        $this->assertSame(Filters::lowercase('Test'), 'test');
    }

    public function testUppercase()
    {
        $this->assertSame(Filters::uppercase('Test'), 'TEST');
    }

    public function testStr2list()
    {
        $this->assertSame(Filters::str2array('a,b,c,'), ['a', 'b', 'c']);
        $this->assertSame(Filters::str2array('a, b ,c,'), ['a', 'b', 'c']);
        $this->assertSame(Filters::str2array(' a, b , c'), ['a', 'b', 'c']);
        $this->assertSame(Filters::str2array(' a,, b ,, c'), ['a', 'b', 'c']);
    }
}

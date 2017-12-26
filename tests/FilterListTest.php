<?php

use PHPUnit\Framework\TestCase;
use Inhere\Validate\Filter\FilterList;

/**
 * @covers FilterList
 */
class FilterListTest extends TestCase
{
    public function testInteger()
    {
        $this->assertSame(FilterList::integer('456'), 456);
        $this->assertSame(FilterList::integer('4df5dg6'), 456);

        $this->assertSame(FilterList::integer(['34', '67gh']), [34, 67]);
    }

    public function testAbs()
    {
        $this->assertSame(FilterList::abs('456'), 456);
        $this->assertSame(FilterList::abs(-45), 45);
    }

    public function testFloat()
    {
//        $this->assertSame(FilterList::float('4.45'), 4.45);
        $this->assertSame(FilterList::float(45.78), 45.78);
        $this->assertSame(FilterList::float(-45.78), -45.78);

        $this->assertSame(FilterList::float(45.78678, 2), 45.79);
        $this->assertSame(FilterList::float(457, 2), 457.00);
    }

    public function testTrim()
    {
        $this->assertEquals(FilterList::trim(' test '), 'test');

        $this->assertEquals(FilterList::trim([' test ', 'a ']), ['test', 'a']);
    }

    public function testLowercase()
    {
        $this->assertSame(FilterList::lowercase('Test'), 'test');
    }

    public function testUppercase()
    {
        $this->assertSame(FilterList::uppercase('Test'), 'TEST');
    }

    public function testStr2list()
    {
        $this->assertSame(FilterList::str2array('a,b,c,'), ['a', 'b', 'c']);
        $this->assertSame(FilterList::str2array('a, b ,c,'), ['a', 'b', 'c']);
        $this->assertSame(FilterList::str2array(' a, b , c'), ['a', 'b', 'c']);
        $this->assertSame(FilterList::str2array(' a,, b ,, c'), ['a', 'b', 'c']);
    }
}

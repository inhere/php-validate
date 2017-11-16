<?php

use PHPUnit\Framework\TestCase;
use Inhere\Validate\Filter\FilterList;

/**
 * @covers FilterList
 */
class FilterListTest extends TestCase
{
    public function testTrim()
    {
        $this->assertEquals(FilterList::trim(' test '), 'test');

        $this->assertEquals(FilterList::trim([' test ', 'a ']), ['test', 'a']);
    }
}

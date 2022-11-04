<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2019-01-24
 * Time: 00:33
 */

namespace Inhere\ValidateTest\Filter;

use Inhere\Validate\Filter\UserFilters;
use PHPUnit\Framework\TestCase;

/**
 * Class UserFiltersTest
 *
 * @package Inhere\ValidateTest\Filter
 */
class UserFiltersTest extends TestCase
{
    public function testBasic(): void
    {
        UserFilters::removeAll();
        UserFilters::setFilters([
            'name1' => static function () {
            },
            'name2' => static function () {
            },
            ''      => static function () {
            },
        ]);

        $this->assertCount(2, UserFilters::getFilters());
        $this->assertTrue(UserFilters::has('name1'));
        $this->assertFalse(UserFilters::has(''));

        $this->assertNotEmpty(UserFilters::get('name2'));
        $this->assertEmpty(UserFilters::get('name3'));

        UserFilters::add('new1', static function () {
        });
        $this->assertTrue(UserFilters::has('new1'));

        UserFilters::remove('name1');
        $this->assertFalse(UserFilters::has('name1'));

        UserFilters::removeAll();
        $this->assertCount(0, UserFilters::getFilters());
    }
}

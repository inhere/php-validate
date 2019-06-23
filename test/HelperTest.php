<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2019-01-31
 * Time: 10:08
 */

namespace Inhere\ValidateTest;

use Inhere\Validate\Filter\Filters;
use Inhere\Validate\Helper;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Class HelperTest
 * @package Inhere\ValidateTest
 */
class HelperTest extends TestCase
{
    public function testStringCheck()
    {
        $this->assertSame(3, Helper::strPos('string', 'i'));
        $this->assertSame(0, Helper::strPos('string', 's'));
        $this->assertFalse(Helper::strPos('string', 'o'));

        $this->assertSame(5, Helper::strrpos('string', 'g'));
        $this->assertFalse(Helper::strrpos('string', 'o'));
    }

    public function testMimeHelper()
    {
        $this->assertSame('image/jpeg', Helper::getImageMime('jpg'));
        $this->assertSame('', Helper::getImageMime('invalid'));

        $this->assertSame('jpeg', Helper::getImageExtByMime('image/jpeg'));
        $this->assertSame('png', Helper::getImageExtByMime('image/png'));
        $this->assertSame('', Helper::getImageExtByMime('invalid'));

        if (extension_loaded('fileinfo')) {
            $mime = Helper::getMimeType(__DIR__ . '/avatar.jpeg');
            $this->assertSame('image/jpeg', $mime);
            $this->assertSame('', Helper::getMimeType('invalid'));
        }
    }

    public function testCompareSize()
    {
        $this->assertTrue(Helper::compareSize(5, '>', 3));

        $this->assertFalse(Helper::compareSize(true, '>', false));
        $this->assertFalse(Helper::compareSize(5, 'invalid', 3));
    }

    public function testGetValueOfArray()
    {
        $data = [
            'user' => [
                'name' => 'inhere',
                'age'  => 1,
            ]
        ];

        $this->assertNull(Helper::getValueOfArray($data, 'not-exist'));
        $this->assertSame($data, Helper::getValueOfArray($data, null));
    }

    public function testCall()
    {
        // function
        $this->assertSame(34, Helper::call('intval', '34'));

        // class:;method
        $this->assertSame(34, Helper::call(Filters::class . '::integer', '34'));

        $callabled = new class
        {
            public function __invoke($str)
            {
                return (int)$str;
            }
        };

        // callabled object
        $this->assertSame(34, Helper::call($callabled, '34'));

        // invalid
        $this->expectException(InvalidArgumentException::class);
        Helper::call('oo-invalid');
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2019-01-21
 * Time: 09:53
 */

namespace Inhere\ValidateTest\Validator;

use Inhere\Validate\Validator\Messages;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;

/**
 * Class MessagesTest
 * @package Inhere\ValidateTest\Validator
 */
class MessagesTest extends TestCase
{
    public function testBasic()
    {
        Messages::setMessages([
            'key1' => 'val1',
            'key2' => 'val2',
            'key3' => '',
        ]);

        $this->assertNotEmpty(Messages::getMessages());
        $this->assertSame('val1', Messages::get('key1'));
        $this->assertTrue(Messages::has('key1'));
        $this->assertFalse(Messages::has('key3'));

        $needle = 'validation is not through!';

        if (\version_compare(Version::id(), '7.0.0', '<') ) {
            $this->assertContains($needle, Messages::getDefault());
        } else {
            $this->assertStringContainsString($needle, Messages::getDefault());
        }
    }
}

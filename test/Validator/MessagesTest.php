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

        $this->assertContains('validation is not through!', Messages::getDefault());
    }
}

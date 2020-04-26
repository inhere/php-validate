<?php declare(strict_types=1);

namespace Inhere\ValidateTest\Validator;

use Inhere\Validate\Validator\GlobalMessage;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;
use function version_compare;

/**
 * Class GlobalMessageTest
 *
 * @package Inhere\ValidateTest\Validator
 */
class GlobalMessageTest extends TestCase
{
    public function testBasic(): void
    {
        GlobalMessage::setMessages([
            'key1' => 'val1',
            'key2' => 'val2',
            'key3' => '',
        ]);

        $this->assertNotEmpty(GlobalMessage::getMessages());
        $this->assertSame('val1', GlobalMessage::get('key1'));
        $this->assertTrue(GlobalMessage::has('key1'));
        $this->assertFalse(GlobalMessage::has('key3'));

        $needle = 'validation is not through!';

        if (version_compare(Version::id(), '7.0.0', '<')) {
            $this->assertContains($needle, GlobalMessage::getDefault());
        } else {
            $this->assertStringContainsString($needle, GlobalMessage::getDefault());
        }
    }
}

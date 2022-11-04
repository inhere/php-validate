<?php declare(strict_types=1);

namespace Inhere\ValidateTest\Simple;

use Inhere\Validate\Exception\ValidateException;
use Inhere\Validate\Simple\VData;
use Inhere\ValidateTest\BaseValidateTestCase;
use function get_class;

/**
 * class ValidDataTest
 */
class ValidDataTest extends BaseValidateTestCase
{
    public array $testData = [
        'int'  => 23,
        'num'  => '23',
        'str'  => 'abc',
        'str1' => ' abc ',
        'flt'  => '2.33',
        'arr'  => ['ab', 'cd'],
        'ints' => ['23', 25],
        'strs' => ['23', 'cd', 25],
    ];

    protected function setUp(): void
    {
        VData::load($this->testData);
    }

    protected function tearDown(): void
    {
        VData::reset();
    }

    public function testBasicOK(): void
    {
        $data = $this->testData;

        $this->assertSame(23, VData::getInt('int'));
        $this->assertSame(2.33, VData::getFloat('flt'));
        $this->assertSame(23.0, VData::getFloat('int'));
        $this->assertSame('abc', VData::getString('str'));
        $this->assertSame('abc', VData::getString('str1'));
        $this->assertSame($data['arr'], VData::getArray('arr'));
        $this->assertSame($data['arr'], VData::getStrings('arr'));
        $this->assertSame([23, 25], VData::getInts('ints'));

        VData::reset();
        $this->assertEmpty(VData::getData());
    }

    public function testCheckFail_int(): void
    {
        $this->assertNotEmpty(VData::getData());

        $e = $this->runAndGetException(function () {
            VData::getInt('str');
        });
        $this->assertSame(ValidateException::class, get_class($e));
        $this->assertSame("'str' must be int value", $e->getMessage());

        $e = $this->runAndGetException(function () {
            VData::getInt('str', 2);
        });
        $this->assertSame(ValidateException::class, get_class($e));
        $this->assertSame("'str' must be int value and must be greater or equal to 2", $e->getMessage());

        $e = $this->runAndGetException(function () {
            VData::getInt('str', null, 20);
        });
        $this->assertSame(ValidateException::class, get_class($e));
        $this->assertSame("'str' must be int value and must be less than or equal to 20", $e->getMessage());

        $e = $this->runAndGetException(function () {
            VData::getInt('str', 2, 20);
        });
        $this->assertSame(ValidateException::class, get_class($e));
        $this->assertSame("'str' must be int value and must be >= 2 and <= 20", $e->getMessage());
    }
}

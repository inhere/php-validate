<?php declare(strict_types=1);

namespace Inhere\ValidateTest\Traits;

use Inhere\Validate\FieldValidation;
use Inhere\Validate\Validation;
use PHPUnit\Framework\TestCase;

/**
 * Class ErrorMessageTraitTest
 */
class ErrorMessageTraitTest extends TestCase
{
    private $sampleDate = [
        // 'userId' => 234,
        'userId'      => 'is not an integer',
        'tagId'       => '234535',
        // 'freeTime' => '1456767657', // filed not exists
        'note'        => '',
        'name'        => 'Ajohn',
        'status'      => 2,
        'existsField' => 'test',
        'passwd'      => 'password',
        'repasswd'    => 'repassword',
        'insertTime'  => '1456767657',
        'goods'       => [
            'apple' => 34,
            'pear'  => 50,
        ],
    ];

    public function testErrorMessage(): void
    {
        // empty test
        $v = FieldValidation::make($this->sampleDate);

        $this->assertCount(0, $v->getErrors());
        $this->assertCount(0, $v->getMessages());

        $this->assertSame('', $v->firstError());
        $this->assertSame([], $v->firstError(false));

        $v = FieldValidation::check($this->sampleDate, [
            ['userId', 'required|int'],
        ]);

        $this->assertTrue($v->isPrettifyName());
        $this->assertNotEmpty($v->getErrors());
        $this->assertNotEmpty($v->getErrors('userId'));

        // firstError
        $this->assertSame('user id must be an integer!', $v->firstError());
        $this->assertNotEmpty($error = $v->firstError(false));
        $this->assertSame('userId', $error['name']);
        $this->assertSame('user id must be an integer!', $error['msg']);

        // lastError
        $this->assertSame('user id must be an integer!', $v->lastError());
        $this->assertNotEmpty($error = $v->lastError(false));
        $this->assertSame('userId', $error['name']);
        $this->assertSame('user id must be an integer!', $error['msg']);

        // reset validation
        $v->resetValidation();

        // prettifyName
        $v->setPrettifyName(false);
        $this->assertFalse($v->isPrettifyName());

        // re-validate
        $v->validate();

        // firstError
        $this->assertSame('userId must be an integer!', $v->firstError());
        $this->assertNotEmpty($error = $v->firstError(false));
        $this->assertSame('userId', $error['name']);
        $this->assertSame('userId must be an integer!', $error['msg']);

        // lastError
        $this->assertSame('userId must be an integer!', $v->lastError());
        $this->assertNotEmpty($error = $v->lastError(false));
        $this->assertSame('userId', $error['name']);
        $this->assertSame('userId must be an integer!', $error['msg']);
    }

    public function testFieldTranslate(): void
    {
        $v = FieldValidation::make([]);

        // getTranslates
        $this->assertEmpty($v->getTranslates());

        $v->setTranslates([
            'userId' => 'User ID',
        ]);

        $this->assertNotEmpty($v->getTranslates());
        // getTranslate
        $this->assertSame('User ID', $v->getTranslate('userId'));
        $this->assertSame('not exist', $v->getTranslate('notExist'));

        // clearTranslates
        $v->clearTranslates();
        $this->assertEmpty($v->getTranslates());
    }

    /**
     * for https://github.com/inhere/php-validate/issues/10
     */
    public function testForIssues10(): void
    {
        $v = Validation::check([
            'page' => 0
        ], [
            ['page', 'integer', 'min' => 1]
        ]);

        $this->assertTrue($v->isFail());
        $this->assertSame('page must be an integer and minimum value is 1', $v->firstError());
    }
}

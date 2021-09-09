<?php declare(strict_types=1);

namespace Inhere\ValidateTest;

use Inhere\Validate\Validation;

/**
 * class IssuesTest
 */
class IssuesTest extends BaseValidateTestCase
{
    public function testIssue_37(): void
    {
        $post = ['birthDay' => '1960-09-21'];

        $v = Validation::check($post, [
            ['birthDay', 'required'],
            ['birthDay', 'date'],
        ]);

        $this->assertTrue($v->isOk());

        // must > 0
        $v = Validation::check($post, [
            ['birthDay', 'required'],
            ['birthDay', 'date', true],
        ]);

        $this->assertTrue($v->isFail());
    }
}

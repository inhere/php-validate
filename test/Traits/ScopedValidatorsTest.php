<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2019-01-23
 * Time: 01:26
 */

namespace Inhere\ValidateTest\Traits;

use Inhere\Validate\Validation;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;
use const UPLOAD_ERR_INI_SIZE;
use const UPLOAD_ERR_OK;

/**
 * Class ScopedValidatorsTest
 */
class ScopedValidatorsTest extends TestCase
{
    public function testUserValidators(): void
    {
        $v = Validation::make([]);
        $v->clearValidators();
        $v->addValidators([
            'name1' => function () {
            },
            'name2' => function () {
            },
            ''      => function () {
            },
        ]);

        $this->assertCount(2, $v->getValidators());
        $this->assertTrue($v->hasValidator('name1'));
        $this->assertFalse($v->hasValidator(''));

        $this->assertNotEmpty($v->getValidator('name2'));
        $this->assertEmpty($v->getValidator('name3'));

        $v->addValidator('name4', function () {
        });
        $this->assertNotEmpty($v->getValidator('name4'));

        $v->delValidator('name1');
        $this->assertFalse($v->hasValidator('name1'));

        $v->clearValidators();
        $this->assertCount(0, $v->getValidators());
    }

    /**
     * eq, neq, lt, lte, gt, gte field compare...
     */
    public function testFiledCompare(): void
    {
        $v = Validation::make([
            'name' => 'tom',
            'age'  => 34,
            'tags' => ['funny', 'smile'],
        ]);

        // eq
        $this->assertTrue($v->eqFieldValidator(34, 'age'));
        $this->assertFalse($v->eqFieldValidator(334, 'age'));
        $this->assertFalse($v->eqFieldValidator(334, ''));

        // neq
        $this->assertTrue($v->neqFieldValidator(334, 'age'));
        $this->assertFalse($v->neqFieldValidator(34, 'age'));
        $this->assertFalse($v->neqFieldValidator(34, ''));

        // lt
        $this->assertTrue($v->ltFieldValidator(23, 'age'));
        $this->assertFalse($v->ltFieldValidator('23', 'age'));
        $this->assertFalse($v->ltFieldValidator(34, 'age'));
        $this->assertFalse($v->ltFieldValidator(34, ''));

        // lte
        $this->assertTrue($v->lteFieldValidator(23, 'age'));
        $this->assertTrue($v->lteFieldValidator(34, 'age'));
        $this->assertFalse($v->lteFieldValidator('34', 'age'));
        $this->assertFalse($v->lteFieldValidator('34', ''));

        // gt
        $this->assertTrue($v->gtFieldValidator(45, 'age'));
        $this->assertFalse($v->gtFieldValidator('45', 'age'));
        $this->assertFalse($v->gtFieldValidator(23, 'age'));
        $this->assertFalse($v->gtFieldValidator(23, ''));

        // gte
        $this->assertTrue($v->gteFieldValidator(43, 'age'));
        $this->assertTrue($v->gteFieldValidator(34, 'age'));
        $this->assertFalse($v->gteFieldValidator('34', 'age'));
        $this->assertFalse($v->gteFieldValidator('34', ''));

        // in
        $this->assertTrue($v->inFieldValidator('funny', 'tags'));
        $this->assertFalse($v->inFieldValidator('book', 'tags'));
    }

    public function testRequired(): void
    {
        $v = Validation::make([
            'age'      => 23,
            'zero'     => 0,
            'false'    => false,
            'null'     => null,
            'emptyStr' => '',
            'emptyArr' => [],
            'emptyObj' => new stdClass(),
        ]);

        $samples = [
            ['age', true],
            ['zero', true],
            ['false', true],
            ['null', false],
            ['emptyStr', false],
            ['emptyArr', false],
            ['emptyObj', false],
            ['notExist', false],
        ];

        foreach ($samples as $item) {
            $this->assertSame($item[1], $v->required($item[0]));
        }
    }

    public function testRequiredXXX(): void
    {
        $v = Validation::make([
            'nick' => 'tom',
        ]);

        $vs1 = ['john', 'jac'];
        $vs2 = ['john', 'tom'];

        // 如果指定的另一个字段（ anotherField ）值等于任何一个 value 时，此字段为 必填 (refer laravel)
        $ok = $v->requiredIf('name', 'inhere', 'nick', $vs2);
        $this->assertTrue($ok);
        $ok = $v->requiredIf('name', '', 'nick', $vs2);
        $this->assertFalse($ok);
        $ok = $v->requiredIf('name', '', 'nick', $vs1);
        $this->assertNull($ok);

        // 如果指定的另一个字段（ anotherField ）值等于任何一个 value 时，此字段为 不必填(refer laravel)
        $ok = $v->requiredUnless('name', '', 'nick', $vs2);
        $this->assertNull($ok);
        $ok = $v->requiredUnless('name', 'inhere', 'nick', $vs1);
        $this->assertTrue($ok);
    }

    public function testUploadFile(): void
    {
        $v = Validation::make([]);
        $v->setUploadedFiles([
            'file1'      => [
                'name'     => 'some.jpg',
                'tmp_name' => '/tmp/some.jpg',
                'error'    => UPLOAD_ERR_OK,
            ],
            'err_file'   => [
                'name'     => 'some.jpg',
                'tmp_name' => '/tmp/some.jpg',
                'error'    => UPLOAD_ERR_INI_SIZE,
            ],
            'err_suffix' => [
                'name'     => 'some-no-ext',
                'tmp_name' => '/tmp/some.jpg',
                'error'    => UPLOAD_ERR_OK,
            ],
        ]);

        $this->assertNotEmpty($v->getUploadedFiles());
        $this->assertNotEmpty($v->getUploadedFile('file1'));

        $this->assertTrue($v->fileValidator('file1'));
        $this->assertTrue($v->fileValidator('file1', ['jpg']));
        $this->assertFalse($v->fileValidator('err_suffix', ['jpg']));
        $this->assertFalse($v->fileValidator('err_file'));
        $this->assertFalse($v->fileValidator('not-exist'));

        $this->assertFalse($v->imageValidator('err_file'));
        $this->assertFalse($v->imageValidator('not-exist'));
    }

    public function testEachValidator(): void
    {
        $v = Validation::make([
            'tags'  => [3, 4, 5],
            'goods' => ['apple', 'pear'],
            'users' => [
                ['id' => 34, 'name' => 'tom'],
                ['id' => 89, 'name' => 'john'],
            ],
        ]);
        $v->addValidator('my-validator', function () {
            return true;
        });

        $tags = $v->getByPath('tags.*');
        $this->assertFalse($v->eachValidator($tags, 'lt', 4));
        $this->assertTrue($v->eachValidator($tags, 'gt', 2));
        $this->assertTrue($v->eachValidator($tags, 'is_int'));
        $this->assertTrue($v->eachValidator($tags, 'my-validator'));
        $this->assertTrue($v->eachValidator($tags, function () {
            return true;
        }));

        $this->expectException(InvalidArgumentException::class);
        $this->assertTrue($v->eachValidator([]));
    }
}

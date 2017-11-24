<?php

use PHPUnit\Framework\TestCase;
use Inhere\Validate\RuleValidation;

/**
 * @covers Validation
 */
class RuleValidationTest extends TestCase
{
    public $data = [
        // 'userId' => 234,
        'userId' => 'is not an integer',
        'tagId' => '35',
        // 'freeTime' => '1456767657', // filed not exists
        'note' => '',
        'status' => 2,
        'name' => 'john',
        'existsField' => 'test',
        'passwd' => 'password',
        'repasswd' => 'repassword',
        'insertTime' => '1456767657',
        'goods' => [
            'apple' => 34,
            'pear' => 50,
        ],
    ];

    public function testValidatePassed()
    {
        $data = $this->data;
        $data['userId'] = '456';
        $rules = [
            // ['tagId,userId,freeTime', 'required'],// set message
            ['tagId,userId,freeTime', 'number', 'filter' => 'int'],
            ['tagId', 'size', 'max'=> 567, 'min'=> 4, 'filter' => 'int'], // 4<= tagId <=567
            // ['goods', 'isList'],
            ['goods.pear', 'max', 60],
        ];
        $v = RuleValidation::make($data, $rules)
            ->setTranslates([
                'goods.pear' => '梨子'
            ])
            ->setMessages([
                'freeTime.required' => 'freeTime is required!!!!'
            ])
           ->validate([], false);

        $this->assertTrue($v->passed());
        $this->assertFalse($v->failed());
        $this->assertEmpty($v->getErrors());

        $this->assertSame($v->getSafe('userId'), 456);
        $this->assertSame($v->getSafe('tagId'), 35);
    }

    public function testValidateFailed()
    {
        $rules = $this->someRules();
        $v = RuleValidation::make($this->data, $rules)
            ->setTranslates([
                'goods.pear' => '梨子'
            ])
            ->setMessages([
                'freeTime.required' => 'freeTime is required!!!!'
            ])
           ->validate([], false);

        $this->assertFalse($v->passed());
        $this->assertTrue($v->failed());

        $errors = $v->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertTrue(count($errors) > 3);
        $this->assertEquals($v->getSafe('tagId'), null);
    }

    protected function someRules()
    {
        return [
            ['tagId,userId,freeTime', 'required'],// set message
            ['tagId,userId,freeTime', 'number'],
            ['note', 'email', 'skipOnEmpty' => false], // set skipOnEmpty is false.
            ['insertTime', 'email', 'scene' => 'otherScene' ],// set scene. will is not validate it on default.
            ['tagId', 'size', 'max'=> 567, 'min'=> 4, ], // 4<= tagId <=567
            ['passwd', 'compare', 'repasswd'], //

            ['name', 'regexp' ,'/^[a-z]\w{2,12}$/'],

            ['goods.pear', 'max', 30], //

            ['goods', 'isList'], //

             ['notExistsField1', 'requiredWithout', 'notExistsField2'], //
        //    ['notExistsField1', 'requiredWithout', 'existsField'], //

            ['freeTime', 'size', 'min'=>4, 'max'=>567, 'when' => function() {
                echo "  use when pre-check\n";

                // $valid is current validation instance.

                return true;
            }], // 4<= tagId <=567

            ['userId', function(){
                echo "  use custom validate to check userId \n";

                // var_dump($value, $data);
                // echo __LINE__ . "\n";

                return false;
            }, 'msg' => 'userId check failure by closure!'],
        ];
    }
}

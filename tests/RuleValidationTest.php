<?php

use Inhere\Validate\Validation;
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
        'name' => '1234a2',
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

    public function testValidateString()
    {
        $val = '123482';
        $v = Validation::make([
            'user_name' => $val
        ], [
            ['user_name', 'string', 'min' => 6],
            ['user_name', 'string', 'max' => 17],
        ])->validate();

        $this->assertTrue($v->passed());
        $this->assertFalse($v->failed());

        $errors = $v->getErrors();
        $this->assertEmpty($errors);
        $this->assertCount(0, $errors);
        $this->assertEquals($v->getSafe('user_name'), $val);
    }

    public function testValidateJson()
    {
        $v = Validation::make([
            'log_level' => 'debug',
            'log_data' => '[23]',
            'log_data1' => '234',
        ], [
            ['log_level, log_data', 'required'],
            ['log_level, log_data', 'string'],
            ['log_data', 'json'],
            ['log_data1', 'json', false],
        ])->validate();

        // var_dump($v->getErrors());
        $this->assertTrue($v->passed());
        $this->assertFalse($v->failed());

        $errors = $v->getErrors();
        $this->assertEmpty($errors);
        $this->assertCount(0, $errors);
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

<?php

use PHPUnit\Framework\TestCase;

/**
 * @covers Validation
 */
class ValidationTest extends TestCase
{
    public $data = [
        // 'userId' => 234,
        'userId' => 'is not an integer',
        'tagId' => '234535',
        // 'freeTime' => '1456767657', // filed not exists
        'note' => '',
        'name' => 'Ajohn',
        'existsField' => 'test',
        'passwd' => 'password',
        'repasswd' => 'repassword',
        'insertTime' => '1456767657',
        'goods' => [
            'apple' => 34,
            'pear' => 50,
        ],
    ];

    public function testValidate()
    {
        $this->assertEquals(1, '1');
    }

    protected function someRules($value='')
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

            ['freeTime', 'size', 'min'=>4, 'max'=>567, 'when' => function($data, $valid) {
                echo "  use when pre-check\n";

                // $valid is current validation instance.

                return true;
            }], // 4<= tagId <=567

            ['userId', function($value, $data){
                echo "  use custom validate to check userId \n";

                // var_dump($value, $data);
                // echo __LINE__ . "\n";

                return false;
            }, 'msg' => 'userId check failure by closure!'],
        ];
    }
}

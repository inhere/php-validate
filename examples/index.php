<?php

require __DIR__ . '/simple-loader.php';

$data = [
    // 'userId' => 234,
    'userId' => 'is not an integer',
    'tagId' => '234535',
    // 'freeTime' => '1456767657', // filed not exists
    'note' => '',
    'name' => 'Ajohn',
    'existsField' => 'test',
    'passwd' => 'password',
    'repasswd' => 'password',
    'insertTime' => '1456767657',
    'goods' => [
        'apple' => 34,
        'pear' => 50,
    ],
];

$rules = [
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

echo "\n----------------------------\n raw data, waiting to validate\n----------------------------\n\n";

print_r($data);

echo "\n----------------------------\n use ValidationTrait\n----------------------------\n\n";

//$model = new DataModel($_POST,$rules);
$model = new DataModel;
$model->setData($data)->setRules($rules);
$model->setMessages([
    'freeTime.required' => 'freeTime is required!!!!'
]);
$model->validate();

print_r($model->getErrors());

echo "\n----------------------------\n use Validation\n----------------------------\n\n";

$v = \Inhere\Validate\Validation::make($data, $rules)
        ->setTranslates([
            'goods.pear' => '梨子'
        ])
        ->setMessages([
            'freeTime.required' => 'freeTime is required!!!!'
        ])
       ->validate([], false);

print_r($v->getErrors());


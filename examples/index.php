<?php

spl_autoload_register(function($class)
{
    // e.g. "inhere\validate\ValidationTrait"
    if (strpos($class,'\\')) {
        $file = dirname(__DIR__) . '/src/' . trim(strrchr($class,'\\'),'\\'). '.php';
    } else {
        $file = __DIR__ . '/' . $class. '.php';
    }

    if (is_file($file)) {
        include $file;
    }
});

$data = [
    // 'userId' => 234,
    'userId' => 'is not an integer',
    'tagId' => '234535',
    // 'freeTime' => '1456767657', // filed not exists
    'note' => '',
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

$rules = [
    ['tagId,userId,freeTime', 'required', 'msg' => '{attr} is required!'],// set message
    ['tagId,userId,freeTime', 'number'],
    ['note', 'email', 'skipOnEmpty' => false], // set skipOnEmpty is false.
    ['insertTime', 'email', 'scene' => 'otherScene' ],// set scene. will is not validate it on default.
    ['tagId', 'size', 'max'=> 567, 'min'=> 4, ], // 4<= tagId <=567
    ['passwd', 'compare', 'repasswd'], //

    ['goods.pear', 'max', 30], //

    // ['notExistsField1', 'requiredWithout', 'notExistsField2'], //
    ['notExistsField1', 'requiredWithout', 'existsField'], //

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
$model->validate();

print_r($model->getErrors());

echo "\n----------------------------\n use Validation\n----------------------------\n\n";

$valid = \inhere\validate\Validation::make($data, $rules)
        ->setAttrTrans([
            'goods.pear' => '梨子'
        ])
       ->validate([], false);

print_r($valid->getErrors());


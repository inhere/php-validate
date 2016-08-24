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
    'userId' => 'sdfdffffffffff',
    'tagId' => '234535',
    // 'freeTime' => 'sdfdffffffffff',
    'distanceRequire' => 'sdfdffffffffff',
    'note' => '',
    'insertTime' => '1456767657',
    'lastReadTime' => '1456767657',
];

$rules = [
    ['tagId,userId,freeTime', 'required', 'msg' => '{attr} is required!'],// set message
    ['note', 'email', 'skipOnEmpty' => false], // set skipOnEmpty is false.
    ['insertTime', 'email', 'scene' => 'otherScene' ],// set scene. will is not validate it on default.
    ['tagId', 'size', 'min'=>4, 'max'=>567], // 4<= tagId <=567
    ['freeTime', 'size', 'min'=>4, 'max'=>567, 'when' => function($data, $valid) {
        echo "  use when pre-check\n";

        // $valid is current validation instance.

        return true;
    }], // 4<= tagId <=567

    ['userId', function($value, $data){
        echo "  use custom validate\n";

        var_dump($value, $data);

        echo __LINE__ . "\n";

        return false;
    }, 'msg' => 'userId check failure!'],
];

echo "use ValidationTrait\n";

//$model = new DataModel($_POST,$rules);
$model = new DataModel;
$model->setData($data)->setRules($rules);
$model->validate();

var_dump(
    $model->all(),
    $model->getErrors()
);

/*
echo "--------------\n";
echo "use Validation\n";

$valid = \inhere\validate\Validation::make($data, $rules)->validate();

var_dump(
    $valid->all(),
    $valid->getErrors()
);
*/
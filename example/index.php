<?php

spl_autoload_register(function($class)
{
    $file = __DIR__ . '/' . $class. '.php';

    if (is_file($file)) {
        include $file;
    }
});

$data = [
    'userId' => 'sdfdffffffffff',
    'tagId' => '234535',
    // 'freeTime' => 'sdfdffffffffff',
    'distanceRequire' => 'sdfdffffffffff',
    'note' => 'sdfdffffffffff',
    'insertTime' => '',
    'lastReadTime' => 'sdfdffffffffff',
];

$rules = [
    ['tagId,userId,freeTime', 'required', 'msg' => '{field} is required!'],
    ['note', 'email'],
    ['tagId', 'size', 'min'=>4, 'max'=>567, 'msg' => '{field} must is big!'], // 4<= tagId <=567
    ['freeTime', 'size', 'min'=>4, 'max'=>567, 'msg' => '{field} must is big!'], // 4<= tagId <=567
    ['userId', function($value){ echo $value."\n"; return false;}, 'msg' => '{field} check filare!'],
];

/*
$model = new TestModel();
$ret = $model->load($_POST)->validate();
*/
$model = new DataModel($_POST,$rules);
$ret = $model->validate([], true);

// echo "<pre>";
var_dump($ret,
$model->firstError()
);


// echo "</pre>";
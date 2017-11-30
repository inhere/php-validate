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
    'repasswd' => 'repassword',
    'insertTime' => '1456767657',
    'goods' => [
        'apple' => 34,
        'pear' => 50,
    ],
];

$rules = [
    ['userId', 'required|int'],
    ['tagId', 'size:0,50'],
];

echo "\n----------------------------\n use FieldValidation\n----------------------------\n\n";

$v = \Inhere\Validate\FieldValidation::make($data, $rules)
    ->setTranslates([
        'goods.pear' => '梨子'
    ])
    ->setMessages([
        'freeTime.required' => 'freeTime is required!!!!'
    ])
   ->validate([], false);

print_r($v->getErrors());

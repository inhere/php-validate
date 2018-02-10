<?php

require __DIR__ . '/simple-loader.php';

$data = [
    'name' => ' tom ',
    'status' => ' 23 ',
    'word' => 'word',
    'toLower' => 'WORD',
    'title' => 'helloWorld',
];

$rules = [
    ['name', 'string|trim'],
    ['status', 'trim|int'],
    ['word', 'string|trim|upper'],
    ['toLower', 'lower'],
    ['title', [
        'string',
        'snake' => ['-'],
        'ucfirst',
    ]],
];

echo "------------- raw data: -------------\n";
var_dump($data);

$cleaned = \Inhere\Validate\Filter\Filtration::make($data, $rules)->filtering();

echo "------------- cleaned data: -------------\n";
var_dump($cleaned);

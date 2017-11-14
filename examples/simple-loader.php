<?php

error_reporting(E_ALL);
date_default_timezone_set("Asia/Shanghai");

spl_autoload_register(function($class)
{
    // e.g. "Inhere\Validate\ValidationTrait"
    if (strpos($class,'\\')) {
        $file = dirname(__DIR__) . '/src/' . trim(strrchr($class,'\\'),'\\'). '.php';
    } else {
        $file = __DIR__ . '/' . $class. '.php';
    }

    if (is_file($file)) {
        include $file;
    }
});

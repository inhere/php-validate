<?php
/**
 * phpunit --bootstrap test/boot.php test
 */

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

$libDir = dirname(__DIR__);
$npMap = [
    'Inhere\\Validate\\' => $libDir . '/src/',
    'Inhere\\ValidateTest\\' => $libDir . '/test/',
];

spl_autoload_register(function ($class) use ($npMap) {
    foreach ($npMap as $np => $dir) {
        $file = $dir . str_replace('\\', '/', substr($class, strlen($np))) . '.php';

        if (file_exists($file)) {
            include $file;
        }
    }
});

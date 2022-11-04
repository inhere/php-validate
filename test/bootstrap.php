<?php declare(strict_types=1);
/**
 * phpunit
 * // output coverage without xdebug
 * phpdbg -dauto_globals_jit=Off -qrr /usr/local/bin/phpunit --coverage-text
 */

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

$libDir = dirname(__DIR__);
$npMap  = [
    'Inhere\\Validate\\'     => $libDir . '/src/',
    'Inhere\\ValidateTest\\' => $libDir . '/test/',
];

spl_autoload_register(static function ($class) use ($npMap) {
    foreach ($npMap as $np => $dir) {
        $file = $dir . str_replace('\\', '/', substr($class, strlen($np))) . '.php';

        if (file_exists($file)) {
            include $file;
        }
    }
});

if (is_file(dirname(__DIR__, 3) . '/autoload.php')) {
    require dirname(__DIR__, 3) . '/autoload.php';
} elseif (is_file(dirname(__DIR__) . '/vendor/autoload.php')) {
    require dirname(__DIR__) . '/vendor/autoload.php';
}
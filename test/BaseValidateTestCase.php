<?php declare(strict_types=1);

namespace Inhere\ValidateTest;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

/**
 * class BaseValidateTestCase
 */
abstract class BaseValidateTestCase extends TestCase
{
    /**
     * @param callable $cb
     *
     * @return Throwable
     */
    protected function runAndGetException(callable $cb): Throwable
    {
        try {
            $cb();
        } catch (Throwable $e) {
            return $e;
        }

        return new RuntimeException('NO ERROR');
    }
}

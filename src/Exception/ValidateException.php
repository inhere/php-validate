<?php declare(strict_types=1);

namespace Inhere\Validate\Exception;

use RuntimeException;

/**
 * Class ValidateException
 *
 * @package Inhere\Validate\Exception
 */
class ValidateException extends RuntimeException
{
    /**
     * @var string
     */
    public string $field;

    /**
     * @param string $field
     * @param string $message
     *
     * @return static
     */
    public static function create(string $field, string $message): self
    {
        return new self($field, $message);
    }

    /**
     * Class constructor.
     *
     * @param string $field
     * @param string $message
     * @param int    $code
     */
    public function __construct(string $field, string $message, int $code = 500)
    {
        parent::__construct("'$field' $message", $code);

        // save field
        $this->field = $field;
    }
}

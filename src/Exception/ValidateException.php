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
    public $field;

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
     */
    public function __construct(string $field, string $message)
    {
        parent::__construct($field . ' ' . $message, 500);

        // save field
        $this->field = $field;
    }
}

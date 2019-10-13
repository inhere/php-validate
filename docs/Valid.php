<?php

namespace Inhere\Validate;

/**
 * Class Valid - Simple Data Validator
 * @package Inhere\Validate
 */
class Valid
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param array $data
     *
     * @return Valid
     */
    public static function create(array $data): self
    {
        return new static($data);
    }

    /**
     * Validator constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getInt(string $field, $min = null, $max = null, $default = 0): int
    {
        return 0;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }
}

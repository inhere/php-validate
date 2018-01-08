<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/1/8 0008
 * Time: 21:47
 */

namespace Inhere\Validate\Utils;

/**
 * Class Rule
 * @package Inhere\Validate\Utils
 */
final class Rule
{
    /**
     * validator name OR validator object
     * @var string|callable
     */
    public $validator;

    /**
     * @var array
     */
    public $params = [];

    /**
     * @var \Closure
     */
    public $when;

    /**
     * @var mixed
     */
    public $default;

    /**
     * check Empty
     * @var callable
     */
    public $isEmpty;

    /**
     * @var bool
     */
    public $skipOnEmpty = true;

    /**
     * @var array|null
     */
    public $filters;
}

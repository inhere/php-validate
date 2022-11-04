<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/1/8 0008
 * Time: 21:47
 */

namespace Inhere\Validate\Validator;

use Closure;

/**
 * Class Rule
 *
 * @package Inhere\Validate\Validator
 * @TODO
 */
final class Rule
{
    /**
     * @var string
     */
    public string $field;

    /**
     * validator name OR validator object
     *
     * @var string|callable
     */
    public $validator;

    /**
     * @var array
     */
    public array $params = [];

    /**
     * @var Closure
     */
    public Closure $when;

    /**
     * @var mixed
     */
    public mixed $value;

    /**
     * @var mixed
     */
    public mixed $default;

    /**
     * default error message
     *
     * @var mixed
     */
    public mixed $message;

    /**
     * check Empty
     *
     * @var callable
     */
    public $isEmpty;

    /**
     * @var bool
     */
    public bool $skipOnEmpty = true;

    /**
     * @var array|null
     */
    public ?array $filters;

    public static function createByArray(array $config): void
    {
    }

    /**
     * @param string         $field     属性名称
     * @param mixed          $value     属性值
     * @param string|Closure $validator 验证器
     * @param array          $params    验证需要的参数
     * @param string $message   default error message
     * @param mixed          $default   default value
     *
     * @return Rule
     */
    public function init(string $field, mixed $value, string|Closure $validator, array $params, string $message, mixed $default): Rule
    {
        $this->field     = $field;
        $this->value     = $value;
        $this->validator = $validator;
        $this->params    = $params;
        $this->message   = $message;
        $this->default   = $default;

        return $this;
    }
}

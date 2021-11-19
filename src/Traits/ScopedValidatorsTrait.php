<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-03-17
 * Time: 11:26
 */

namespace Inhere\Validate\Traits;

use Inhere\Validate\Filter\Filters;
use Inhere\Validate\Helper;
use Inhere\Validate\Validators;
use InvalidArgumentException;
use function array_shift;
use function function_exists;
use function getimagesize;
use function in_array;
use function is_object;
use function is_string;
use function method_exists;
use function strpos;
use function strrchr;
use function strtolower;
use function trim;
use const UPLOAD_ERR_OK;

/**
 * trait ScopedValidatorsTrait - deps the current validation instance.
 * - user custom validators
 * - some validators of require context data.
 *
 * @package Inhere\Validate\Traits
 */
trait ScopedValidatorsTrait
{
    /** @var array user custom add's validators(current scope) */
    protected $_validators = [];

    /**
     * @see $_FILES
     * @var array[]
     */
    private $uploadedFiles = [];

    /*******************************************************************************
     * custom validators
     ******************************************************************************/

    /**
     * add a custom validator
     *
     * ```
     * $v = Validation::make($_POST)
     *     ->addValidator('my-validator', function($val [, $arg1, $arg2 ... ]){
     *           return $val === 23;
     *     });
     * $v->validate();
     * ```
     *
     * @param string   $name
     * @param callable $callback
     * @param string   $message
     *
     * @return $this
     */
    public function addValidator(string $name, callable $callback, string $message = ''): self
    {
        return $this->setValidator($name, $callback, $message);
    }

    /**
     * add a custom validator
     *
     * @param string   $name
     * @param callable $callback
     * @param string   $message
     *
     * @return self
     */
    public function setValidator(string $name, callable $callback, string $message = ''): self
    {
        if ($name = trim($name)) {
            $this->_validators[$name] = $callback;

            if ($message) {
                $this->setMessage($name, $message);
            }
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return null|callable
     */
    public function getValidator(string $name): ?callable
    {
        return $this->_validators[$name] ?? null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasValidator(string $name): bool
    {
        return isset($this->_validators[$name]);
    }

    /**
     * @param array $validators
     *
     * @return $this
     */
    public function addValidators(array $validators): self
    {
        foreach ($validators as $name => $validator) {
            $this->addValidator($name, $validator);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getValidators(): array
    {
        return $this->_validators;
    }

    /**
     * @param string $name
     */
    public function delValidator(string $name): void
    {
        if (isset($this->_validators[$name])) {
            unset($this->_validators[$name]);
        }
    }

    /**
     * clear validators
     */
    public function clearValidators(): void
    {
        $this->_validators = [];
    }

    /*******************************************************************************
     * fields(required*, file) validators
     ******************************************************************************/

    /**
     * 验证字段必须存在，且输入数据不为空。
     * The verification field must exist and the input data is not empty.
     *
     * @param string     $field
     * @param null|mixed $value
     *
     * @return bool
     * - True  field exists and value is not empty.
     * - False field not exists or value is empty.
     * @see Validators::isEmpty() 如何鉴定为空
     */
    public function required(string $field, $value = null): bool
    {
        if (null !== $value) {
            $val = $value;
        } elseif (null === ($val = $this->getByPath($field))) {
            // check uploaded files
            if (!isset($this->uploadedFiles[$field])) {
                return false;
            }

            $val = $this->uploadedFiles[$field];
        }

        return !Validators::isEmpty($val);
    }

    /**
     * 如果指定的另一个字段（ anotherField ）值等于任何一个 value 时，此字段为 必填 (refer laravel)
     *
     * @param string       $field
     * @param mixed        $fieldVal
     * @param string       $anotherField
     * @param array|string $values
     *
     * @return bool|null
     * - TRUE  check successful
     * - FALSE check failed
     * - NULL  skip check the field
     */
    public function requiredIf(string $field, $fieldVal, string $anotherField, $values): ?bool
    {
        if (isset($this->data[$anotherField])) {
            $anotherVal = $this->data[$anotherField];

            // if (in_array($anotherVal, (array)$values, true)) {
            if (Helper::inArray($anotherVal, (array)$values)) {
                return $this->required($field, $fieldVal);
            }
        }

        return null;
    }

    /**
     * 如果指定的另一个字段（ anotherField ）值等于任何一个 value 时，此字段为 不必填(refer laravel)
     *
     * @param string       $field
     * @param mixed        $fieldVal
     * @param string       $anotherField
     * @param array|string $values
     *
     * @return bool|null
     * @see requiredIf()
     */
    public function requiredUnless(string $field, $fieldVal, string $anotherField, $values): ?bool
    {
        if (isset($this->data[$anotherField])) {
            $anotherVal = $this->data[$anotherField];

            // if (in_array($anotherVal, (array)$values, true)) {
            if (Helper::inArray($anotherVal, (array)$values)) {
                return null;
            }
        }

        return $this->required($field, $fieldVal);
    }

    /**
     * 如果指定的其他字段中的 任意一个 有值且不为空，则此字段为 必填(refer laravel)
     *
     * @param string       $field
     * @param mixed        $fieldVal
     * @param array|string $fields
     *
     * @return bool|null
     * @see requiredIf()
     */
    public function requiredWith(string $field, $fieldVal, $fields): ?bool
    {
        foreach ((array)$fields as $name) {
            if ($this->required($name)) {
                return $this->required($field, $fieldVal);
            }
        }

        return null;
    }

    /**
     * 如果指定的 所有字段 都有值且不为空，则此字段为 必填(refer laravel)
     *
     * @param string       $field
     * @param mixed        $fieldVal
     * @param array|string $fields
     *
     * @return bool|null
     * @see requiredIf()
     */
    public function requiredWithAll(string $field, $fieldVal, $fields): ?bool
    {
        $allHasValue = true;

        foreach ((array)$fields as $name) {
            if (!$this->required($name)) {
                $allHasValue = false;
                break;
            }
        }

        return $allHasValue ? $this->required($field, $fieldVal) : null;
    }

    /**
     * 如果缺少 任意一个 指定的字段值，则此字段为 必填(refer laravel)
     *
     * @param string       $field
     * @param mixed        $fieldVal
     * @param array|string $fields
     *
     * @return bool|null
     * @see requiredIf()
     */
    public function requiredWithout(string $field, $fieldVal, $fields): ?bool
    {
        $allHasValue = true;

        foreach ((array)$fields as $name) {
            if (!$this->required($name)) {
                $allHasValue = false;
                break;
            }
        }

        return $allHasValue ? null : $this->required($field, $fieldVal);
    }

    /**
     * 如果所有指定的字段 都没有 值，则此字段为 必填(refer laravel)
     *
     * @param string       $field
     * @param mixed        $fieldVal
     * @param array|string $fields
     *
     * @return bool|null
     * @see requiredIf()
     */
    public function requiredWithoutAll(string $field, $fieldVal, $fields): ?bool
    {
        $allNoValue = true;

        foreach ((array)$fields as $name) {
            if ($this->required($name)) {
                $allNoValue = false;
                break;
            }
        }

        return $allNoValue ? $this->required($field, $fieldVal) : null;
    }

    /*******************************************************************************
     * Files validators(require context data)
     ******************************************************************************/

    /**
     * 验证的字段必须是成功上传的文件
     *
     * @param string       $field
     * @param string|array $suffixes e.g ['jpg', 'jpeg', 'png', 'gif', 'bmp']
     *
     * @return bool
     */
    public function fileValidator(string $field, $suffixes = null): bool
    {
        if (!$file = $this->uploadedFiles[$field] ?? null) {
            return false;
        }

        if (!isset($file['error']) || ($file['error'] !== UPLOAD_ERR_OK)) {
            return false;
        }

        if (!$suffixes) {
            return true;
        }

        if (!$suffix = trim((string)strrchr($file['name'], '.'), '.')) {
            return false;
        }

        $suffixes = is_string($suffixes) ? Filters::explode($suffixes) : (array)$suffixes;

        return in_array(strtolower($suffix), $suffixes, true);
    }

    /**
     * 验证的字段必须是成功上传的图片文件
     *
     * @param string       $field
     * @param string|array $suffixes e.g ['jpg', 'jpeg', 'png', 'gif', 'bmp']
     *
     * @return bool
     */
    public function imageValidator(string $field, $suffixes = null): bool
    {
        if (!$file = $this->uploadedFiles[$field] ?? null) {
            return false;
        }

        if (!isset($file['error']) || ($file['error'] !== UPLOAD_ERR_OK)) {
            return false;
        }

        if (!$tmpFile = $file['tmp_name'] ?? null) {
            return false;
        }

        // getimagesize() 判定某个文件是否为图片的有效手段, 常用在文件上传验证
        // Note: 本函数不需要 GD 图像库
        // list($width, $height, $type, $attr) = getimagesize("img/flag.jpg");
        $imgInfo = @getimagesize($tmpFile);

        if ($imgInfo && $imgInfo[2]) {
            $mime = strtolower($imgInfo['mime']); // 支持不标准扩展名

            // 是否是图片
            if (!in_array($mime, Helper::$imgMimeTypes, true)) {
                return false;
            }

            if (!$suffixes) {
                return true;
            }

            $suffix   = Helper::getImageExtByMime($mime);
            $suffixes = is_string($suffixes) ? Filters::explode($suffixes) : (array)$suffixes;

            return in_array($suffix, $suffixes, true);
        }

        return false;
    }

    /**
     * 验证的文件必须与给定 MIME 类型之一匹配
     * ['video', 'mimeTypes', 'video/avi,video/mpeg,video/quicktime']
     *
     * @param string       $field
     * @param string|array $types
     *
     * @return bool
     */
    public function mimeTypesValidator(string $field, $types): bool
    {
        if (!$file = $this->uploadedFiles[$field] ?? null) {
            return false;
        }

        if (!isset($file['error']) || ($file['error'] !== UPLOAD_ERR_OK)) {
            return false;
        }

        if (!$tmpFile = $file['tmp_name'] ?? null) {
            return false;
        }

        $mime  = Helper::getMimeType($tmpFile);
        $types = is_string($types) ? Filters::explode($types) : (array)$types;

        return in_array($mime, $types, true);
    }

    /**
     * 验证的文件必须具有与列出的其中一个扩展名相对应的 MIME 类型
     * ['photo', 'mimes', 'jpeg,bmp,png']
     *
     * @param string       $field
     * @param string|array $types
     * return bool
     *
     * @todo
     */
    public function mimesValidator(string $field, $types = null): void
    {
    }

    /*******************************************************************************
     * Field compare validators
     ******************************************************************************/

    /**
     * 字段值比较：当前字段值是否与给定的字段值相同
     *
     * @param mixed  $val
     * @param string $compareField
     *
     * @return bool
     */
    public function compareValidator($val, string $compareField): bool
    {
        return $compareField && ($val === $this->getByPath($compareField));
    }

    public function eqFieldValidator($val, string $compareField): bool
    {
        return $this->compareValidator($val, $compareField);
    }

    /**
     * 字段值比较：当前字段值是否与给定的字段值不相同
     *
     * @param mixed  $val
     * @param string $compareField
     *
     * @return bool
     */
    public function neqFieldValidator($val, string $compareField): bool
    {
        return $compareField && ($val !== $this->getByPath($compareField));
    }

    /**
     * 字段值比较：当前字段值 要小于 给定字段的值
     *
     * @param string|int $val
     * @param string     $compareField
     *
     * @return bool
     */
    public function ltFieldValidator($val, string $compareField): bool
    {
        $maxVal = $this->getByPath($compareField);

        if ($maxVal === null) {
            return false;
        }

        return Validators::lt($val, $maxVal);
    }

    /**
     * 字段值比较：当前字段值 要小于等于 给定字段的值
     *
     * @param string|int $val
     * @param string     $compareField
     *
     * @return bool
     */
    public function lteFieldValidator($val, string $compareField): bool
    {
        $maxVal = $this->getByPath($compareField);

        if ($maxVal === null) {
            return false;
        }

        return Validators::lte($val, $maxVal);
    }

    /**
     * 字段值比较：当前字段值 要大于 给定字段的值
     *
     * @param string|int $val
     * @param string     $compareField
     *
     * @return bool
     */
    public function gtFieldValidator($val, string $compareField): bool
    {
        $minVal = $this->getByPath($compareField);

        if ($minVal === null) {
            return false;
        }

        return Validators::gt($val, $minVal);
    }

    /**
     * 字段值比较：当前字段值 要大于等于 给定字段的值
     *
     * @param string|int $val
     * @param string     $compareField
     *
     * @return bool
     */
    public function gteFieldValidator($val, string $compareField): bool
    {
        $minVal = $this->getByPath($compareField);

        if ($minVal === null) {
            return false;
        }

        return Validators::gte($val, $minVal);
    }

    /**
     * 验证的 字段值 必须存在于另一个字段（anotherField）的值中。
     *
     * @param mixed  $val
     * @param string $anotherField
     *
     * @return bool
     */
    public function inFieldValidator($val, string $anotherField): bool
    {
        if ($anotherField && $dict = $this->getByPath($anotherField)) {
            return Validators::in($val, $dict);
        }

        return false;
    }

    /**
     * 比较两个日期字段的 间隔天数 是否符合要求
     *
     * @param string $val
     * @param string $compareField
     * @param int    $expected
     * @param string $op
     *
     * @todo
     */
    public function intervalDayValidator($val, string $compareField, int $expected, string $op = '>='): void
    {
    }

    /**
     * 对数组中的每个值都应用给定的验证器，并且要全部通过
     * `['foo.*.id', 'each', 'number']`
     * `['goods.*', 'each', 'string']`
     *
     * @param array $values
     * @param array ...$args
     *  - string|\Closure $validator
     *  - ... args for $validator
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function eachValidator(array $values, ...$args): bool
    {
        if (!$validator = array_shift($args)) {
            throw new InvalidArgumentException('must setting a validator for \'each\' validate.');
        }

        foreach ($values as $value) {
            $passed = false;

            if (is_object($validator) && method_exists($validator, '__invoke')) {
                $passed = $validator($value, ...$args);
            } elseif (is_string($validator)) {
                // special for required
                if ('required' === $validator) {
                    $passed = !Validators::isEmpty($value);
                } elseif (isset($this->_validators[$validator])) {
                    $callback = $this->_validators[$validator];
                    $passed   = $callback($value, ...$args);
                } elseif (method_exists($this, $method = $validator . 'Validator')) {
                    $passed = $this->$method($value, ...$args);
                } elseif (method_exists(Validators::class, $validator)) {
                    $passed = Validators::$validator($value, ...$args);

                // it is function name
                } elseif (function_exists($validator)) {
                    $passed = $validator($value, ...$args);
                } else {
                    throw new InvalidArgumentException("The validator [$validator] don't exists!");
                }
            }

            if (!$passed) {
                return false;
            }
        }

        return true;
    }

    /*******************************************************************************
     * getter/setter/helper
     ******************************************************************************/

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function isCheckFile(string $name): bool
    {
        return false !== strpos(Helper::$fileValidators, '|' . $name . '|');
    }

    /**
     * @param string $name
     * @param array $args
     * @return bool
     */
    public static function isCheckRequired(string $name, array $args = []): bool
    {
        // the $arg.0 is validator name.
        $name = $name === 'each' ? ((string)($args[0] ?? '')) : $name;
        
        return 0 === strpos($name, 'required');
    }

    /**
     * @param string $field
     *
     * @return array|null
     */
    public function getUploadedFile(string $field): ?array
    {
        return $this->uploadedFiles[$field] ?? null;
    }

    /**
     * @return array
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * @param array $uploadedFiles
     *
     * @return $this
     */
    public function setUploadedFiles($uploadedFiles): self
    {
        $this->uploadedFiles = (array)$uploadedFiles;
        return $this;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-03-17
 * Time: 11:26
 */

namespace Inhere\Validate\Utils;

use Inhere\Validate\Validators;

/**
 * trait UserAndContextValidatorsTrait
 * - user custom validators
 * - some validators of require context data.
 * @package Inhere\Validate\Utils
 */
trait UserAndContextValidatorsTrait
{
    /**
     * custom add's validator by addValidator()
     * @var array
     */
    protected static $_validators = [];

    /** @var string */
    protected static $_fileValidators = '|file|image|mimeTypes|mimes|';

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
     * ```
     * $vd = ValidatorClass::make($_POST)
     *     ->addValidator('name',function($val [, $arg1, $arg2 ... ]){
     *           return $val === 23;
     *     });
     * $vd->validate();
     * ```
     * @param string $name
     * @param callable $callback
     * @param string $msg
     * @return $this
     */
    public function addValidator(string $name, callable $callback, string $msg = '')
    {
        self::setValidator($name, $callback, $msg);

        return $this;
    }

    /**
     * add a custom validator
     * @param string $name
     * @param callable $callback
     * @param string $msg
     */
    public static function setValidator(string $name, callable $callback, string $msg = null)
    {
        self::$_validators[$name] = $callback;

        if ($msg) {
            self::setDefaultMessage($name, $msg);
        }
    }

    /**
     * @param string $name
     * @return null|callable
     */
    public static function getValidator(string $name)
    {
        if (isset(self::$_validators[$name])) {
            return self::$_validators[$name];
        }

        return null;
    }

    /**
     * @param string $name
     * @return bool|callable
     */
    public static function delValidator(string $name)
    {
        $cb = false;

        if (isset(self::$_validators[$name])) {
            $cb = self::$_validators[$name];
            unset(self::$_validators[$name]);
        }

        return $cb;
    }

    /**
     * clear Filters
     */
    public static function clearValidators()
    {
        self::$_validators = [];
    }


    /**
     * @param array $validators
     */
    public static function setValidators(array $validators)
    {
        self::$_validators = array_merge(self::$_validators, $validators);
    }

    /**
     * @return array
     */
    public static function getValidators(): array
    {
        return self::$_validators;
    }

    /*******************************************************************************
     * fields(required*, file) validators
     ******************************************************************************/

    /**
     * 验证字段必须存在，且输入数据不为空。
     * @see Validators::isEmpty() 如何鉴定为空
     * @param string $field
     * @param null|mixed $value
     * @return bool
     */
    public function required(string $field, $value = null)
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
     * 如果指定的其它字段（ anotherField ）值等于任何一个 value 时，此字段为 必填
     * @from laravel
     * @param string $field
     * @param mixed $fieldVal
     * @param string $anotherField
     * @param array|string $values
     * @return bool
     */
    public function requiredIf(string $field, $fieldVal, $anotherField, $values)
    {
        if (!isset($this->data[$anotherField])) {
            return false;
        }

        $val = $this->data[$anotherField];

        if (\in_array($val, (array)$values, true)) {
            return $this->required($field, $fieldVal);
        }

        return false;
    }

    /**
     * 如果指定的其它字段（ anotherField ）值等于任何一个 value 时，此字段为 不必填
     * @from laravel
     * @param string $field
     * @param mixed $fieldVal
     * @param string $anotherField
     * @param array|string $values
     * @return bool
     */
    public function requiredUnless(string $field, $fieldVal, $anotherField, $values)
    {
        if (!isset($this->data[$anotherField])) {
            return false;
        }

        if (\in_array($this->data[$anotherField], (array)$values, true)) {
            return true;
        }

        return $this->required($field, $fieldVal);
    }

    /**
     * 如果指定的其他字段中的 任意一个 有值且不为空，则此字段为 必填
     * @from laravel
     * @param string $field
     * @param mixed $fieldVal
     * @param array|string $fields
     * @return bool
     */
    public function requiredWith(string $field, $fieldVal, $fields)
    {
        foreach ((array)$fields as $name) {
            if ($this->required($name)) {
                return $this->required($field, $fieldVal);
            }
        }

        return true;
    }

    /**
     * 如果指定的 所有字段 都有值，则此字段为必填。
     * @from laravel
     * @param string $field
     * @param mixed $fieldVal
     * @param array|string $fields
     * @return bool
     */
    public function requiredWithAll(string $field, $fieldVal, $fields)
    {
        $allHasValue = true;

        foreach ((array)$fields as $name) {
            if (!$this->required($name)) {
                $allHasValue = false;
                break;
            }
        }

        return $allHasValue ? $this->required($field, $fieldVal) : true;
    }

    /**
     * 如果缺少 任意一个 指定的字段值，则此字段为必填。
     * @from laravel
     * @param string $field
     * @param mixed $fieldVal
     * @param array|string $fields
     * @return bool
     */
    public function requiredWithout(string $field, $fieldVal, $fields)
    {
        $allHasValue = true;

        foreach ((array)$fields as $name) {
            if (!$this->required($name)) {
                $allHasValue = false;
                break;
            }
        }

        return $allHasValue ? true : $this->required($field, $fieldVal);
    }

    /**
     * 如果所有指定的字段 都没有 值，则此字段为必填。
     * @from laravel
     * @param string $field
     * @param mixed $fieldVal
     * @param array|string $fields
     * @return bool
     */
    public function requiredWithoutAll(string $field, $fieldVal, $fields)
    {
        $allNoValue = true;

        foreach ((array)$fields as $name) {
            if ($this->required($name)) {
                $allNoValue = false;
                break;
            }
        }

        return $allNoValue ? $this->required($field, $fieldVal) : true;
    }

    /**
     * 验证的字段必须是成功上传的文件
     * @param string $field
     * @param string|array $suffixes e.g ['jpg', 'jpeg', 'png', 'gif', 'bmp']
     * @return bool
     */
    public function fileValidator(string $field, $suffixes = null)
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

        $suffix = trim(strrchr($file['name'], '.'), '.');

        if (!$suffix) {
            return false;
        }

        $suffix = strtolower($suffix);
        $suffixes = \is_string($suffixes) ? Helper::explode($suffixes) : (array)$suffixes;

        return \in_array($suffix, $suffixes, true);
    }

    /**
     * 验证的字段必须是成功上传的图片文件
     * @param string $field
     * @param string|array $suffixes e.g ['jpg', 'jpeg', 'png', 'gif', 'bmp']
     * @return bool
     */
    public function imageValidator(string $field, $suffixes = null)
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
            if (!\in_array($mime, Helper::$imgMimeTypes, true)) {
                return false;
            }

            if (!$suffixes) {
                return true;
            }

            $suffix = Helper::getImageExtByMime($mime);
            $suffixes = \is_string($suffixes) ? Helper::explode($suffixes) : (array)$suffixes;

            return \in_array($suffix, $suffixes, true);
        }

        return false;
    }

    /**
     * 验证的文件必须与给定 MIME 类型之一匹配
     * ['video', 'mimeTypes', 'video/avi,video/mpeg,video/quicktime']
     * @param string $field
     * @param string|array $types
     * @return bool
     */
    public function mimeTypesValidator(string $field, $types)
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

        $mime = Helper::getMimeType($tmpFile);
        $types = \is_string($types) ? Helper::explode($types) : (array)$types;

        return \in_array($mime, $types, true);
    }

    /**
     * 验证的文件必须具有与列出的其中一个扩展名相对应的 MIME 类型
     * ['photo', 'mimes', 'jpeg,bmp,png']
     * @todo
     * @param string $field
     * @param string|array $types
     * return bool
     */
    public function mimesValidator(string $field, $types = null)
    {
    }

    /*******************************************************************************
     * Special validators(require context data)
     ******************************************************************************/

    /**
     * 字段值比较：当前字段值是否与给定的字段值相同
     * @param mixed $val
     * @param string $compareField
     * @return bool
     */
    public function compareValidator($val, string $compareField)
    {
        return $compareField && ($val === $this->getByPath($compareField));
    }

    public function sameValidator($val, string $compareField)
    {
        return $this->compareValidator($val, $compareField);
    }

    public function equalValidator($val, string $compareField)
    {
        return $this->compareValidator($val, $compareField);
    }

    /**
     * 字段值比较：当前字段值是否与给定的字段值不相同
     * @param mixed $val
     * @param string $compareField
     * @return bool
     */
    public function notEqualValidator($val, string $compareField)
    {
        return $compareField && ($val !== $this->getByPath($compareField));
    }

    /**
     * alias of the 'notEqualValidator'
     * @param mixed $val
     * @param string $compareField
     * @return bool
     */
    public function differentValidator($val, string $compareField)
    {
        return $compareField && ($val !== $this->getByPath($compareField));
    }

    /**
     * 验证的 字段值 必须存在于另一个字段（anotherField）的值中。
     * @param mixed $val
     * @param string $anotherField
     * @return bool
     */
    public function inFieldValidator($val, string $anotherField)
    {
        if ($anotherField && $dict = $this->getByPath($anotherField)) {
            return Validators::in($val, $dict);
        }

        return false;
    }

    /**
     * 对数组中的每个值都应用给定的验证器，并且要全部通过
     * `['foo.*.id', 'each', 'number']`
     * `['goods.*', 'each', 'string']`
     * @param array $values
     * @param array ...$args
     *  - string|\Closure $validator
     *  - ... args for $validator
     * @return bool
     */
    public function eachValidator(array $values, ...$args)
    {
        if (!$validator = array_shift($args)) {
            throw new \InvalidArgumentException('must setting a validator for \'each\' validate.');
        }

        foreach ($values as $value) {
            $passed = false;

            if (\is_object($validator) && method_exists($validator, '__invoke')) {
                $passed = $validator($value, ...$args);
            } elseif (\is_string($validator)) {
                // special for required
                if ('required' === $validator) {
                    $passed = !Validators::isEmpty($value);

                } elseif (isset(self::$_validators[$validator])) {
                    $callback = self::$_validators[$validator];
                    $passed = $callback($value, ...$args);

                } elseif (method_exists($this, $method = $validator . 'Validator')) {
                    $passed = $this->$method($value, ...$args);

                } elseif (method_exists(Validators::class, $validator)) {
                    $passed = Validators::$validator($value, ...$args);

                    // it is function name
                } elseif (\function_exists($validator)) {
                    $passed = $validator($value, ...$args);
                } else {
                    throw new \InvalidArgumentException("The validator [$validator] don't exists!");
                }
            }

            if (!$passed) {
                return false;
            }
        }

        return true;
    }

    /**
     * 比较两个日期字段的 间隔天数 是否符合要求
     * @todo
     * @param string $val
     * @param string $compareField
     * @param int $expected
     * @param string $op
     */
    public function intervalDayValidator($val, string $compareField, int $expected, string $op = '>=')
    {

    }

    /*******************************************************************************
     * getter/setter/helper
     ******************************************************************************/

    /**
     * @param string $name
     * @return bool
     */
    public static function isCheckFile(string $name)
    {
        return false !== strpos(self::$_fileValidators, '|' . $name . '|');
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function isCheckRequired(string $name)
    {
        return 0 === strpos($name, 'required');
    }

    /**
     * @param string $field
     * @return array|null
     */
    public function getUploadedFile($field)
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
     * @return $this
     */
    public function setUploadedFiles($uploadedFiles)
    {
        $this->uploadedFiles = (array)$uploadedFiles;

        return $this;
    }

}

<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-03-17
 * Time: 11:26
 */

namespace Inhere\Validate\Utils;

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

    /**
     * @see $_FILES
     * @var array
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
    public static function getValidator($name)
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
    public static function delValidator($name)
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
     * 验证字段必须存在输入数据，且不为空。字段符合下方任一条件时即为「空」
     * - 该值为 null.
     * - 该值为空字符串。
     * - 该值为空数组
     * @param  string $field
     * @return bool
     */
    public function required($field)
    {
        if (!isset($this->data[$field])) {
            return false;
        }

        $val = $this->data[$field];

        return $val !== '' && $val !== null && $val !== false && $val !== [];
    }

    /**
     * 如果指定的其它字段（ anotherField ）值等于任何一个 value 时，此字段为 必填
     * @from laravel
     * @param  string $field
     * @param  string $anotherField
     * @param  array|string $values
     * @return bool
     */
    public function requiredIf($field, $anotherField, $values)
    {
        if (!isset($this->data[$anotherField])) {
            return false;
        }

        $val = $this->data[$anotherField];

        if (\in_array($val, (array)$values, true)) {
            return $this->required($field);
        }

        return false;
    }

    /**
     * 如果指定的其它字段（ anotherField ）值等于任何一个 value 时，此字段为 不必填
     * @from laravel
     * @param  string $field
     * @param  string $anotherField
     * @param  array|string $values
     * @return bool
     */
    public function requiredUnless($field, $anotherField, $values)
    {
        if (!isset($this->data[$anotherField])) {
            return false;
        }

        if (\in_array($this->data[$anotherField], (array)$values, true)) {
            return true;
        }

        return $this->required($field);
    }

    /**
     * 如果指定的字段中的 任意一个 有值且不为空，则此字段为必填
     * @from laravel
     * @param  string $field
     * @param  array|string $fields
     * @return bool
     */
    public function requiredWith($field, $fields)
    {
        foreach ((array)$fields as $name) {
            if ($this->required($name)) {
                return $this->required($field);
            }
        }

        return true;
    }

    /**
     * 如果指定的 所有字段 都有值，则此字段为必填。
     * @from laravel
     * @param  string $field
     * @param  array|string $fields
     * @return bool
     */
    public function requiredWithAll($field, $fields)
    {
        $allHasValue = true;

        foreach ((array)$fields as $name) {
            if (!$this->required($name)) {
                $allHasValue = false;
                break;
            }
        }

        return $allHasValue ? $this->required($field) : true;
    }

    /**
     * 如果缺少 任意一个 指定的字段值，则此字段为必填。
     * @from laravel
     * @param  string $field
     * @param  array|string $fields
     * @return bool
     */
    public function requiredWithout($field, $fields)
    {
        $allHasValue = true;

        foreach ((array)$fields as $name) {
            if (!$this->required($name)) {
                $allHasValue = false;
                break;
            }
        }

        return $allHasValue ? true : $this->required($field);
    }

    /**
     * 如果所有指定的字段 都没有 值，则此字段为必填。
     * @from laravel
     * @param  string $field
     * @param  array|string $fields
     * @return bool
     */
    public function requiredWithoutAll($field, $fields)
    {
        $allNoValue = true;

        foreach ((array)$fields as $name) {
            if ($this->required($name)) {
                $allNoValue = false;
                break;
            }
        }

        return $allNoValue ? $this->required($field) : true;
    }

    /**
     * 验证的字段必须是成功上传的文件
     * @param string $field
     * @param string|array $suffixes e.g ['jpg', 'jpeg', 'png', 'gif', 'bmp']
     * @return bool
     */
    public function file($field, $suffixes = null)
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
    public function image($field, $suffixes = null)
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
            if (!\in_array($mime, Helper::IMG_MIME_TYPES, true)) {
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
    public function mimeTypes($field, $types)
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
     * @param string $field
     * @param string|array $types
     * return bool
     */
    public function mimes($field, $types = null)
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
    public function compare($val, $compareField)
    {
        return $compareField && ($val === $this->get($compareField));
    }

    public function same($val, $compareField)
    {
        return $this->compare($val, $compareField);
    }

    public function equal($val, $compareField)
    {
        return $this->compare($val, $compareField);
    }

    /**
     * 字段值比较：当前字段值是否与给定的字段值不相同
     * @param mixed $val
     * @param string $compareField
     * @return bool
     */
    public function notEqual($val, $compareField)
    {
        return $compareField && ($val !== $this->get($compareField));
    }

    /*******************************************************************************
     * getter/setter
     ******************************************************************************/

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

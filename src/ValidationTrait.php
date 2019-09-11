<?php
/**
 * Created by sublime 3.
 * Auth: Inhere
 * Date: 14-9-28
 * Time: 10:35
 */

namespace Inhere\Validate;

use Closure;
use Generator;
use Inhere\Validate\Filter\FilteringTrait;
use Inhere\Validate\Filter\Filters;
use Inhere\Validate\Traits\ErrorMessageTrait;
use Inhere\Validate\Traits\ScopedValidatorsTrait;
use Inhere\Validate\Validator\UserValidators;
use InvalidArgumentException;
use stdClass;
use function array_keys;
use function array_merge;
use function array_shift;
use function array_values;
use function explode;
use function function_exists;
use function in_array;
use function is_array;
use function is_object;
use function is_string;
use function method_exists;
use function property_exists;
use function strpos;
use function substr;
use function trim;
use const PHP_INT_MIN;

/**
 * Trait ValidationTrait
 * @package Inhere\Validate
 * property array $data To verify the data list. please define it on main class. 待验证的数据列表
 */
trait ValidationTrait
{
    use FilteringTrait, ErrorMessageTrait, ScopedValidatorsTrait;

    /** @var array The rules is by setRules() */
    private $_rules = [];

    /** @var array Through the validation of the data */
    private $_safeData = [];

    /** @var bool Mark validate has running */
    private $_validated = false;

    /** @var Closure before validate handler */
    private $_beforeHandler;

    /** @var Closure after validate handler */
    private $_afterHandler;

    /** @var array Used rules at current scene */
    protected $_usedRules = [];

    /**
     * Whether to skip when empty(When not required)
     * default is TRUE, need you manual add 'required'.
     * @var bool
     */
    private $_skipOnEmpty = true;

    /**
     * Setting current scenario name
     * 当前验证的场景 -- 如果需要让规则列表在多个类似情形下使用
     * (
     * e.g: 在MVC框架中，
     * - 通常可以根据控制器的 action name(add, edit, register) 来区分。
     * - 或者根据模型的场景(create, update, delete) 来区分。
     * )
     * @var string
     */
    protected $scene = '';

    /**
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * define attribute field translate list
     * @return array
     */
    public function translates(): array
    {
        return [
            // 'field' => 'translate',
            // e.g. 'name'=>'名称',
        ];
    }

    /**
     * 自定义验证器的默认错误消息格式
     * custom validator's message, to override default message.
     * @return array
     */
    public function messages(): array
    {
        return [
            // validator name => message string
            // 'required' => '{attr} 是必填项。',
            // 'username.required' => '用户名 是必填项。',
        ];
    }

    /**
     * The field that the current scene needs to collect
     * @return array
     */
    public function scenarios(): array
    {
        return [
            // scene name => needed fields ...
            // 'scene' => ['filed1', 'field2'],
            // 'create' => ['filed1', 'field2'],
        ];
    }

    /**
     * before validate handler
     *
     * @param Closure $cb
     *
     * @return static
     */
    public function onBeforeValidate(Closure $cb)
    {
        $this->_beforeHandler = $cb;
        return $this;
    }

    public function beforeValidate(): bool
    {
        $ok = true;

        if ($cb = $this->_beforeHandler) {
            $ok = $cb($this);
        }

        // do something ...
        return (bool)$ok;
    }

    /**
     * after validate handler
     *
     * @param Closure $cb
     *
     * @return static
     */
    public function onAfterValidate(Closure $cb)
    {
        $this->_afterHandler = $cb;
        return $this;
    }

    public function afterValidate(): void
    {
        if ($cb = $this->_afterHandler) {
            $cb($this);
        }
        // do something ...
    }

    /*******************************************************************************
     * Validate
     ******************************************************************************/

    /**
     * Perform data validation
     *
     * @param array     $onlyChecked You can set this field that needs verification
     * @param bool|null $stopOnError Stop verification if there is an error
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function validate(array $onlyChecked = [], bool $stopOnError = null)
    {
        if (!property_exists($this, 'data')) {
            throw new InvalidArgumentException('Must be defined property "data"(array) in the sub-class used.');
        }

        if ($this->_validated) {
            return $this;
        }

        $this->resetValidation();
        $this->setStopOnError($stopOnError);
        $this->prepareValidation();

        if (!$this->beforeValidate()) {
            return $this;
        }

        if (!$onlyChecked) {
            $onlyChecked = $this->getSceneFields();
        }

        foreach ($this->collectRules() as $fields => $rule) {
            $this->applyRule($fields, $rule, $onlyChecked);

            // There is an error an immediate end to verify
            if ($this->isStopOnError() && $this->isFail()) {
                break;
            }
        }

        // fix: has error, clear safe data.
        if ($this->isFail()) {
            $this->_safeData = [];
        }

        $this->afterValidate();

        // fix : deny repeat validate
        $this->_validated = true;
        return $this;
    }

    /**
     * apply validate rule for given fields
     *
     * @param string|array $fields
     * @param array        $rule
     * @param array        $onlyChecked
     */
    protected function applyRule($fields, array $rule, array $onlyChecked): void
    {
        $fields    = is_string($fields) ? Filters::explode($fields) : (array)$fields;
        $validator = array_shift($rule);

        // How to determine the property is empty(default use the Validators::isEmpty)
        $isEmpty = [Validators::class, 'isEmpty'];
        if (!empty($rule['isEmpty']) && (is_string($rule['isEmpty']) || $rule['isEmpty'] instanceof Closure)) {
            $isEmpty = $rule['isEmpty'];
        }

        // Preconditions for verification -- If do not meet the conditions, skip this rule
        $when = $rule['when'] ?? null;
        if ($when && ($when instanceof Closure) && $when($this->data, $this) !== true) {
            return;
        }

        // Whether to skip when empty(When not required). ref yii2
        $skipOnEmpty = $rule['skipOnEmpty'] ?? $this->_skipOnEmpty;
        $filters     = $rule['filter'] ?? null;  // filter
        $defMsg      = $rule['msg'] ?? null; // Custom error message
        $defValue    = $rule['default'] ?? null;// Allow default

        // clear all keywords options. 0 is the validator
        unset($rule['msg'], $rule['default'], $rule['skipOnEmpty'], $rule['isEmpty'], $rule['when'], $rule['filter']);

        // The rest are validator parameters. Some validators require parameters. e.g. size()
        $args = $rule;

        foreach ($fields as $field) {
            if (!$field || ($onlyChecked && !in_array($field, $onlyChecked, true))) {
                continue;
            }

            $value = $this->getByPath($field, $defValue);

            // Field value filtering(有通配符`*`的字段, 不应用过滤器)
            if ($filters && null !== $value && !strpos($field, '.*')) {
                $value = $this->valueFiltering($value, $filters);
                // save
                $this->data[$field] = $value;
            }

            // Field name validate
            if (is_string($validator)) {
                if ($validator === 'safe') {
                    $this->setSafe($field, $value);
                    continue;
                }

                // required*系列字段检查 || 文件资源检查
                if (self::isCheckRequired($validator) || self::isCheckFile($validator)) {
                    $result = $this->fieldValidate($field, $value, $validator, $args, $defMsg);

                    if (false === $result && $this->isStopOnError()) {
                        break;
                    }
                    continue;
                }
            }

            // skip On Empty && The value is empty
            if ($skipOnEmpty && Helper::call($isEmpty, $value)) {
                continue;
            }

            // Field value verification check
            if (!$this->valueValidate($field, $value, $validator, $args, $defMsg) && $this->isStopOnError()) {
                break;
            }
        }
    }

    /**
     * field require/exists validate 字段存在检查
     *
     * @param string       $field Attribute name
     * @param mixed        $value Attribute value
     * @param string       $validator required* Validator name
     * @param array        $args Verify the required parameters
     * @param string|array $defMsg
     *
     * @return bool|null
     * - TRUE  check successful
     * - FALSE check failed
     * - NULL  skip check(for `required*`)
     * @throws InvalidArgumentException
     */
    protected function fieldValidate(string $field, $value, string $validator, array $args, $defMsg): ?bool
    {
        // required check
        if ($validator === 'required') {
            $passed = $this->required($field, $value);

            // File resource check
        } elseif (self::isCheckFile($validator)) {
            $passed = $this->$validator($field, ...array_values($args));

            // other required* methods
        } elseif (method_exists($this, $validator)) {
            $passed = $this->$validator($field, $value, ...array_values($args));
        } else {
            throw new InvalidArgumentException("The validator [$validator] is not exists!");
        }

        // validate success, save value to safeData
        if ($passed) {
            $this->collectSafeValue($field, $value);
            return true;
        }

        if ($passed === false) {
            $this->addError($field, $this->getMessage($validator, $field, $args, $defMsg));
        }

        return $passed;
    }

    /**
     * field value validate 字段值验证
     *
     * @param string               $field Field name
     * @param mixed                $value Field value
     * @param Closure|string|mixed $validator Validator
     * @param array                $args Arguments for validate
     * @param string               $defMsg
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function valueValidate(string $field, $value, $validator, array $args, $defMsg): bool
    {
        // if field don't exists.
        if (null === $value) {
            $this->addError($field, $this->getMessage($validator, $field, $args, $defMsg));
            return false;
        }

        $rawArgs = $args;
        $args    = array_values($args);

        // if $validator is a closure OR a object has method '__invoke'
        if (is_object($validator) && method_exists($validator, '__invoke')) {
            $args[] = $this->data;
            $passed = $validator($value, ...$args);
        } elseif (is_string($validator)) {
            $realName = Validators::realName($validator);
            // is global user validator
            if ($callback = $this->getValidator($validator)) {
                $args[] = $this->data;
                $passed = $callback($value, ...$args);
                // if $validator is a custom method of the subclass.
            } elseif (method_exists($this, $method = $validator . 'Validator')) {
                $passed = $this->$method($value, ...$args);
                // if $validator is a global custom validator {@see UserValidators::$validators}.
            } elseif ($callback = UserValidators::get($validator)) {
                $args[] = $this->data;
                $passed = $callback($value, ...$args);
            } elseif (method_exists(Validators::class, $realName)) {
                $passed = Validators::$realName($value, ...$args);
            } elseif (function_exists($validator)) { // it is function name
                $passed = $validator($value, ...$args);
            } else {
                throw new InvalidArgumentException("The validator [$validator] don't exists!");
            }
        } else {
            $passed = Helper::call($validator, $value, ...$args);
        }

        // validate success, save value to safeData
        if ($passed) {
            $this->collectSafeValue($field, $value);

            unset($rawArgs);
            return true;
        }

        $this->addError($field, $this->getMessage($validator, $field, $rawArgs, $defMsg));
        return false;
    }

    /**
     * collect Safe Value
     *
     * @param string $field
     * @param mixed  $value
     */
    protected function collectSafeValue(string $field, $value): void
    {
        // 进行的是子级属性检查 eg: 'goods.apple'
        if ($pos = strpos($field, '.')) {
            $field = (string)substr($field, 0, $pos);
            $value = $this->getRaw($field, []);
        }

        // set
        $this->_safeData[$field] = $value;
    }

    /**
     * @param bool|false $clearErrors
     */
    public function resetValidation(bool $clearErrors = true): void
    {
        $this->_validated = false;
        $this->_safeData  = $this->_usedRules = [];

        if ($clearErrors) {
            $this->clearErrors();
        }
    }

    /**
     * 收集当前场景可用的规则列表
     * Collect the current scenario of the available rules list
     * @throws InvalidArgumentException
     */
    protected function collectRules(): ?Generator
    {
        $scene = $this->scene;

        foreach ($this->getRules() as $rule) {
            // check fields
            if (!isset($rule[0]) || !$rule[0]) {
                throw new InvalidArgumentException('Please setting the fields(string|array) to wait validate! position: rule[0]');
            }

            // check validator
            if (!isset($rule[1]) || !$rule[1]) {
                throw new InvalidArgumentException('The rule validator is must be setting! position: rule[1]');
            }

            // only use to special scene.
            if (!empty($rule['on'])) {
                if (!$scene) {
                    continue;
                }

                $sceneList = is_string($rule['on']) ? Filters::explode($rule['on']) : (array)$rule['on'];

                if (!in_array($scene, $sceneList, true)) {
                    continue;
                }

                unset($rule['on']);
            }

            $this->_usedRules[] = $rule;
            // fields
            $fields = array_shift($rule);
            $this->prepareRule($rule);

            yield $fields => $rule;
        }
        //
    }

    /**
     * @param array $rule
     */
    protected function prepareRule(array &$rule): void
    {
        $validator = $rule[0];

        if (!is_string($validator)) {
            return;
        }

        switch ($validator) {
            case 'num':
            case 'number':
            case 'string':
            case 'length':
                // fixed: 当只有 max 时，自动补充一个 min. 字符串最小长度就是 0
                if (isset($rule['max']) && !isset($rule['min'])) {
                    $rule['min'] = 0;
                }
                break;
            case 'int':
            case 'size':
            case 'range':
            case 'integer':
            case 'between':
                // fixed: 当只有 max 时，自动补充一个 min
                if (isset($rule['max']) && !isset($rule['min'])) {
                    $rule['min'] = PHP_INT_MIN;
                }
                break;
        }
    }

    /*******************************************************************************
     * getter/setter/helper
     ******************************************************************************/

    /**
     * get fields for the scene.
     * @return array
     */
    public function getSceneFields(): array
    {
        if ($this->scene && $conf = $this->scenarios()) {
            return $conf[$this->scene] ?? [];
        }

        return [];
    }

    /**
     * @param string     $path 'users.*.id' 'goods.*' 'foo.bar.*.id'
     * @param null|mixed $default
     * @param array      $data
     * @param bool       $startWithWildcard
     *
     * @return mixed
     */
    protected function getByWildcard(string $path, $default = null, array $data = null, bool $startWithWildcard = false)
    {
        $data = $data ?: $this->data;
        if ($startWithWildcard) {
            $recently = $data;
            // '*.x'
            $last = substr($path, 2);
        } else {
            [$first, $last] = explode('.*', $path, 2);
            $recently = Helper::getValueOfArray($data, $first, $default);

            // 'goods.*'
            if ('' === $last) {
                return $recently;
            }

            if (!$recently || !is_array($recently)) {
                return $default;
            }
        }

        $newPath = '';
        $field  = trim($last, '.');

        if (strpos($field, '.')) {
            [$field, $newPath] = explode('.', $field, 2);
        }

        $result = [];
        foreach ($recently as $item) {
            if (is_array($item) && isset($item[$field])) {
                $result[] = $item[$field];
            }
        }

        if ('' === $newPath) {
            return $result;
        } elseif (empty($result)) {
            return $default;
        }

        if (false !== strpos($newPath, '*')) {
            $data = [];
            foreach ($result as $item) {
                $data = array_merge($data, $item);
            }
            return $this->getByPath($newPath, $default, $data);
        }

        $children = [];
        foreach ($result as $item) {
            $children[] = Helper::getValueOfArray($item, $newPath, $default);
        }

        return $children;
    }

    /**
     * @return bool
     */
    public function isValidated(): bool
    {
        return $this->_validated;
    }

    /**
     * @return bool
     */
    public function hasRule(): bool
    {
        return $this->getRules() ? true : false;
    }

    /**
     * @return array
     */
    public function getRules(): array
    {
        return array_merge($this->rules(), $this->_rules);
    }

    /**
     * @param array $rules
     *
     * @return $this
     */
    public function setRules(array $rules): self
    {
        $this->_rules = $rules;
        return $this;
    }

    /**
     * @return array
     */
    public function getUsedRules(): array
    {
        return $this->_usedRules;
    }

    /**
     * @return string
     */
    public function getScene(): string
    {
        return $this->scene;
    }

    /**
     * @param string $scene
     *
     * @return self
     */
    public function atScene(string $scene): self
    {
        $this->scene = trim($scene);
        return $this;
    }

    /**
     * alias of the `setScene()`
     *
     * @param string $scene
     *
     * @return static
     */
    public function setScene(string $scene)
    {
        return $this->atScene($scene);
    }

    /**
     * alias of the `setScene()`
     *
     * @param string $scene
     *
     * @return static
     */
    public function onScene(string $scene)
    {
        return $this->atScene($scene);
    }

    /**
     * @param bool $_skipOnEmpty
     *
     * @return static
     */
    public function setSkipOnEmpty(bool $_skipOnEmpty)
    {
        $this->_skipOnEmpty = $_skipOnEmpty;
        return $this;
    }

    /**
     * Get all items in collection
     * @return array The collection's source data
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Does this collection have a given key?
     *
     * @param string $key The data key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Get data item by key
     *
     * @param string $key The data key
     * @param mixed  $default The default value to return if data key does not exist
     *
     * @return mixed The key's value, or the default value
     */
    public function get(string $key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    /**
     * @param string $key
     * @param null   $default
     *
     * @return mixed
     */
    public function getRaw(string $key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    /**
     * Set data item by key
     *
     * @param string $key The data key
     * @param mixed  $value The data value
     *
     * @return $this
     */
    public function setRaw(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * alias of the setRow()
     * {@inheritdoc}
     */
    public function setValue(string $key, $value): self
    {
        return $this->setRaw($key, $value);
    }

    /**
     * Get data item by key
     *  支持以 '.' 分割进行子级值获取 eg: $this->get('goods.apple')
     *
     * @param string $key The data key
     * @param mixed  $default The default value
     * @param array  $data
     *
     * @return mixed The key's value, or the default value
     */
    public function getByPath(string $key, $default = null, array $data = null)
    {
        $data = $data ?: $this->data;

        if (strlen($key) > 2) {
            // eg. 'users.*.id' or '*.id'
            if (($startWithWildcard = 0 === strpos($key, '*.')) || strpos($key, '.*') > 0) {
                return $this->getByWildcard($key, $default, $data, $startWithWildcard);
            }
        }

        return Helper::getValueOfArray($data, $key, $default);
    }

    /**
     * alias of the 'getSafe()'
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function val(string $key, $default = null)
    {
        return $this->getSafe($key, $default);
    }

    /**
     * alias of the 'getSafe()'
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function safe(string $key, $default = null)
    {
        return $this->getSafe($key, $default);
    }

    /**
     * get safe field value
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getSafe(string $key, $default = null)
    {
        return $this->_safeData[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setSafe(string $key, $value): void
    {
        $this->_safeData[$key] = $value;
    }

    /**
     * @param bool $asObject
     *
     * @return array|stdClass
     */
    public function safeData($asObject = false)
    {
        return $this->getSafeData($asObject);
    }

    /**
     * @param bool $asObject
     *
     * @return array|stdClass
     */
    public function getSafeData($asObject = false)
    {
        return $asObject ? (object)$this->_safeData : $this->_safeData;
    }

    /**
     * @param array $safeData
     * @param bool  $clearOld
     */
    public function setSafeData(array $safeData, $clearOld = false): void
    {
        if ($clearOld) {
            $this->_safeData = [];
        }

        $this->_safeData = array_merge($this->_safeData, $safeData);
    }

    /**
     * Through the validation of the data keys
     * @return array
     */
    public function getSafeKeys(): array
    {
        return array_keys($this->_safeData);
    }
}

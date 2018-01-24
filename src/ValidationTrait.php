<?php
/**
 * Created by sublime 3.
 * Auth: Inhere
 * Date: 14-9-28
 * Time: 10:35
 */

namespace Inhere\Validate;

use Inhere\Validate\Utils\DataFiltersTrait;
use Inhere\Validate\Utils\Helper;
use Inhere\Validate\Utils\ErrorMessageTrait;
use Inhere\Validate\Utils\UserAndContextValidatorsTrait;

/**
 * Trait ValidationTrait
 * @package Inhere\Validate
 * property array $data To verify the data list. please define it on main class. 待验证的数据列表
 */
trait ValidationTrait
{
    use DataFiltersTrait, ErrorMessageTrait, UserAndContextValidatorsTrait;

    /**
     * the rules is by setRules()
     * @var array
     */
    private $_rules = [];

    /**
     * Through the validation of the data
     * @var array
     */
    private $_safeData = [];

    /** @var bool */
    private $_validated = false;

    /** @var \Closure before validate handler */
    private $_beforeHandler;

    /** @var \Closure after validate handler */
    private $_afterHandler;

    /**
     * current scenario name
     * 当前验证的场景 -- 如果需要让规则列表在多个类似情形下使用
     * (
     * e.g: 在MVC框架中，
     * - 通常可以根据控制器的 action name(add, edit, register) 来区分。
     * - 或者根据模型的场景(create, update, delete) 来区分。
     * )
     * @var string
     */
    protected $scene = '';

    /** @var array used rules at current scene */
    protected $_usedRules = [];

    /**
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * define attribute field translate list
     * @return array
     */
    public function translates()
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
    public function messages()
    {
        return [
            // validator name => message string
            // 'required' => '{attr} 是必填项。',
            // 'username.required' => '用户名 是必填项。',
        ];
    }

    /**
     * 当前场景需要收集的字段
     */
    // public function scenarios()
    // {
    //     return [
    //         // scene name => needed fields ...
    //         // 'scene' => ['filed1', 'field2'],
    //         // 'create' => ['filed1', 'field2'],
    //     ];
    // }

    /**
     * before validate handler
     * @param  \Closure $cb
     * @return static
     */
    public function onBeforeValidate(\Closure $cb)
    {
        $this->_beforeHandler = $cb;

        return $this;
    }

    public function beforeValidate()
    {
        // do something ...
    }

    /**
     * after validate handler
     * @param  \Closure $cb
     * @return static
     */
    public function onAfterValidate(\Closure $cb)
    {
        $this->_afterHandler = $cb;

        return $this;
    }

    public function afterValidate()
    {
        // do something ...
    }

    /*******************************************************************************
     * Validate
     ******************************************************************************/

    /**
     * 进行数据验证
     * @author inhere
     * @date   2015-08-11
     * @param  array $onlyChecked 可以设置此次需要验证的字段
     * @param  bool|null $stopOnError 是否出现错误即停止验证
     * @return static
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function validate(array $onlyChecked = null, $stopOnError = null)
    {
        if (!property_exists($this, 'data')) {
            throw new \InvalidArgumentException('Must be defined property "data"(array) in the sub-class used.');
        }

        if ($this->_validated) {
            return $this;
        }

        $this->resetValidation(true);
        $this->setStopOnError($stopOnError);
        $this->beforeValidate();

        if ($cb = $this->_beforeHandler) {
            $cb($this);
        }

        foreach ($this->collectRules() as $fields => $rule) {
            $fields = \is_string($fields) ? Helper::explode($fields) : (array)$fields;
            $validator = $rule[0];

            // 如何判断属性为空 默认使用 Validators::isEmpty(). 也可自定义
            $isEmpty = [Validators::class, 'isEmpty'];
            if (!empty($rule['isEmpty']) && (\is_string($rule['isEmpty']) || $rule['isEmpty'] instanceof \Closure)) {
                $isEmpty = $rule['isEmpty'];
            }

            // 验证的前置条件 -- 不满足条件,跳过此条规则
            $when = $rule['when'] ?? null;
            if ($when && ($when instanceof \Closure) && $when($this->data, $this) !== true) {
                continue;
            }

            // 为空时是否跳过(非 required 时). 参考自 yii2
            $skipOnEmpty = $rule['skipOnEmpty'] ?? true;
            $filters = $rule['filter'] ?? null;  // 使用过滤器
            $defMsg = $rule['msg'] ?? null; // 自定义错误消息
            $defValue = $rule['default'] ?? null;// 允许默认值

            // clear all keywords options. 0 is the validator
            unset($rule[0], $rule['msg'], $rule['default'], $rule['skipOnEmpty'], $rule['isEmpty'], $rule['when'], $rule['filter']);

            // 余下的都作为验证器参数, 有一些验证器需要参数。 e.g. size()
            $args = $rule;

            foreach ($fields as $field) {
                if (!$field || ($onlyChecked && !\in_array($field, $onlyChecked, true))) {
                    continue;
                }

                $value = $this->getByPath($field, $defValue);

                if (\is_string($validator)) {
                    if ($validator === 'safe') {
                        $this->setSafe($field, $value);
                        continue;
                    }

                    // required*系列字段检查 || 文件资源检查
                    if (self::isCheckRequired($validator) || self::isCheckFile($validator)) {
                        if (!$this->fieldValidate($field, $value, $validator, $args, $defMsg) && $this->isStopOnError()) {
                            break;
                        }

                        continue;
                    }
                }

                // 设定了为空跳过 并且 值为空
                if ($skipOnEmpty && Helper::call($isEmpty, $value)) {
                    continue;
                }

                // 字段值过滤(有通配符`*`的字段, 不应用过滤器)
                if ($filters && !strpos($field, '.*')) {
                    $value = $this->valueFiltering($value, $filters);
                    $this->data[$field] = $value;
                }

                // 字段值验证检查
                if (!$this->valueValidate($field, $value, $validator, $args, $defMsg) && $this->isStopOnError()) {
                    break;
                }
            }

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

        if ($cb = $this->_afterHandler) {
            $cb($this);
        }

        // fix : deny repeat validate
        $this->_validated = true;

        return $this;
    }

    /**
     * field require Validate 字段存在检查
     * @param string $field 属性名称
     * @param mixed $value 属性值
     * @param string $validator required* 验证器
     * @param array $args 验证需要的参数
     * @param string $defMsg
     * @return bool
     * @throws \InvalidArgumentException
     */
    protected function fieldValidate(string $field, $value, string $validator, array $args, $defMsg): bool
    {
        // required 检查
        if ($validator === 'required') {
            $passed = $this->required($field, $value);

            // 文件资源检查 方法
        } elseif (self::isCheckFile($validator)) {
            $passed = $this->$validator($field, ...array_values($args));

            // 其他 required* 方法
        } elseif (method_exists($this, $validator)) {
            $passed = $this->$validator($field, $value, ...array_values($args));
        } else {
            throw new \InvalidArgumentException("The validator [$validator] is not exists!");
        }

        // validate success, save value to safeData
        if ($passed) {
            $this->collectSafeValue($field, $value);

            return true;
        }

        $this->addError($field, $this->getMessage($validator, $field, $args, $defMsg));

        return false;
    }

    /**
     * value Validate 字段值验证
     * @param string $field 属性名称
     * @param mixed $value 属性值
     * @param \Closure|string $validator 验证器
     * @param array $args 验证需要的参数
     * @param string $defMsg
     * @return bool
     */
    protected function valueValidate(string $field, $value, $validator, array $args, $defMsg): bool
    {
        // if field don't exists.
        if (null === $value) {
            return false;
        }

        $rawArgs = $args;
        $args = array_values($args);

        // if $validator is a closure OR a object has method '__invoke'
        if (\is_object($validator) && method_exists($validator, '__invoke')) {
            $args[] = $this->data;
            $passed = $validator($value, ...$args);
        } elseif (\is_string($validator)) {
            // if $validator is a custom add callback in the property {@see $_validators}.
            if (isset(self::$_validators[$validator])) {
                $callback = self::$_validators[$validator];
                $passed = $callback($value, ...$args);

                // if $validator is a custom method of the subclass.
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
     * @param string $field
     * @param mixed $value
     */
    protected function collectSafeValue(string $field, $value)
    {
        // 进行的是子级属性检查 eg: 'goods.apple'
        if ($pos = strpos($field, '.')) {
            $field = (string)substr($field, 0, $pos);
            $value = $this->data[$field];
        }

        // set
        $this->_safeData[$field] = $value;
    }

    /**
     * @param bool|false $clearErrors
     * @return $this
     */
    protected function resetValidation($clearErrors = false)
    {
        $this->_validated = false;
        $this->_safeData = $this->_usedRules = [];

        if ($clearErrors) {
            $this->clearErrors();
        }

        return $this;
    }

    /**
     * 收集当前场景可用的规则列表
     * Collect the current scenario of the available rules list
     * @throws \InvalidArgumentException
     */
    protected function collectRules()
    {
        $scene = $this->scene;

        foreach ($this->getRules() as $rule) {
            // check fields
            if (!isset($rule[0]) || !$rule[0]) {
                throw new \InvalidArgumentException('Please setting the fields(string|array) to wait validate! position: rule[0].');
            }

            // check validator
            if (!isset($rule[1]) || !$rule[1]) {
                throw new \InvalidArgumentException('The rule validator is must be setting! position: rule[1].');
            }

            // only use to special scene.
            if (!empty($rule['on'])) {
                if (!$scene) {
                    continue;
                }

                $sceneList = \is_string($rule['on']) ? Helper::explode($rule['on']) : (array)$rule['on'];

                if (!\in_array($scene, $sceneList, true)) {
                    continue;
                }

                unset($rule['on']);
            }

            $this->_usedRules[] = $rule;
            $fields = array_shift($rule);
            $this->prepareRule($rule);

            yield $fields => $rule;
        }

        //
    }

    /**
     * @param array $rule
     */
    protected function prepareRule(array &$rule)
    {
        $validator = $rule[0];

        if (!\is_string($validator)) {
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
     * @param string $path 'users.*.id' 'goods.*' 'foo.bar.*.id'
     * @param null|mixed $default
     * @return mixed
     */
    protected function getByWildcard(string $path, $default = null)
    {
        list($first, $last) = explode('.*', $path, 2);
        $recently = Helper::getValueOfArray($this->data, $first, $default);

        // 'goods.*'
        if ('' === $last) {
            return $recently;
        }

        if (!$recently || !\is_array($recently)) {
            return $default;
        }

        $last = trim($last, '.');
        $result = [];

        foreach ($recently as $item) {
            $result[] = $item[$last];
        }

        return $result;
    }

    /**
     * @param string $path 'users.*.id'
     * @return bool
     */
    protected function hasWildcard(string $path): bool
    {
        return strpos($path, '.*') > 0;
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
     * @return $this
     */
    public function setRules(array $rules)
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
     * @return static
     */
    public function setScene(string $scene)
    {
        $this->scene = trim($scene);

        return $this;
    }

    /**
     * alias of the `setScene()`
     * @param string $scene
     * @return static
     */
    public function atScene(string $scene)
    {
        return $this->setScene($scene);
    }

    /**
     * alias of the `setScene()`
     * @param string $scene
     * @return static
     */
    public function onScene(string $scene)
    {
        return $this->setScene($scene);
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
     * @param string $key The data key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Set data item
     * @param string $key The data key
     * @param mixed $value The data value
     * @return $this
     */
    public function setValue($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Get data item by key
     * @param string $key The data key
     * @param mixed $default The default value to return if data key does not exist
     * @return mixed The key's value, or the default value
     */
    public function get(string $key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function getRaw(string $key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    /**
     * Get data item by key
     *  支持以 '.' 分割进行子级值获取 eg: $this->get('goods.apple')
     * @param string $key The data key
     * @param mixed $default The default value
     * @return mixed The key's value, or the default value
     */
    public function getByPath(string $key, $default = null)
    {
        if ($this->hasWildcard($key)) {
            return $this->getByWildcard($key);
        }

        return Helper::getValueOfArray($this->data, $key, $default);
    }

    /**
     * alias of the 'getSafe()'
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function val(string $key, $default = null)
    {
        return $this->getSafe($key, $default);
    }

    /**
     * alias of the 'getSafe()'
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getValid(string $key, $default = null)
    {
        return $this->getSafe($key, $default);
    }

    /**
     * get safe field value
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSafe(string $key, $default = null)
    {
        return $this->_safeData[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setSafe(string $key, $value)
    {
        $this->_safeData[$key] = $value;
    }

    /**
     * @param bool $asObject
     * @return array|\stdClass
     */
    public function safeData($asObject = false)
    {
        return $this->getSafeData($asObject);
    }

    /**
     * @param bool $asObject
     * @return array|\stdClass
     */
    public function getSafeData($asObject = false)
    {
        return $asObject ? (object)$this->_safeData : $this->_safeData;
    }

    /**
     * @param array $safeData
     * @param bool $clearOld
     */
    public function setSafeData(array $safeData, $clearOld = false)
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

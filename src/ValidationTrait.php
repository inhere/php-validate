<?php
/**
 * Created by sublime 3.
 * Auth: Inhere
 * Date: 14-9-28
 * Time: 10:35
 */

namespace Inhere\Validate;

use Inhere\Validate\Utils\Helper;
use Inhere\Validate\Utils\ErrorMessageTrait;
use Inhere\Validate\Utils\ErrorInformationTrait;
use Inhere\Validate\Utils\RequiredValidatorsTrait;

/**
 * Trait ValidationTrait
 * @package Inhere\Validate
 * @property array $data To verify the data list. please define it on main class. 待验证的数据列表
 */
trait ValidationTrait
{
    use ErrorInformationTrait, ErrorMessageTrait, RequiredValidatorsTrait;

    /**
     * custom add's validator by addValidator()
     * @var array
     */
    private static $_validators = [];

    private $_filters = [];

    /**
     * current scenario name
     * 当前验证的场景 -- 如果需要让规则列表在多个类似情形下使用
     * (e.g: 在MVC框架中，通常可以根据 controller 的 action name 来区分。 e.g. add, edit, register)
     * @var string
     */
    protected $scene = '';

    /**
     * @var bool
     */
    private $_validated = false;

    /**
     * Through the validation of the data
     * @var array
     */
    private $_safeData = [];

    /**
     * the rules is by setRules()
     * @var array
     */
    private $_rules = [];

    /**
     * available rules at current scene
     * @var array
     */
    private $_availableRules = [];

    /**
     * before validate handler
     * @var \Closure
     */
    private $_beforeHandler;

    /**
     * after validate handler
     * @var \Closure
     */
    private $_afterHandler;

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
            // 'required.username' => '用户名 是必填项。',
        ];
    }

    /**
     * define attribute field translate list
     * @deprecated please use translates() instead.
     * @return array
     */
    public function attrTrans()
    {
        return $this->translates();
    }

//////////////////////////////////// Validate ////////////////////////////////////

    /**
     * before validate handler
     * @param  \Closure $cb
     * @return static
     */
    public function beforeValidate(\Closure $cb)
    {
        $this->_beforeHandler = $cb;

        return $this;
    }

    /**
     * after validate handler
     * @param  \Closure $cb
     * @return static
     */
    public function afterValidate(\Closure $cb)
    {
        $this->_afterHandler = $cb;

        return $this;
    }

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
    public function validate(array $onlyChecked = [], $stopOnError = null)
    {
        if (!property_exists($this, 'data')) {
            throw new \InvalidArgumentException('Must be defined property "data"(array) in the sub-class used.');
        }

        if ($this->_validated) {
            return $this;
        }

        $this->resetValidation(true);
        $this->setStopOnError($stopOnError);

        if ($cb = $this->_beforeHandler) {
            $cb($this);
        }

        $data = $this->data;

        // 循环规则
        foreach ($this->collectRules() as $attrs => $rule) {
            $attrs = \is_string($attrs) ? array_map('trim', explode(',', $attrs)) : (array)$attrs;
            // 要使用的验证器(a string or a Closure)
            $validator = array_shift($rule);

            // 为空时是否跳过(非 required 时). 参考自 yii2
            $skipOnEmpty = $rule['skipOnEmpty'] ?? true;

            // 如何判断属性为空 默认使用 ValidatorList::isEmpty(). 也可自定义
            $isEmpty = [ValidatorList::class, 'isEmpty'];
            if (isset($rule['isEmpty']) && $rule['isEmpty'] instanceof \Closure) {
                $isEmpty = $rule['isEmpty'];
            }

            // 自定义当前验证的错误提示消息
            $message = $rule['msg'] ?? null;
            // 允许默认值
            $defValue = $rule['default'] ?? null;

            // 验证的前置条件 -- 不满足条件,跳过此条规则
            $when = $rule['when'] ?? null;
            if ($when && $when instanceof \Closure && $when($data, $this) !== true) {
                continue;
            }

            // clear some fields
            unset($rule['msg'], $rule['default'], $rule['skipOnEmpty'], $rule['isEmpty'], $rule['when']);

            // 验证设置, 有一些验证器需要参数。 e.g. size()
            $args = $rule;

            // 循环检查属性
            foreach ($attrs as $attr) {
                $value = $this->getValue($attr, $defValue);

                // 不在需要检查的列表内
                if ($onlyChecked && !\in_array($attr, $onlyChecked, true)) {
                    continue;
                }

                // mark attribute is safe. not need validate. like. 'created_at'
                if ($validator === 'safe') {
                    $this->_safeData[$attr] = $value;
                    continue;
                }

                // required* 系列字段检查器
                if (\is_string($validator) && 0 === strpos($validator, 'required')) {
                    if (!$this->fieldValidate($attr, $value, $validator, $args)) {
                        $this->addError($attr, $this->getMessage($validator, $attr, $args, $message));

                        if ($this->isStopOnError()) {
                            break;
                        }
                    }

                    continue;
                }

                // 设定了为空跳过 并且 值为空
                if ($skipOnEmpty && Helper::call($isEmpty, $value)) {
                    continue;
                }

                // 字段值检查 failed
                if (!$this->valueValidate($data, $attr, $value, $validator, $args)) {
                    $this->addError($attr, $this->getMessage($validator, $attr, $args, $message));

                    if ($this->isStopOnError()) {
                        break;
                    }
                }
            }

            // There is an error an immediate end to verify
            if ($this->isStopOnError() && $this->hasError()) {
                break;
            }
        }

        // fix: has error, clear safe data.
        if ($this->hasError()) {
            $this->_safeData = [];
        }

        if ($cb = $this->_afterHandler) {
            $cb($this);
        }

        // fix : deny repeat validate
        $this->_validated = true;

        unset($data);
        return $this;
    }

    /**
     * field required Validate 字段名存在 检查
     * @param string $attr 属性名称
     * @param mixed $value 属性值
     * @param string $validator required* 验证器
     * @param array $args 验证需要的参数
     * @return bool
     * @throws \InvalidArgumentException
     */
    protected function fieldValidate($attr, $value, $validator, $args)
    {
        // required 检查
        if ($validator === 'required') {
            $passed = $this->required($attr);

            // 其他 required* 方法
        } elseif (method_exists($this, $validator)) {
            $args = array_values($args);
            $passed = $this->$validator($attr, ...$args);
        } else {
            throw new \InvalidArgumentException("The validator [$validator] is not exists!");
        }

        // validate success, save value to safeData
        if ($passed) {
            $this->collectSafeValue($attr, $value);

            return true;
        }

        return false;
    }

    /**
     * do Validate 字段值 检查
     * @param array $data 原始数据列表
     * @param string $attr 属性名称
     * @param mixed $value 属性值
     * @param \Closure|string $validator 验证器
     * @param array $args 验证需要的参数
     * @return bool
     * @throws \InvalidArgumentException
     */
    protected function valueValidate($data, $attr, $value, $validator, $args)
    {
        // if attr don't exists.
        if (null === $value) {
            return false;
        }

        $args = array_values($args);

        // if $validator is a closure
        if (\is_object($validator) && method_exists($validator, '__invoke')) {
            $args[] = $data;
            $passed = $validator($value, ...$args);
        } elseif (\is_string($validator)) {
            // if $validator is a custom add callback in the property {@see $_validators}.
            if (isset(self::$_validators[$validator])) {
                $callback = self::$_validators[$validator];
                $passed = $callback($value, ...$args);

                // if $validator is a custom method of the subclass.
            } elseif (method_exists($this, $validator)) {
                $passed = $this->$validator($value, ...$args);

                // $validator is a method of the class 'ValidatorList'
            } elseif (method_exists(ValidatorList::class, $validator)) {
                $passed = ValidatorList::$validator($value, ...$args);

                // it is function name
            } elseif (\function_exists($validator)) {
                $passed = $validator($value, ...$args);
            } else {
                throw new \InvalidArgumentException("The validator [$validator] don't exists!");
            }
        } else {
            throw new \InvalidArgumentException('Validator type is error, must is String or Closure!');
        }

        // validate success, save value to safeData
        if ($passed) {
            $this->collectSafeValue($attr, $value);

            return true;
        }

        return false;
    }

    /**
     * @param bool|false $clearErrors
     * @return $this
     */
    protected function resetValidation($clearErrors = false)
    {
        $this->_validated = false;
        $this->_safeData = $this->_availableRules = [];

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
                throw new \InvalidArgumentException('Please setting the attrs(string|array) to wait validate! position: rule[0].');
            }

            // check validator
            if (!isset($rule[1]) || !$rule[1]) {
                throw new \InvalidArgumentException('The rule validator is must be setting! position: rule[1].');
            }

            // global rule.
            if (empty($rule['on'])) {
                $this->_availableRules[] = $rule;

                // only use to special scene.
            } else {
                $sceneList = \is_string($rule['on']) ? array_map('trim', explode(',', $rule['on'])) : (array)$rule['on'];

                if ($scene && !\in_array($scene, $sceneList, true)) {
                    continue;
                }

                unset($rule['on']);
                $this->_availableRules[] = $rule;
            }

            $attrs = array_shift($rule);

            yield $attrs => $rule;
        }

        // return $this->_availableRules;
    }

    /**
     * collect Safe Value
     * @param string $attr
     * @param mixed $value
     */
    protected function collectSafeValue($attr, $value)
    {
        // 进行的是子级属性检查 eg: 'goods.apple'
        if ($pos = strpos($attr, '.')) {
            $firstLevelKey = substr($attr, 0, $pos);
            $this->_safeData[$firstLevelKey] = $this->data[$firstLevelKey];
        } else {
            $this->_safeData[$attr] = $value;
        }
    }

    /*******************************************************************************
     * Filters
     ******************************************************************************/

    /**
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

//////////////////////////////////// custom validators ////////////////////////////////////

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
     * @param \Closure $callback
     * @param string $msg
     * @return $this
     */
    public function addValidator(string $name, \Closure $callback, string $msg = '')
    {
        self::setValidator($name, $callback, $msg);

        return $this;
    }

    /**
     * add a custom validator
     * @param string $name
     * @param \Closure $callback
     * @param string $msg
     */
    public static function setValidator(string $name, \Closure $callback, string $msg = null)
    {
        self::$_validators[$name] = $callback;

        if ($msg) {
            self::setDefaultMessage($name, $msg);
        }
    }

    /**
     * @param string $name
     * @return null|\Closure
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
     * @return bool|\Closure
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

//////////////////////////////////// getter/setter ////////////////////////////////////

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
    public function getAvailableRules(): array
    {
        return $this->_availableRules;
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
        $this->scene = $scene;

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
     * Get data item by key
     *  支持以 '.' 分割进行子级值获取 eg: $this->get('goods.apple')
     * @param string $key The data key
     * @param mixed $default The default value
     * @return mixed The key's value, or the default value
     */
    public function getValue(string $key, $default = null)
    {
        return Helper::getValueOfArray($this->data, $key, $default);
    }

    /**
     * get safe attribute value
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSafe(string $key, $default = null)
    {
        return $this->getValid($key, $default);
    }

    /**
     * get safe attribute value
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getValid(string $key, $default = null)
    {
        return array_key_exists($key, $this->_safeData) ? $this->_safeData[$key] : $default;
    }

    /**
     * @return array
     */
    public function getSafeData(): array
    {
        return $this->_safeData;
    }

    /**
     * @return array
     */
    public function getSafeKeys(): array
    {
        return array_keys($this->_safeData);
    }
}

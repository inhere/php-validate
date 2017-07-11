<?php
/**
 * Created by sublime 3.
 * Auth: Inhere
 * Date: 14-9-28
 * Time: 10:35
 * Used: 主要功能是 hi
 */

namespace inhere\validate;

/**
 * Trait ValidationTrait
 * @package inhere\validate
 *
 * @property array $data To verify the data list. please define it on main class. 待验证的数据列表
 */
trait ValidationTrait
{
    /**
     * current scenario name
     * 当前验证的场景 -- 如果需要让规则列表在多个类似情形下使用
     * (e.g: 在MVC框架中，通常可以根据 controller 的 action name 来区分。 e.g. add, edit, register)
     * @var string
     */
    protected $scene = '';

    /**
     * Whether there is error stop validation 是否出现验证失败就立即停止验证
     * True  -- 出现一个验证失败即停止验证,并退出
     * False -- 全部验证并将错误信息保存到 {@see $_errors}
     * @var boolean
     */
    private $_stopOnError = true;

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
     * 保存所有的验证错误信息
     * @var array[]
     * [
     *     [ field => errorMessage1 ],
     *     [ field => errorMessage2 ],
     *     [ field2 => errorMessage3 ]
     * ]
     */
    private $_errors = [];

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
     * custom append's validator by addValidator()
     * @var array
     */
    private $_validators = [];

    /**
     * attribute field translate list
     * @var array
     */
    private $_attrTrans = [];

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
        return [
        // ['fields', 'validator', 'arg1', 'arg2' ...]
        ];
    }

    /**
     * define attribute field translate list
     * @return array
     */
    public function attrTrans()
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
        ];
    }

//////////////////////////////////// Validate ////////////////////////////////////

    /**
     * before validate handler
     * @param  \Closure $cb
     * @return static
     */
    public function before(\Closure $cb)
    {
        $this->_beforeHandler = $cb;

        return $this;
    }

    /**
     * after validate handler
     * @param  \Closure $cb
     * @return static
     */
    public function after(\Closure $cb)
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
     * @throws \RuntimeException
     */
    public function validate(array $onlyChecked = [], $stopOnError = null)
    {
        if (!property_exists($this, 'data')) {
            throw new \InvalidArgumentException('Must be defined property \'data (array)\' in the sub-class used.');
        }

        if ($this->_validated) {
            return $this;
        }

        $this->resetRuntimeData(true);

        if ($cb = $this->_beforeHandler) {
            $cb($this);
        }

        $stopOnError !== null && $this->setStopOnError((bool)$stopOnError);

        $data = $this->data;

        // 循环规则
        foreach ($this->collectRules() as $rule) {
            // 要检查的属性(字段)名称集
            $attrs = array_shift($rule);
            $attrs = is_string($attrs) ? array_map('trim', explode(',', $attrs)) : (array)$attrs;

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

            // 验证的前置条件 -- 不满足条件,跳过此条规则
            $when = $rule['when'] ?? null;
            if ($when && $when instanceof \Closure && $when($data, $this) !== true) {
                continue;
            }

            // clear some fields
            unset($rule['msg'], $rule['skipOnEmpty'], $rule['isEmpty'], $rule['when']);

            // 验证设置, 有一些验证器需要参数。 e.g. size()
            $args = $rule;

            // 循环检查属性
            foreach ($attrs as $attr) {
                $value = $this->getValue($attr);

                // 不在需要检查的列表内
                if ($onlyChecked && !in_array($attr, $onlyChecked, true)) {
                    continue;
                }

                // mark attribute is safe. not need validate. like. 'created_at'
                if ($validator === 'safe') {
                    $this->_safeData[$attr] = $value;
                    continue;
                }

                // required* 系列字段检查器
                if (is_string($validator) && 0 === strpos($validator, 'required')) {
                    if (!$this->requiredValidate($attr, $value, $validator, $args)) {
                        $this->_errors[] = [
                            $attr => $this->getMessage($validator, ['{attr}' => $attr], $args, $message)
                        ];

                        if ($this->_stopOnError) {
                            break;
                        }
                    }

                    continue;
                }

                // 设定了为空跳过 并且 值为空
                if ($skipOnEmpty && call_user_func($isEmpty, $value)) {
                    continue;
                }

                // 字段值检查 failed
                if (!$this->doValidate($data, $attr, $value, $validator, $args)) {
                    $this->_errors[] = [
                        $attr => $this->getMessage($validator, ['{attr}' => $attr], $args, $message)
                    ];

                    if ($this->_stopOnError) {
                        break;
                    }
                }
            }

            // There is an error an immediate end to verify
            if ($this->_stopOnError && $this->hasError()) {
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
     * required Validate 字段名存在 检查
     *
     * @param string $attr      属性名称
     * @param mixed  $value     属性值
     * @param string $validator required* 验证器
     * @param array  $args      验证需要的参数
     *
     * @return bool
     */
    protected function requiredValidate($attr, $value, $validator, $args)
    {
        // required 检查
        if ($validator === 'required') {
            $result = $this->required($attr);

            // 其他 required* 方法
        } elseif (method_exists($this, $validator)) {
            // 压入当前属性/字段名
            array_unshift($args, $attr);

            $result = $this->$validator(...$args);
        } else {
            throw new \InvalidArgumentException("The validator [$validator] is not exists!");
        }

        // validate success, save value to safeData
        if ($result) {
            $this->collectSafeValue($attr, $value);

            return true;
        }

        return false;
    }

    /**
     * do Validate 字段值 检查
     * @param array $data 原始数据列表
     * @param string $attr  属性名称
     * @param mixed $value 属性值
     * @param \Closure|string $validator 验证器
     * @param array $args 验证需要的参数
     * @return bool
     */
    protected function doValidate($data, $attr, $value, $validator, $args)
    {
        // if attr don't exists.
        if (null === $value) {
            return false;
        }

        // 压入当前属性值 e.g. ValidatorList::range($val, $min, $max)
        array_unshift($args, $value);

        // if $validator is a closure
        if ($validator instanceof \Closure) {
            $args[] = $data;
            $passed = $validator(...$args);

        } elseif (is_string($validator)) {

            // if $validator is a custom add callback in the property {@see $_validators}.
            if (isset($this->_validators[$validator])) {
                $callback = $this->_validators[$validator];
                $passed = $callback(...$args);

                // if $validator is a custom method of the subclass.
            } elseif (method_exists($this, $validator)) {
                $passed = $this->$validator(...$args);

                // $validator is a method of the class 'ValidatorList'
            } elseif (method_exists(ValidatorList::class, $validator)) {

                $passed = call_user_func_array([ValidatorList::class, $validator], $args);
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
    protected function resetRuntimeData($clearErrors = false)
    {
        $this->_safeData = $this->_availableRules = [];

        if ($clearErrors) {
            $this->clearErrors();
        }

        return $this;
    }

    /**
     * add a custom validator
     *
     * ```
     * $valid = ValidatorClass::make($_POST)
     *          ->addValidator('name',function($var [, $arg1, $arg2 ... ]){
     *              return $var === 23;
     *          });
     * $valid->validate();
     * ```
     *
     * @param string $name
     * @param \Closure $callback
     * @param string $msg
     * @return $this
     */
    public function addValidator(string $name, \Closure $callback, string $msg = '')
    {
        $this->_validators[$name] = $callback;

        if ($msg) {
            self::$_defaultMessages[$name] = $msg;
        }

        return $this;
    }

    /**
     * 收集当前场景可用的规则列表
     * Collect the current scenario of the available rules list
     */
    protected function collectRules()
    {
        $scene = $this->scene;

        // 循环规则, 搜集当前场景可用的规则
        foreach ($this->getRules() as $rule) {
            // check attrs
            if (!isset($rule[0]) && !$rule[0]) {
                throw new \InvalidArgumentException('Please setting the attrs(string|array) to wait validate! position: rule[0].');
            }

            // check validator
            if (!is_string($rule[1]) && !($rule[1] instanceof \Closure)) {
                throw new \InvalidArgumentException('The rule validator rule must be is a validator name or a Closure! position: rule[1].');
            }

            // global rule.
            if (empty($rule['on'])) {
                $this->_availableRules[] = $rule;

                // only use to special scene.
            } else {
                $sceneList = is_string($rule['on']) ? array_map('trim', explode(',', $rule['on'])) : (array)$rule['on'];

                if (in_array($scene, $sceneList, true)) {
                    unset($rule['on']);
                    $this->_availableRules[] = $rule;
                }
            }

            yield $rule;
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

//////////////////////////////////// extra validate methods ////////////////////////////////////

    /**
     * 验证字段必须存在输入数据，且不为空。字段符合下方任一条件时即为「空」
     * - 该值为 null.
     * - 该值为空字符串。
     * - 该值为空数组
     *
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
     *
     * @from laravel
     * @param  string $field
     * @param  string $anotherField
     * @param  array|string  $values
     * @return bool
     */
    public function requiredIf($field, $anotherField, $values)
    {
        if (!isset($this->data[$anotherField])) {
            return false;
        }

        $val = $this->data[$anotherField];

        if (in_array($val, (array)$values, true)) {
            return $this->required($field);
        }

        return false;
    }

    /**
     * 如果指定的其它字段（ anotherField ）值等于任何一个 value 时，此字段为 不必填
     *
     * @from laravel
     * @param  string $field
     * @param  string $anotherField
     * @param  array|string  $values
     * @return bool
     */
    public function requiredUnless($field, $anotherField, $values)
    {
        if (!isset($this->data[$anotherField])) {
            return false;
        }

        if (in_array($this->data[$anotherField], (array)$values, true)) {
            return true;
        }

        return $this->required($field);
    }

    /**
     * 如果指定的字段中的 任意一个 有值且不为空，则此字段为必填
     *
     * @from laravel
     * @param  string $field
     * @param  array|string  $fields
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
     *
     * @from laravel
     * @param  string $field
     * @param  array|string  $fields
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
     *
     * @from laravel
     * @param  string $field
     * @param  array|string  $fields
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
     *
     * @from laravel
     * @param  string $field
     * @param  array|string  $fields
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

//////////////////////////////////// error info ////////////////////////////////////

    /**
     * @return $this
     */
    public function clearErrors()
    {
        $this->_errors = [];

        return $this;
    }

    /**
     * 是否有错误
     * @return boolean
     */
    public function hasError(): bool
    {
        return $this->isFail();
    }

    /**
     * @return bool
     */
    public function isFail(): bool
    {
        return count($this->_errors) > 0;
    }

    /**
     * @return bool
     */
    public function fail(): bool
    {
        return $this->isFail();
    }

    /**
     * @return bool
     */
    public function passed(): bool
    {
        return !$this->isFail();
    }

    /**
     * @param string $attr
     * @param string $msg
     */
    public function addError(string $attr, string $msg)
    {
        $this->_errors[] = [$attr => $msg];
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->_errors;
    }

    /**
     * 得到第一个错误信息
     * @author inhere
     * @param bool $onlyMsg
     * @return array|string
     */
    public function firstError($onlyMsg = true)
    {
        $e = $this->_errors;
        $first = array_shift($e);

        return $onlyMsg ? array_values($first)[0] : $first;
    }

    /**
     * 得到最后一个错误信息
     * @author inhere
     * @param bool $onlyMsg
     * @return array|string
     */
    public function lastError($onlyMsg = true)
    {
        $e = $this->_errors;
        $last = array_pop($e);

        return $onlyMsg ? array_values($last)[0] : $last;
    }

    /**
     * (过滤器)默认的错误提示信息
     * @var array
     */
    private static $_defaultMessages = [
        'int' => '{attr} must be an integer!',
        'integer' => '{attr} must be an integer!',
        'num' => '{attr} must be an integer greater than 0!',
        'number' => '{attr} must be an integer greater than 0!',
        'bool' => '{attr} must be is boolean!',
        'boolean' => '{attr} must be is boolean!',
        'float' => '{attr} must be is float!',
        'url' => '{attr} is not a url address!',
        'email' => '{attr} is not a email address!',
        'date' => '{attr} is not a date format!',
        'dateFormat' => '{attr} is not in a {value0} date format !',
        'ip' => '{attr} is not IP address!',
        'ipv4' => '{attr} is not a IPv4 address!',
        'ipv6' => '{attr} is not a IPv6 address!',
        'required' => 'parameter {attr} is required!',
        'length' => '{attr} length must at rang {min} ~ {max}',
        'size' => '{attr} must be an integer and at rang {min} ~ {max}',
        'range' => '{attr} must be an integer and at rang {min} ~ {max}',
        'min' => '{attr} minimum boundary is {value0}',
        'max' => '{attr} maximum boundary is {value0}',
        'in' => '{attr} must in ({value0})',
        'notIn' => '{attr} cannot in ({value0})',
        'string' => '{attr} must be a string',
        'regexp' => '{attr} does not match the {value0} conditions',
        'compare' => '{attr} must be equals to {value0}',
        'same' => '{attr} must be equals to {value0}',
        'isArray' => '{attr} must be an array',
        'isMap' => '{attr} must be an array and is key-value format',
        'isList' => '{attr} must be an array of nature',
        'intList' => '{attr} must be an array and value is all integers',
        'strList' => '{attr} must be an array and value is all strings',
        'json' => '{attr} must be an json string',
        'callback' => '{attr} don\'t pass the test and verify!',
        '_' => '{attr} validation is not through!',
    ];

    /**
     * @return array
     */
    public static function getDefaultMessages(): array
    {
        return self::$_defaultMessages;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return array_merge(self::$_defaultMessages, $this->messages());
    }

    /**
     * 各个验证器的提示消息
     * @author inhere
     * @date   2015-09-27
     * @param  string|\Closure $validator 验证器
     * @param  array $params 待替换的参数
     * @param  array $args
     * @param  string $msg 自定义提示消息
     * @return string
     */
    public function getMessage($validator, array $params, array $args = [], $msg = null)
    {
        $name = $validator instanceof \Closure ? 'callback' : $validator;

        if (!$msg) {
            $msgList = $this->getMessages();
            $msg = $msgList[$name] ?? $msgList['_'];
        }

        $params['{attr}'] = $this->getAttrTran($params['{attr}']);

        foreach ($args as $key => $value) {
            $key = is_int($key) ? "value$key" : $key;
            $params['{' . $key . '}'] = is_array($value) ? implode(',', $value) : $value;
        }

        return strtr($msg, $params);
    }

//////////////////////////////////// getter/setter ////////////////////////////////////

    /**
     * @param bool $stopOnError
     * @return $this
     */
    public function setStopOnError(bool $stopOnError = true)
    {
        $this->_stopOnError = $stopOnError;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStopOnError(): bool
    {
        return $this->_stopOnError;
    }

    /**
     * @return bool
     */
    public function isValidated(): bool
    {
        return $this->_validated;
    }

    /**
     * @return array
     */
    public function getValidators(): array
    {
        return $this->_validators;
    }

    /**
     * @param string $attr
     * @return string
     */
    public function getAttrTran(string $attr): string
    {
        $trans = $this->getAttrTrans();

        return $trans[$attr] ?? Helper::toUnderscoreCase($attr, ' ');
    }

    /**
     * @return array
     */
    public function getAttrTrans(): array
    {
        return array_merge($this->attrTrans(), $this->_attrTrans);
    }

    /**
     * set the attrs translation data
     * @param array $attrTrans
     * @return $this
     */
    public function setAttrTrans(array $attrTrans)
    {
        $this->_attrTrans = $attrTrans;

        return $this;
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
     *
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

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
        return [];
        /* e.g:
            return [
                // not set 'scene', enable this rule at all scene.
                [ 'tagId,userId', 'required', 'msg' => '{attr} is required!'],

                // set scene is add -- when `$this->scene == 'add'` enable this rule.
                [ 'tagId', 'size', 'min'=>4, 'max'=>567, 'on' => 'add' ],

                // use callback and custom error message
                [ 'userId', function($value){ return $value > 1;}, 'msg' => '{attr} is must gt 1!'],
            ];
       */
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
     * 自定义验证器的错误消息格式
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

    public function before(\Closure $cb)
    {
        $this->_beforeHandler = $cb;

        return $this;
    }

    public function after(\Closure $cb)
    {
        $this->_afterHandler = $cb;

        return $this;
    }

    /**
     * 进行数据验证
     * @author inhere
     * @date   2015-08-11
     * @param array $onlyChecked 只检查一部分属性
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

            // 如何判断属性为空 默认使用 empty($data[$attr]). 也可自定义
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

            // 验证设置, 有一些验证器需要设置参数。 e.g. size()
            $copy = $rule;

            // 循环检查属性
            foreach ($attrs as $attr) {
                // 不在需要检查的列表内 || 值为空并且设定了为空跳过
                if (
                    ($onlyChecked && !in_array($attr, $onlyChecked, true)) ||
                    ($validator !== 'required' && $skipOnEmpty && call_user_func($isEmpty, $data, $attr))
                ) {
                    continue;
                }

                // mark attribute is safe. not need validate. like. 'created_at'
                if ($validator === 'safe') {
                    $this->_safeData[$attr] = $data[$attr];
                    continue;
                }

                [$result, $validator] = $this->doValidate($data, $attr, $validator, $copy);

                if ($result === false) {
                    $this->_errors[] = [
                        $attr => $this->getMessage($validator, ['{attr}' => $attr], $rule, $message)
                    ];
                } else {
                    $this->_safeData[$attr] = $data[$attr];
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

        if ($cb = $this->_beforeHandler) {
            $cb($this);
        }

        // fix : deny repeat validate
        $this->_validated = true;

        return $this;
    }

    /**
     * do Validate
     * @param array $data 待验证的数据列表
     * @param string $attr 属性名称
     * @param \Closure|string $validator 验证器
     * @param array $args 验证需要的参数
     * @return array
     */
    protected function doValidate($data, $attr, $validator, $args)
    {
        // if attr don't exists.
        if (!$this->has($attr)) {
            return [false, $validator instanceof \Closure ? 'callback' : $validator];
        }

        if ($validator === 'required') {
            $result = ValidatorList::required($data, $attr);

            return [$result, $validator];
        }

        // 压入当前属性值 e.g. ValidatorList::range($data[$attr], $min , $max)
        array_unshift($args, $data[$attr]);

        // if $validator is a closure
        if ($validator instanceof \Closure) {
            $callback = $validator;
            $validator = 'callback';
            $args[] = $data;

        } elseif (is_string($validator)) {

            // if $validator is a custom add callback in the property {@see $_validators}.
            if (isset($this->_validators[$validator])) {
                $callback = $this->_validators[$validator];

                // if $validator is a custom method of the subclass.
            } elseif (method_exists($this, $validator)) {

                $callback = [$this, $validator];

                // $validator is a method of the class 'ValidatorList'
            } elseif (is_callable([ValidatorList::class, $validator])) {

                $callback = [ValidatorList::class, $validator];
            } else {
                throw new \InvalidArgumentException("The validator [$validator] don't exists!");
            }
        } else {
            throw new \InvalidArgumentException('Validator type is error, must is String or Closure!');
        }

        $result = call_user_func_array($callback, $args);

        return [(bool)$result, $validator];
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
        return count($this->_errors) > 0;
    }

    /**
     * @return bool
     */
    public function isFail(): bool
    {
        return $this->hasError();
    }

    /**
     * @return bool
     */
    public function fail(): bool
    {
        return $this->hasError();
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
        'number' => '{attr} must be an integer greater than 0!',
        'bool' => '{attr} must be is boolean!',
        'float' => '{attr} must be is float!',
        'regexp' => '{attr} does not meet the conditions',
        'url' => '{attr} not is url address!',
        'email' => '{attr} not is email address!',
        'ip' => '{attr} not is ip address!',
        'required' => 'parameter {attr} is required!',
        'length' => '{attr} length must at rang {min} ~ {max}',
        'size' => '{attr} must be an integer and at rang {min} ~ {max}',
        'min' => '{attr} minimum boundary is {value}',
        'max' => '{attr} maximum boundary is {value}',
        'in' => '{attr} must in ({value})',
        'string' => '{attr} must be a string',
        'compare' => '{attr} must be equals to {attr0}',
        'isArray' => '{attr} must be an array',
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
     * @param  string $name 验证器名称
     * @param  array $params 待替换的参数
     * @param  array $rule
     * @param  string $msg 自定义提示消息
     * @return string
     */
    public function getMessage($name, array $params, array $rule = [], $msg = null)
    {
        if (!$msg) {
            $msgList = $this->getMessages();
            $msg = $msgList[$name] ?? $msgList['_'];
        }

        $params['{attr}'] = $this->getAttrTran($params['{attr}']);

        foreach ($rule as $key => $value) {
            $key = is_int($key) ? "attr$key" : $key;
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
//    public function set($key, $value)
//    {
//        $this->data[$key] = $value;
//
//        return $this;
//    }

    /**
     * Get data item for key
     * @param string $key The data key
     * @param mixed $default The default value to return if data key does not exist
     * @return mixed The key's value, or the default value
     */
    public function get(string $key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
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

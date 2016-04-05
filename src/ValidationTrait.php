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
 * @property array $data
 */
trait ValidationTrait
{
    /**
     * 当前验证的场景 -- 如果需要让一个验证器在多个类似情形下使用
     * (在MVC框架中，通常是根据 controller 的 action name 来区分。 e.g. add, edit, register)
     * @var string
     */
    protected $scene = '';

////////////////////////////////////////// validate data //////////////////////////////////////////

    /**
     * 保存所有的验证错误信息
     * @var array[]
     * $_errors = [
     *     [ field => errorMessage1 ],
     *     [ field => errorMessage2 ],
     *     [ field2 => errorMessage3 ]
     * ]
     */
    private $_errors   = [];

    /**
     * 出现一个错误即停止验证
     * 默认 false 即是 全部验证并将错误信息保存到 {@see $_errors}
     * @var boolean
     */
    private $_hasErrorStop   = false;

    /**
     * @var array
     */
    private $_rules   = [];

    /**
     * @var array
     */
    private $_validators   = [];

    /**
     * attribute field translate list
     * @var array
     */
    private $_attrTrans = [];

    /**
     * Through the validation of the data
     * @var array
     */
    private $_safeData = [];

    /**
     * @var bool
     */
    private $_hasValidated = false;

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
                [ 'tagId', 'size', 'min'=>4, 'max'=>567, 'scene' => 'add' ],

                // use callback and custom error message
                [ 'userId', function($value){ echo "$value ttg tg tt";}, 'msg' => '{attr} is required!'],
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

    public function beforeValidate(){}

    /**
     * [ValidatorList::required] 验证是必定被调用的
     * @author inhere
     * @date   2015-08-11
     * @param array $onlyChecked 只检查一部分属性
     * @param  boolean $hasErrorStop 出现错误即停止验证
     * @return static
     * @throws \RuntimeException
     */
    public function validate(array $onlyChecked = [], $hasErrorStop=false)
    {
        if ( !property_exists($this, 'data') ) {
            throw new \InvalidArgumentException('Must be defined property \'data (array)\' in the classes used.');
        }

        if ( $this->_hasValidated || !($data = $this->data) ) {
            return $this;
        }

        $this->clearErrors()->beforeValidate();
        $this->hasErrorStop($hasErrorStop);

        // 循环规则
        foreach ($this->collectRules() as $rule) {
            // 要检查的属性(字段)名称
            $names = array_shift($rule);
            $names = is_string($names) ? array_filter(explode(',', $names),'trim') : (array)$names;

            // 要使用的验证器
            $validator = array_shift($rule);

            // 错误提示消息
            $message   = isset($rule['msg']) ? $rule['msg'] : null;
            unset($rule['msg']);

            // 验证的前置条件
            if ( isset($rule['when']) && ( $when = $rule['when'] ) && $when instanceof \Closure ) {
                unset($rule['when']);

                // 检查失败 -- 跳过此条规则
                if ( $when($data, $this) !== true ) {
                    continue;
                }
            }

            // 验证设置, 有一些验证器需要设置参数。 e.g. size()
            $copy = $rule;

            // 循环检查属性
            foreach ($names as $name) {
                if ( $onlyChecked && !in_array($name, $onlyChecked)) {
                     continue;
                }

                list($result,$validator) = $this->doValidate($data, $name, $validator, $copy);

                if ($result === false) {
                    $this->_errors[] = [
                        $name => $this->getMessage($validator, ['{attr}' => $name], $rule, $message)
                    ];
                } else {
                    $this->_safeData[$name] = $data[$name];
                }
            }

            $message = null;

            // There is an error an immediate end to verify
            if ( $this->hasError() && $this->_hasErrorStop ) {
                break;
            }
        }

        // fix: has error, clear safe data.
        if ( $this->hasError() ) {
            $this->_safeData = [];
        }

        $this->afterValidate();

        // fix : deny repeat validate
        $this->_hasValidated = true;

        return $this;
    }

    /**
     * do Validate
     * @param $data
     * @param $name
     * @param $validator
     * @param $copy
     * @return array
     */
    protected function doValidate($data, $name, $validator, $copy)
    {
        $result = ValidatorList::required($data, $name);

        if ($result && $validator !== 'required') {
            array_unshift($copy, $data[$name]);// 压入当前属性值

            // if it's a closure
            if ( is_callable($validator) && $validator instanceof \Closure) {
                $callback  = $validator;
                $validator = 'callback';

            // if it is a custom add callback in the property {@see $_validators}.
            } elseif ( is_string($validator) && isset($this->_validators['validator']) ) {

                $callback = $this->_validators['validator'];

            // if it is a custom method of the subclass.
            } elseif ( is_string($validator) && method_exists($this, $validator) ) {

                $callback = [ $this, $validator ];

            // it's a method of the class 'ValidatorList'
            } elseif ( is_string($validator) && is_callable([ValidatorList::class, $validator]) ) {

                $callback = [ ValidatorList::class, $validator];
            } else {
                throw new \InvalidArgumentException("validator [$validator] don't exists!");
            }

            $result = call_user_func_array($callback, $copy);
        }

        return [$result,$validator];
    }

    public function afterValidate(){}

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
    public function addValidator($name, \Closure $callback, $msg = '')
    {
        $this->_validators[$name] = $callback;

        if ($msg) {
            $this->defaultMessages[$name] = $msg;
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function collectRules()
    {
        $availableRules = [];
        $scene = $this->scene;

        // 循环规则, 搜集当前场景的规则
        foreach ($this->getRules() as $rule) {
            if ( empty($rule['scene']) ) {
                $availableRules[] = $rule;
            } else {
                if ( $rule['scene'] == $scene ) {
                    unset($rule['scene']);
                    $availableRules[] = $rule;
                }
            }
        }

        return $availableRules;
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
     * @param bool $val
     */
    public function hasErrorStop($val)
    {
        $this->_hasErrorStop = (bool)$val;
    }

    /**
     * 是否有错误
     * @date   2015-09-27
     * @return boolean
     */
    public function hasError()
    {
        return count($this->_errors) > 0;
    }
    public function fail()
    {
        return $this->hasError();
    }

    /**
     * @param $attr
     * @param $msg
     * @return mixed
     */
    public function addError($attr, $msg)
    {
        $this->_errors[] = [$attr, $msg];
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * 得到第一个错误信息
     * @author inhere
     * @date   2015-09-27
     * @param bool $onlyMsg
     * @return array|string
     */
    public function firstError($onlyMsg=true)
    {
        $e =  $this->_errors;
        $first = array_shift($e);

        return $onlyMsg ? array_values($first)[0] : $first;
    }

    /**
     * 得到最后一个错误信息
     * @author inhere
     * @date   2015-09-27
     * @param bool $onlyMsg
     * @return array|string
     */
    public function lastError($onlyMsg=true)
    {
        $e =  $this->_errors;
        $last = array_pop($e);

        return $onlyMsg ? array_values($last)[0] : $last;
    }

    /**
     * (过滤器)默认的错误提示信息
     * @return array
     */
    public $defaultMessages = [
        'int'    => '{attr} must be an integer!',
        'number' => '{attr} must be an integer greater than 0!',
        'bool'   => '{attr} must be is boolean!',
        'float'  => '{attr} must be is float!',
        'regexp' => '{attr} does not meet the conditions',
        'url'    => '{attr} not is url address!',
        'email'  => '{attr} not is email address!',
        'ip'     => '{attr} not is ip address!',
        'required' => '{attr} is not block!',
        'length' => '{attr} length must at rang {min} ~ {max}',
        'size'  => '{attr} must be an integer and at rang {min} ~ {max}',
        'min'   => '{attr} minimum boundary is {value}',
        'max'   => '{attr} maximum boundary is {value}',
        'in'    => '{attr} must in ({value})',
        'string' => '{attr} must be a string',
        'isArray' => '{attr} must be an array',
        'callback' => 'The custom callback validation fails of the [{attr}]!',
        '_'      => '{attr} validation is not through!',
    ];

    public function getMessages()
    {
        return array_merge($this->defaultMessages, $this->messages());
    }

    /**
     * 各个验证器的提示消息
     * @author inhere
     * @date   2015-09-27
     * @param  string $name 验证器名称
     * @param  array $params 待替换的参数
     * @param array $rule
     * @param  string $msg 提示消息
     * @return string
     */
    public function getMessage($name, array $params, $rule = [], $msg=null)
    {
        if ( !$msg ) {
            $msgs = $this->getMessages();
            $msg = isset($msgs[$name]) ? $msgs[$name]: $msgs['_'];
        }

        $trans = $this->getAttrTrans();
        $attrName = $params['{attr}'];
        $params['{attr}'] = isset($trans[$attrName]) ? $trans[$attrName] : $attrName;

        foreach ($rule as $key => $value) {
            $params['{' . $key . '}'] = is_array($value) ? implode(',', $value) : $value;
        }

        return strtr($msg, $params);
    }

//////////////////////////////////// getter/setter ////////////////////////////////////

    /**
     * @return array
     */
    public function getAttrTrans()
    {
        return array_merge($this->attrTrans(), $this->_attrTrans);
    }

    /**
     * @param array $attrTrans
     * @return $this
     */
    public function setAttrTrans(array $attrTrans)
    {
        $this->_attrTrans = array_merge($this->_attrTrans, $attrTrans);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasRule()
    {
        return $this->getRules() ? true : false;
    }

    /**
     * @return array
     */
    public function getRules()
    {
        if ( !$this->_rules ) {
            $this->_rules = $this->rules();
        }

        return $this->_rules;
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
     * @return string
     */
    public function getScene()
    {
        return $this->scene;
    }

    /**
     * @param string $scene
     * @return static
     */
    public function setScene($scene)
    {
        $this->scene = $scene;

        return $this;
    }

    /**
     * Get all items in collection
     *
     * @return array The collection's source data
     */
    public function all()
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
    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Set data item
     *
     * @param string $key The data key
     * @param mixed $value The data value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Get data item for key
     *
     * @param string $key     The data key
     * @param mixed  $default The default value to return if data key does not exist
     *
     * @return mixed The key's value, or the default value
     */
    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    /**
     * get safe attribute
     * @param $key
     * @param null $default
     * @return null
     */
    public function getSafe($key, $default = null)
    {
        return $this->getValid($key, $default);
    }
    public function getValid($key, $default = null)
    {
        return array_key_exists($key, $this->_safeData) ? $this->_safeData[$key] : $default;
    }

    /**
     * @return array
     */
    public function getSafeData()
    {
        return $this->_safeData;
    }
}

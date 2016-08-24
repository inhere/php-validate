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
 * @property array $data 待验证的数据列表
 */
trait ValidationTrait
{
    /**
     * 当前验证的场景 -- 如果需要让一个验证器在多个类似情形下使用
     * (在MVC框架中，通常可以根据 controller 的 action name 来区分。 e.g. add, edit, register)
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

    private $_position = [
        'attr'      => 0,
        'validator' => 1,
    ];

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
            throw new \InvalidArgumentException('Must be defined property \'data (array)\' in the class used.');
        }

        if ( $this->_hasValidated ) {
            return $this;
        }

        $this->clearErrors()->beforeValidate();
        $this->hasErrorStop($hasErrorStop);

        $data = $this->data;

        // 循环规则
        foreach ($this->collectRules() as $rule) {
            // 要检查的属性(字段)名称集
            $attrs = array_shift($rule);
            $attrs = is_string($attrs) ? array_filter(explode(',', $attrs),'trim') : (array)$attrs;

            // 要使用的验证器(a string or a Closure)
            $validator = array_shift($rule);

            // 为空时是否跳过(非 required 时). 参考自 yii2
            $skipOnEmpty   = isset($rule['skipOnEmpty']) ? $rule['skipOnEmpty'] : true;

            // 如何判断属性为空 默认使用 empty($data[$attr]). 也可自定义
            if ( isset($rule['isEmpty']) && $rule['isEmpty'] instanceof \Closure ) {
                $isEmpty   = $rule['isEmpty'];
            } else {
                $isEmpty = [ ValidatorList::class, 'isEmpty'];
            }

            // 自定义当前验证的错误提示消息
            $message   = isset($rule['msg']) ? $rule['msg'] : null;

            // 验证的前置条件
            $when   = isset($rule['when']) ? $rule['when'] : null;
            if ( $when && $when instanceof \Closure ) {

                // 检查失败 -- 跳过此条规则
                if ( $when($data, $this) !== true ) {
                    continue;
                }
            }

            // clear some fields
            unset($rule['msg'], $rule['skipOnEmpty'],$rule['isEmpty'],$rule['when']);

            // 验证设置, 有一些验证器需要设置参数。 e.g. size()
            $copy = $rule;

            // 循环检查属性
            foreach ($attrs as $attr) {
                // 不在需要检查的列表内 ||  $skipOnEmpty is true && ValidatorList::isEmpty($data,$attr)
                if (
                    ($onlyChecked && !in_array($attr, $onlyChecked)) ||
                    ( $validator !== 'required' && $skipOnEmpty && call_user_func($isEmpty, $data, $attr))
                ) {
                     continue;
                }

                list($result,$validator) = $this->doValidate($data, $attr, $validator, $copy);

                if ($result === false) {
                    $this->_errors[] = [
                        $attr => $this->getMessage($validator, ['{attr}' => $attr], $rule, $message)
                    ];
                } else {
                    $this->_safeData[$attr] = $data[$attr];
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
     * @param array $data 待验证的数据列表
     * @param string $attr 属性名称
     * @param mixed $validator 验证器
     * @param array $args 验证需要的参数
     * @return array
     */
    protected function doValidate($data, $attr, $validator, $args)
    {
        // if attr don't exists.
        if ( !$this->has($attr) ) {
            return [false, $validator instanceof \Closure ? 'callback' : $validator];
        }

        if ( $validator === 'required' ) {
            $result = ValidatorList::required($data, $attr);

            return [$result,$validator];
        }

        // 压入当前属性值 e.g. ValidatorList::range($data[$attr], $min , $max)
        array_unshift($args, $data[$attr]);

        // if $validator is a closure
        if ( is_callable($validator) && $validator instanceof \Closure) {
            $callback  = $validator;
            $validator = 'callback';
            $args[] = $data;

        } elseif ( is_string($validator) ) {
            
            // if $validator is a custom add callback in the property {@see $_validators}.
            if ( isset($this->_validators['validator']) ) {
                $callback = $this->_validators['validator'];
                
            // if $validator is a custom method of the subclass.
            } elseif ( is_string($validator) && method_exists($this, $validator) ) {
    
                $callback = [ $this, $validator ];
    
            // $validator is a method of the class 'ValidatorList'
            } elseif ( is_string($validator) && is_callable([ValidatorList::class, $validator]) ) {
    
                $callback = [ ValidatorList::class, $validator];
            } else {
                throw new \InvalidArgumentException("validator [$validator] don't exists!");
            }
        } else {
            throw new \InvalidArgumentException("validator format is error, must is String or Closure !");
        }

        $result = call_user_func_array($callback, $args);

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
            self::$_defaultMessages[$name] = $msg;
        }

        return $this;
    }

    /**
     * 收集当前场景可用的规则列表
     * @return array
     */
    protected function collectRules()
    {
        $availableRules = [];
        $scene = $this->scene;

        // 循环规则, 搜集当前场景的规则
        foreach ($this->getRules() as $rule) {

            // check validator
            if ( !is_string($rule[1]) && !($rule[1] instanceof \Closure) ) {
                throw new \InvalidArgumentException("validator setting error!");
            }

            if ( empty($rule['scene']) ) {
                $availableRules[] = $rule;
            } else {
                $ruleScene = $rule['scene'];
                $ruleScene = is_string($ruleScene) ? array_filter(explode(',', $ruleScene),'trim') : (array)$ruleScene;

                if ( in_array($scene,$ruleScene) ) {
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
    private static $_defaultMessages = [
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
        return array_merge(self::$_defaultMessages, $this->messages());
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
            $msgList = $this->getMessages();
            $msg = isset($msgList[$name]) ? $msgList[$name]: $msgList['_'];
        }

        $params['{attr}'] = $this->getAttrTran($params['{attr}']);

        foreach ($rule as $key => $value) {
            $params['{' . $key . '}'] = is_array($value) ? implode(',', $value) : $value;
        }

        return strtr($msg, $params);
    }

//////////////////////////////////// getter/setter ////////////////////////////////////

    /**
     * @param string $attr
     * @return string
     */
    public function getAttrTran($attr)
    {
        $trans = $this->getAttrTrans();

        return isset($trans[$attr]) ? $trans[$attr] : StrHelper::toUnderscoreCase($attr, ' ');
    }

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

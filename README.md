# php validate

[![License](https://img.shields.io/packagist/l/inhere/php-validate.svg?style=flat-square)](LICENSE)
[![Php Version](https://img.shields.io/badge/php-%3E=7.0-brightgreen.svg?maxAge=2592000)](https://packagist.org/packages/inhere/php-validate)
[![Latest Stable Version](http://img.shields.io/packagist/v/inhere/php-validate.svg)](https://packagist.org/packages/inhere/php-validate)
[![git branch](https://img.shields.io/badge/branch-php5-yellow.svg)](https://github.com/inhere/php-validate)

一个简洁小巧且功能完善的php验证、过滤库。仅有几个文件，无依赖。

- 简单方便，支持添加自定义验证器
- 支持前置验证检查, 自定义如何判断非空
- 支持将规则按场景进行分组设置。或者部分验证
- 支持在进行验证前对值使用过滤器进行净化过滤[内置过滤器](#built-in-filters)
- 支持自定义每个验证的错误消息，字段翻译，消息翻译，支持默认值
- 支持基本的数组检查，数组的子级值检查
- 方便的获取错误信息，验证后的安全数据获取
- 已经内置了40多个常用的验证器[内置验证器](#built-in-validators)
- 规则设置参考自 yii 的。部分规则参考自 laravel
- `RuleValidation` 规则配置类似于Yii: 每条规则中，允许多个字段，但只能有一个验证器。
  - e.g `['tagId,userId,name,email,freeTime', 'required', ...]`(下面的示例都是这种)
- `FieldValidation` 规则配置类似于Laravel: 每条规则中，只能有一个字段，但允许多个验证器。
  - e.g `['field', 'required|string:5,10|...', ...]`

> 新增了独立的过滤器 `Inhere\Validate\Filter\Filtration` 用于数据过滤

## 项目地址

- **git@osc** https://gitee.com/inhere/php-validate.git
- **github** https://github.com/inhere/php-validate.git

**注意：**

- master 分支是要求 `php >= 7` 的(推荐使用)。
- php5 分支是支持 php 5 的代码分支 

## 安装

- 使用 composer

编辑 `composer.json`，在 `require` 添加

```
"inhere/php-validate": "dev-master",
// "inhere/php-validate": "dev-php5", // for php5
```

然后执行: `composer update`

- 直接拉取

```
git clone https://git.oschina.net/inhere/php-validate.git // git@osc
git clone https://github.com/inhere/php-validate.git // github
```

## 使用

<a name="how-to-use"></a>
### 方式 1: 创建一个新的class，并继承Validation

创建一个新的class，并继承 `Inhere\Validate\Validation`。用于一个（或一系列相关）请求的验证, 相当于 laravel 的 表单请求验证

> 此方式是最为完整的使用方式

```php

use Inhere\Validate\Validation;

class PageRequest extends Validation
{
    public function rules()
    {
        return [
            ['tagId,title,userId,freeTime', 'required'],
            ['tagId', 'size', 'min'=>4, 'max'=>567], // 4<= tagId <=567
            ['title', 'min', 40],
            ['freeTime', 'number'],
            ['tagId', 'number', 'when' => function($data) {
                return isset($data['status']) && $data['status'] > 2;
            }],
            ['userId', 'number', 'on' => 'scene1' ],
            ['username', 'string', 'on' => 'scene2' ],
            ['username', 'regexp' ,'/^[a-z]\w{2,12}$/'],
            ['title', 'customValidator', 'msg' => '{attr} error msg!' ], // 指定当前规则的消息
            ['status', function($status) {
                if (is_int($status) && $status > 3) {
                    return true;
                }
                return false;
            }],
            ['createdAt, updatedAt', 'safe'], // 标记字段是安全可靠的。
        ];
    }
    
    // 添加一个验证器。必须返回一个布尔值标明验证失败或成功
    protected function customValidator($title)
    {
        // some logic ...

        return true; // Or false;
    }

    // 定义字段翻译
    public function translates()
    {
        return [
          'userId' => '用户Id',
        ];
    }

    // 自定义验证器的提示消息, 更多请看 {@see ValidationTrait::$messages}
    public function messages()
    {
        return [
          'required' => '{attr} 是必填项。',
          // 可以直接针对字段的某个规则进行消息定义
          'title.required' => 'O, 标题是必填项。are you known?',
        ];
    }
}
```

使用

```php
// 验证 POST 数据
$v = PageRequest::make($_POST)->validate();

// 验证失败
if ($v->fail()) {
    var_dump($v->getErrors());
    var_dump($v->firstError());
}

// 验证成功 ...
$safeData = $v->getSafeData(); // 验证通过的安全数据
// $postData = $v->all(); // 原始数据

$db->save($safeData);
```

### 方式 2: 直接使用类 Validation

需要快速简便的使用验证时，可直接使用 `Inhere\Validate\Validation`

```php
use Inhere\Validate\Validation;

class SomeController
{
    public function demoAction()
    {
        $v = Validation::make($_POST,[
            // add rule
            ['title', 'min', 40],
            ['freeTime', 'number'],
        ])->validate();

        if ($v->fail()) {
            var_dump($v->getErrors());
            var_dump($v->firstError());
        }

        // $postData = $v->all(); // 原始数据
        $safeData = $v->getSafeData(); // 验证通过的安全数据

        $db->save($safeData);
    }
}
```

### 方式 1: 创建一个新的class，使用  ValidationTrait

创建一个新的class，并使用 Trait `Inhere\Validate\ValidationTrait`。 此方式是高级自定义的使用方式, 可以方便的嵌入到其他类中

如下， 嵌入到一个数据模型类中, 实现一个简单的模型基类，添加数据库记录前自动进行验证

```php
class DataModel
{
    use \Inhere\Validate\ValidationTrait;

    protected $data = [];

    protected $db;

    /**
     * @param array $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function create()
    {
        if ($this->validate()->fail()) {
            return false;
        }

        return $this->db->insert($this->getSafeData());
    }
}
```

使用：

```php
// on model class
class UserModel extends DataModel
{
    public function rules()
    {
        return [
            ['username, passwd', 'required', 'on' => 'create' ],
            ['passwd', 'compare', 'repasswd', 'on' => 'create']
            ['username', 'string', 'min' => 2, 'max' => 20, 'on' => 'create' ],
            ['id', 'number', 'on' => 'update' ],
            ['createdAt, updatedAt', 'safe'],
        ];
    }
}

// on controller ...
class UserController 
{
    // in action
    // @api /user/add
    public function addAction()
    {
        $model = new UserModel;
        $ret = $model->setData($_POST)->atScene('create')->create();

        if (!$ret) {
            exit($model->firstError());
        }

        echo "add success: userId = $ret";
    }

}
```


## 添加自定义验证器

- 在继承了 `Inhere\Validate\Validation` 的子类添加验证方法. 请看上面的 **使用方式1**
- 通过 `Validation::addValidator()` 添加自定义验证器. e.g:

```php

$v = Validation::make($_POST,[
        // add rule
        ['title', 'min', 40],
        ['freeTime', 'number'],
        ['title', 'checkTitle'],
    ])
    ->addValidator('checkTitle',function($title){
        // some logic ...

        return true; // 成功返回 True。 如果验证失败,返回 False.
    }, '{attr} default message!')
    ->validate();
```

- 直接写闭包进行验证 e.g:

```php
    ['status', function($status) {
        if ($status > 3) {
            return true;
        }
        
        return false;
    }]
```

## 规则关键词

除了可以添加字段的验证之外，还有一些特殊关键词可以设置使用，以适应各种需求。

### `default` -- 设置字段的默认值

给一个或多个字段设置一个默认值。

```php
['page', 'number', 'default' => 1],
['pageSize', 'number', 'default' => 15],
```

> NOTICE: 默认值也会被验证器验证

### `msg` -- 设置错误提示消息

设置当前规则的错误提示消息, 设置了后就不会在使用默认的提示消息。

```php
['title', 'customValidator', 'msg' => '{attr} error msg!' ], // 指定当前规则的消息
// o, 可以是数组哦 :)
['tagId,title,userId,freeTime', 'required', 'msg' => [
  'tagId' => 'message ...',
  'userId' => 'message 1 ...',
]],
```

### `on` -- 设置规则使用场景

> 如果需要让定义的规则在多个类似情形下重复使用，可以设置规则的使用场景。在验证时也表明要验证的场景

```php
    // 在继承了 Validation 的子类 ValidationClass 中 ...
    public function rules()
    {
         return [
            ['title', 'required' ],
            ['userId', 'number', 'on' => 'create' ],
            ['userId', 'int', 'on' => 'update' ],
            ['name', 'string', 'on' => 'create,update' ],
        ];
    }
```

使用:

如，在下面指定了验证场景时，将会使用上面的第 1,3,4 条规则. (第 1 条没有限制规则使用场景的，在所有场景都可用)

```php
    // ...
    $valid = ValidationClass::make($_POST)->atScene('update')->validate();
    // ...

```

### `when` -- 规则的前置条件

> 只有在先满足了(`when`)前置条件时才会验证这条规则

如在下面的例子中，检查到第二条规则时，会先执行闭包(`when`)，
当其返回 `true` 验证此条规则，否则不会验证此条规则

```php
    // 在继承了 Validation 的子类中 ...
    public function rules()
    {
         return [
            ['title', 'required' ],
            ['tagId', 'number', 'when' => function($data)
            {
               return isset($data['status']) && $data['status'] > 2;
            }],
        ];
    }
```

### `skipOnEmpty` -- 为空是否跳过验证

当字段值为空时是否跳过验证,默认值是 `true`. (参考自 yii2)

> 'required*' 规则不在此限制内.

如,有一条规则:

```php
['name', 'string']
```

提交的数据中 没有 `name` 字段或者 `$data['name']` 等于空都不会进行 `string` 验证;
只有当 `$data['name']` **有值且不为空** 时才会验证是否是 `string`

如果要想为空时也检查, 请将此字段同时加入 `required` 规则中. 

```php
['name', 'required' ]
['name', 'string' ]
```

或者也可以设置 `'skipOnEmpty' => false`:

```php
['name', 'string', 'skipOnEmpty' => false ]
```

### `isEmpty` -- 是否为空判断

是否为空判断, 这个判断作为 `skipOnEmpty` 的依据. 默认使用 `ValidatorList::isEmpty` 来判断.

你也可以自定义判断规则:

```php
['name', 'string', 'isEmpty' => function($value) {
    return true or false;
 }]
```

### `filter` -- 使用过滤器

支持在进行验证前对值使用过滤器进行净化过滤[内置过滤器](#built-in-filters)

> 允许同时使用多个过滤器。字符串使用 `|` 分隔，或者配置为数组。

```php
['tagId,userId,freeTime', 'number', 'filter' => 'int'],
['field', 'validator', 'filter' => 'filter0|filter1...'],
```

> 过滤器请参看 http://php.net/manual/zh/filter.filters.sanitize.php

### 一个完整的规则示例

一个完整的规则示例, 包含了所有可添加的项。

```php
// a full rule: 
[
 // validate setting
 'field0,field1,...', 'validator', 'arg0', 'arg1', ..., 

 // some extended option settings
 'skipOnEmpty' => 'bool', 
 'msg' => 'string|array', 
 'default' => 'mixed', 
 'on' => 'string|array' 
 'isEmpty' => 'callback(string|closure)', 
 'when' => 'callback(string|closure)', 
 'filter' => 'callback(string|array|closure)'
]
```

## 一些关键方法使用说明

### 设置验证场景

```php
public function setScene(string $scene)
public function atScene(string $scene) // setScene 的别名方法
```

设置当前验证的场景名称。将只会使用符合当前场景的规则对数据进行验证

### 进行数据验证

```php
public function validate(array $onlyChecked = [], $stopOnError = null)
```

进行数据验证。 返回验证器对象，然后就可以获取验证结果等信息。

- `$onlyChecked` 可以设置此次需要验证的字段
- `$stopOnError` 是否当出现一个验证失败就立即停止。 默认是 `true`

### 添加自定义的验证器

```php
public function addValidator(string $name, \Closure $callback, string $msg = '')
```

添加自定义的验证器。 返回验证器对象以支持链式调用

- `$name` 自定义验证器名称
- `$callback` 自定义验证器。处理验证，为了简洁只允许闭包。
- `$msg` 可选的。 当前验证器的错误消息

### 判断验证是否通过

```
// 验证失败
public function hasError()
public function isFail() // hasError() 的别名方法
public function fail() // hasError() 的别名方法

// 成功通过验证
public function passed() 
public function isPassed() // passed() 的别名方法
```

获取验证是否通过(是否有验证失败)。

### 获取所有错误信息

```php
public function getErrors(): array
```

获取所有的错误信息, 包含所有错误的字段和错误信息的多维数组。 eg:

```php 
[
    [ attr1 => 'error message 1'],
    [ attr1 => 'error message 2'],
    [ attr2 => 'error message 3'],
]
```

> 同一个属性/字段也可能有多个错误消息，当为它添加了多个验证规则时。

### 得到第一个错误信息

```php
public function firstError($onlyMsg = true)
```

- `$onlyMsg` 是否只返回消息字符串。当为 false，返回的则是数组 eg: `[ attr => 'error message']`

### 得到最后一个错误信息

```php
public function lastError($onlyMsg = true)
```

- `$onlyMsg` 是否只返回消息字符串。当为 false，返回的则是数组 eg: `[ attr => 'error message']`

### 获取所有验证通过的数据

```php
public function getSafeData(): array
```

获取所有 **验证通过** 的安全数据. 

- 此数据数组只包含加入了规则验证的字段数据，不会含有额外的字段。(可直接省去后续的字段收集)
- 推荐使用此数据进行后续操作，比如存入数据库等。

> 注意： 当有验证失败出现时，安全数据 `safeData` 将会被重置为空。 即只有全部通过验证，才能获取到 `safeData`

### 根据字段名获取安全值

```php
public function getSafe(string $key, $default = null)
public function getValid(string $key, $default = null) // getSafe() 的别名方法
```

从 **验证通过** 的数据中取出对应 key 的值

### 获取所有原始数据

```php
public function all(): array
```

获取验证时传入的所有数据

### 根据字段名获取原始数据的值

```php
public function get(string $key, $default = null)
```

从验证时传入的数据中取出对应 key 的值

<a name="built-in-filters"></a>
## 内置的过滤器

过滤器 | 说明 | 示例
-------|-------------|------------
`int/integer` | 过滤非法字符并转换为`int`类型 | `['userId', 'number', 'filter' => 'int'],`
`float` | 过滤非法字符,保留`float`格式的数据 | `['price', 'float', 'filter' => 'float'],`
`string` | 过滤非法字符并转换为`string`类型 | `['userId', 'number', 'filter' => 'string'],`
`trim` | 去除首尾空白字符，支持数组。 | `['username', 'min', 4, 'filter' => 'trim'],`
`lowercase` | 字符串转换为小写 | `['description', 'min', 4, 'filter' => 'lowercase'],`
`uppercase` | 字符串转换为大写 | `['title', 'min', 4, 'filter' => 'uppercase'],`
`timestamp/strToTime` | 字符串日期转换时间戳 | `['pulishedAt', 'number', 'filter' => 'strToTime'],`
`abs` | 返回绝对值 | `['field', 'int', 'filter' => 'abs'],`
`url` | URL 过滤,移除所有不符合 URL 的字符 | `['field', 'url', 'filter' => 'url'],`
`email` | email 过滤,移除所有不符合 email 的字符 | `['field', 'email', 'filter' => 'email'],`
`encoded` | 去除 URL 编码不需要的字符,与 `urlencode()` 函数很类似 | `['imgUrl', 'url', 'filter' => 'encoded'],`
`specialChars` | 相当于使用 `htmlspecialchars()` 转义数据 | `['content', 'string', 'filter' => 'specialChars'],`
`quotes` | 应用 `addslashes()` 转义数据 | `['content', 'string', 'filter' => 'quotes'],`

<a name="built-in-validators"></a>
## 内置的验证器

> `/` 分隔的验证器，表明功能是一样的，只是有不同的别名

验证器 | 说明 | 规则示例
----------|-------------|------------
`int/integer`   | 验证是否是 int | `['userId', 'int']`
`num/number`    | 验证是否是 number | `['userId', 'number']`
`bool/boolean`  | 验证是否是 bool | `['open', 'bool']`
`float` | 验证是否是 float | `['price', 'float']`
`string`    | 验证是否是 string. 支持长度检查 | `['name', 'string']`, `['name', 'string', 'min'=>4, 'max'=>16]`
`url`   | 验证是否是 url | `['myUrl', 'url']`
`email` | 验证是否是 email | `['userEmail', 'email']`
`alpha`   | 验证值是否仅包含字母字符 | `['name', 'alpha']`
`alphaNum`   | 验证是否仅包含字母、数字 | `['field', 'alphaNum']`
`alphaDash`   | 验证是否仅包含字母、数字、破折号（ - ）以及下划线（ _ ） | `['field', 'alphaDash']`
`isMap`   | 验证值是否是一个非自然数组 map (key - value 形式的) | `['goods', 'isMap']`
`isList`   | 验证值是否是一个自然数组 list (key是从0自然增长的) | `['tags', 'isList']`
`isArray`   | 验证是否是数组 | `['goods', 'isArray']`
`intList`   | 验证字段值是否是一个 int list | `['tagIds', 'intList']`
`strList`   | 验证字段值是否是一个 string list | `['tags', 'strList']`
`min`   | 最小边界值验证 | `['title', 'min', 40]`
`max`   | 最大边界值验证 | `['title', 'max', 40]`
`size/range/between`  | 验证大小范围, 可以支持验证 `int`, `string`, `array` 数据类型 | `['tagId', 'size', 'min'=>4, 'max'=>567]`
`length`    | 长度验证（ 跟 `size`差不多, 但只能验证 `string`, `array` 的长度 | `['username', 'length', 'min' => 5, 'max' => 20]`
`in/enum`    | 枚举验证 | `['status', 'in', [1,2,3]`
`notIn`    | 枚举验证 | `['status', 'notIn', [4,5,6]]`
`mustBe`   | 必须是等于给定值 | `['status', 'mustBe', 1]`
`notBe`   | 不能等于给定值 | `['status', 'notBe', 0]`
`compare/same/equal` | 字段值相同比较 | `['passwd', 'compare', 'repasswd']`
`notEqual` | 字段值不能相同比较 | `['passwd', 'notEqual', 'repasswd']`
`required`  | 要求此字段/属性是必须的 | `['tagId, userId', 'required' ]`
`requiredIf` | 指定的其它字段（ anotherField ）值等于任何一个 value 时，此字段为 **必填** | `['city', 'requiredIf', 'myCity', ['chengdu'] ]`
`requiredUnless` | 指定的其它字段（ anotherField ）值等于任何一个 value 时，此字段为 **不必填** | `['city', 'requiredUnless', 'myCity', ['chengdu'] ]`
`requiredWith` | 指定的字段中的 _任意一个_ 有值且不为空，则此字段为 **必填** | `['city', 'requiredWith', ['myCity'] ]`
`requiredWithAll` | 如果指定的 _所有字段_ 都有值，则此字段为 **必填** | `['city', 'requiredWithAll', ['myCity', 'myCity1'] ]`
`requiredWithout` | 如果缺少 _任意一个_ 指定的字段值，则此字段为 **必填** | `['city', 'requiredWithout', ['myCity', 'myCity1'] ]`
`requiredWithoutAll` | 如果所有指定的字段 都没有 值，则此字段为 **必填** | `['city', 'requiredWithoutAll', ['myCity', 'myCity1'] ]`
`date` | 验证是否是 date | `['publishedAt', 'date']`
`dateFormat` | 验证是否是 date, 并且是指定的格式 | `['publishedAt', 'dateFormat', 'Y-m-d']`
`dateEquals` | 验证是否是 date, 并且是否是等于给定日期 | `['publishedAt', 'dateEquals', '2017-05-12']`
`beforeDate` | 验证字段值必须是给定日期之前的值 | `['publishedAt', 'beforeDate', '2017-05-12']`
`beforeOrEqualDate` | 字段值必须是小于或等于给定日期的值 | `['publishedAt', 'beforeOrEqualDate', '2017-05-12']`
`afterOrEqualDate` | 字段值必须是大于或等于给定日期的值 | `['publishedAt', 'afterOrEqualDate', '2017-05-12']`
`afterDate` | 验证字段值必须是给定日期之前的值 | `['publishedAt', 'afterDate', '2017-05-12']`
`json`   | 验证是否是json字符串 | `['goods', 'json']`
`file`   | 验证是否是上传的文件 | `['upFile', 'file']`
`image`   | 验证是否是上传的图片文件 | `['avatar', 'image']`, 限定后缀名 `['avatar', 'image', 'jpg,png']`
`ip`    | 验证是否是 IP | `['ipAddr', 'ip']`
`ipv4`    | 验证是否是 IPv4 | `['ipAddr', 'ipv4']`
`ipv6`    | 验证是否是 IPv6 | `['ipAddr', 'ipv6']`
`md5`    | 验证是否是 md5 格式的字符串 | `['passwd', 'md5']`
`sha1`    | 验证是否是 sha1 格式的字符串 | `['passwd', 'sha1']`
`color`    | 验证是否是html color | `['backgroundColor', 'color']`
`regexp`    | 使用正则进行验证 | `['name', 'regexp', '/^\w+$/']`
`safe`    | 用于标记字段是安全的，无需验证 | `['createdAt, updatedAt', 'safe']`

### `safe` 验证器,标记属性/字段是安全的

特殊验证器 用于标记字段是安全的，无需验证，直接加入到安全数据中。

比如我们在写入数据库之前手动追加的字段: 创建时间，更新时间。

```php
['createdAt, updatedAt', 'safe']
```

### 关于文件验证

文件验证时注意要设置文件信息源数据

```php
$v = Validation::make($_POST, [
    // [...], 
    // some rules ...
])
->setUploadedFiles($_FILES)
->validate();

// ...
```

### 一些补充说明

- **请将 `required*` 系列规则写在规则列表的最前面**
- 关于布尔值验证
    * 如果是 "1"、"true"、"on" 和 "yes"，则返回 TRUE
    * 如果是 "0"、"false"、"off"、"no" 和 ""，则返回 FALSE
- 支持对数组的子级值验证 

```php
[
    'goods' => [
        'apple' => 34,
        'pear' => 50,
    ],
]
```

规则：

```php
    ['goods.pear', 'max', 30], //goods 下的 pear 值最大不能超过 30
```

- 验证大小范围 `int` 是比较大小。 `string` 和 `array` 是检查长度
- `required*` 系列规则参考自 laravel
- `size/range` `length` 可以只定义 min 最小值。 但是当定义了max 值时，必须同时定义最小值

## 代码示例

可运行示例请看 `examples` 

## 单元测试

```sh
./tests/test.sh
```

## License

MIT

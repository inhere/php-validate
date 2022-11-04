# PHP Validate

[![License](https://img.shields.io/packagist/l/inhere/php-validate.svg?style=flat-square)](LICENSE)
[![Php Version](https://img.shields.io/badge/php-%3E=8.1-brightgreen.svg?maxAge=2592000)](https://packagist.org/packages/inhere/php-validate)
[![Latest Stable Version](http://img.shields.io/packagist/v/inhere/php-validate.svg)](https://packagist.org/packages/inhere/php-validate)
[![Coverage Status](https://coveralls.io/repos/github/inhere/php-validate/badge.svg?branch=master)](https://coveralls.io/github/inhere/php-validate?branch=master)
[![Github Actions Status](https://github.com/inhere/php-validate/workflows/Unit-tests/badge.svg)](https://github.com/inhere/php-validate/actions)
[![English readme](https://img.shields.io/badge/English-Readme-brightgreen.svg?style=for-the-badge&maxAge=2592000)](README.en.md)

一个简洁小巧且功能完善的php验证、过滤库。

- 简单方便，支持添加自定义验证器
- 支持前置验证检查, 自定义如何判断非空
- 支持将规则按场景进行分组设置。或者部分验证
- 支持在进行验证前对值使用过滤器进行净化过滤[内置过滤器](#built-in-filters)
- 支持在进行验证前置处理和后置处理[独立验证处理](#on-in-Validation)
- 支持自定义每个验证的错误消息，字段翻译，消息翻译，支持默认值
- 支持基本的数组检查，数组的子级(`'goods.apple'`)值检查, 通配符的子级检查 (`'users.*.id' 'goods.*'`)
- 方便的获取错误信息，验证后的安全数据获取(只会收集有规则检查过的数据)
- 已经内置了大量的常用的验证器[内置验证器](#built-in-validators)
- 规则设置参考 `yii`, `laravel`, `Respect/Validation`
- 独立的过滤器 `Inhere\Validate\Filter\Filtration`，可单独用于数据过滤

## 两种规则配置方式

validate 同时支持两种规则配置方式，对应了两种规则的收集解析策略。

### 多字段单规则

- `Validation|RuleValidation`: **每条规则中，允许多个字段，但只能有一个验证器。** (_规则配置类似于Yii_)

配置示例: (_本文档的示例都是这种_)

```php
[
    ['tagId,userId,name,email,freeTime', 'required', ...],
    // ... ...
];
```

### 单字段多规则

- `FieldValidation`: **每条规则中，只能有一个字段，但允许多个验证器。** (_规则配置类似于Laravel_)
- `FieldValidation`: 不支持验证器`each`

配置示例:

```php
[
    ['field', 'required|string:5,10|...', ...],
    // ... ...
]
```

> 更多配置示例请参看 [像Laravel一样配置](https://github.com/inhere/php-validate/wiki/config-rules-like-laravel)

## 项目地址

- **github** <https://github.com/inhere/php-validate.git>
- **gitee** <https://gitee.com/inhere/php-validate.git>

## 安装

```bash
composer require inhere/php-validate
# or
# composer require inhere/php-validate ^3.0
```

### 注意

- `master` 分支是要求 `php8.1+` 的(推荐使用)
- `2.x` 分支是支持 `php7.1+`,但是基本上不再维护。
- `1.x` 分支是支持php5的代码分支,但是基本上不再维护。

## 立即使用

<a name="how-to-use1"></a>

### 方式1: 直接使用类 `Validation`

需要快速简便的使用验证时，可直接使用 `Inhere\Validate\Validation`

```php
use Inhere\Validate\Validation;

class SomeController
{
    public function demoAction()
    {
        $v = Validation::check($_POST,[
            // add rule
            ['title', 'min', 40],
            ['freeTime', 'number'],
        ]);

        if ($v->isFail()) {
            var_dump($v->getErrors());
            var_dump($v->firstError());
        }

        // $postData = $v->all(); // 原始数据
        $safeData = $v->getSafeData(); // 验证通过的安全数据

        $db->save($safeData);
    }
}
```

<a name="how-to-use2"></a>

### 方式2: 继承类 `Validation`

创建一个新的class，并继承 `Inhere\Validate\Validation`。用于一个（或一系列相关）请求的验证, 相当于 laravel 的 表单请求验证

> 此方式是最为完整的使用方式，可以配置规则，设置字段翻译，设置自定义的错误消息等

```php
use Inhere\Validate\Validation;

class PageRequest extends Validation
{
    # 进行验证前处理,返回false则停止验证,但没有错误信息,可以在逻辑中调用 addError 增加错误信息
    public function beforeValidate(): bool
    {
        return true;
    }
    # 进行验证后处理,该干啥干啥
    public function afterValidate(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            // 字段必须存在且不能为空
            ['tagId,title,userId,freeTime', 'required'],

            // 4<= tagId <=567
            ['tagId', 'size', 'min'=>4, 'max'=>567, 'filter' => 'int'],

            // title length >= 40. 注意只需一个参数的验证，无需加 key, 如这里的 40
            ['title', 'min', 40, 'filter' => 'trim'],

            // 大于 0
            ['freeTime', 'number'],

            // 含有前置条件
            ['tagId', 'number', 'when' => function($data) {
                return isset($data['status']) && $data['status'] > 2;
            }],

            // 在验证前会先过滤转换为 int。并且仅会在指明场景名为 'scene1' 时规则有效
            ['userId', 'number', 'on' => 'scene1', 'filter' => 'int'],
            ['username', 'string', 'on' => 'scene2', 'filter' => 'trim'],

            // 使用自定义正则表达式
            ['username', 'regexp' ,'/^[a-z]\w{2,12}$/'],

            // 自定义验证器，并指定当前规则的消息
            ['title', 'custom', 'msg' => '{attr} error msg!' ],

            // 直接使用闭包验证
            ['status', function($status) {
                if (is_int($status) && $status > 3) {
                    return true;
                }
                return false;
            }],

            // 标记字段是安全可靠的 无需验证
            ['createdAt, updatedAt', 'safe'],
        ];
    }

    // 定义不同场景需要验证的字段。
    // 功能跟规则里的 'on' 类似，两者尽量不要同时使用，以免混淆。
    public function scenarios(): array
    {
        return [
            'create' => ['user', 'pwd', 'code'],
            'update' => ['user', 'pwd'],
        ];
    }

    // 定义字段翻译
    public function translates(): array
    {
        return [
          'userId' => '用户Id',
        ];
    }

    // 自定义验证器的提示消息, 默认消息请看 {@see ErrorMessageTrait::$messages}
    public function messages(): array
    {
        return [
           // 使用验证器名字指定消息
          'required' => '{attr} 是必填项。',
          // 可以直接针对字段的某个规则进行消息定义
          'title.required' => 'O, 标题是必填项。are you known?',
        ];
    }

    // 添加一个验证器。必须返回一个布尔值标明验证失败或成功
    protected function customValidator($title): bool
    {
        // some logic ...
        // $this->getRaw('field'); 访问 data 数据

        return true; // Or false;
    }
}
```

- 使用

```php
// 验证 POST 数据
$v = PageRequest::check($_POST);

// 验证失败
if ($v->isFail()) {
    var_dump($v->getErrors());
    var_dump($v->firstError());
}

// 验证成功 ...
$safeData = $v->getSafeData(); // 验证通过的安全数据
// $postData = $v->all(); // 原始数据

$db->save($safeData);
```

<a name="how-to-use3"></a>

### 方式3: 使用trait `ValidationTrait`

创建一个新的class，并使用 Trait `Inhere\Validate\ValidationTrait`。

> 此方式是高级自定义的使用方式, 可以方便的嵌入到其他类中。

如下，嵌入到一个数据模型类中, 实现一个简单的模型基类，添加数据库记录前自动进行验证

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
    public function load(array $data)
    {
        $this->data = $data;

        return $this;
    }

    public function create()
    {
        if ($this->validate()->isFail()) {
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
    public function rules(): array
    {
        return [
            ['username, passwd', 'required'],
            ['passwd', 'compare', 'repasswd', 'on' => 'create'],
            ['username', 'string', 'min' => 2, 'max' => 20, 'on' => 'create' ],
            ['id', 'number', 'on' => 'update' ], // 仅作用于场景 update
            ['createdAt, updatedAt', 'safe'],
        ];
    }
}

// on controller action ...
class UserController
{
    // in action
    // @api /user/add
    public function addAction()
    {
        $model = new UserModel;
        // 载入提交数据并设定场景为: create
        $model->load($_POST)->atScene('create');

        if (!$ret = $model->create()) {
            exit($model->firstError());
        }

        echo "add success: userId = $ret";
    }
}
```

## 添加自定义验证器

底层调用验证器是支持：

- 一个闭包
- 一个函数名称
- 一个当前验证类的方法名
  - 在继承了 `Inhere\Validate\Validation` 的子类添加验证方法. 请看上面的 [使用方式1](#how-to-use2)
- 一个通过 `Validation->addValidator()` 添加的临时验证器
- 一个通过 `Validator\UserValidators::set` 注册的全局验证器
- 一个实现了 `Validator\ValidatorInterface` 的对象
- 一个可调用的对象(有 `__invoke` 方法)

> 注意： 写在当前类里的验证器方法必须带有后缀 `Validator`, 以防止对内部的其他的方法造成干扰

### 示例

```php
$v = Validation::make($_POST,[
        // add rule
        ['title', 'min', 40],
        ['freeTime', 'number'],
        ['title', 'checkTitle'],
    ])
    ->addValidator('checkTitle',function($title) { // 第一个参数是字段值。最后一个参数总是 $data
        // some logic ...

        return true; // 成功返回 True。验证失败,返回 False.
    }, '{attr} default message!')
    ->validate();
```

直接写闭包进行验证 e.g:

```php
    ['status', function($status) { // 第一个参数是字段值。最后一个参数总是 $data
        if ($status > 3) {
            return true;
        }

        return false;
    }]
```

定义一个闭包验证类进行验证,这种方法能提高验证方法的复用性

> 别忘了继承 `\Inhere\Validate\Validator\AbstractValidator`,和实现必须方法`validate`

```php
class AdemoValidator extends \Inhere\Validate\Validator\AbstractValidator
{

    public function validate($value, $data): bool
    {
        if ($value == 1) {
            return true;
        }
        return false;
    }

}

// 使用
    ['status', new AdemoValidator()],
```

<a name="on-in-Validation"></a>

## **验证前置/后置**处理

* 方式1: 在 `Validation` 中

> 该方法与`onBeforeValidate`&`onAfterValidate`有冲突

```php
use Inhere\Validate\Validation;

class PageValidation extends Validation
{
    # 进行验证前处理,返回false则停止验证,但没有错误信息,可以在逻辑中调用 addError 增加错误信息
    public function beforeValidate(): bool
    {
        return true;
    }
    # 进行验证后处理,该干啥干啥
    public function afterValidate(): bool
    {
        return true;
    }
}
```

* 方式2: `onBeforeValidate`&`onAfterValidate`

```php
use Inhere\Validate\Validation;

$v = Validation::make(['name' => 'inhere'], [
    ['name', 'string', 'min' => 3, 'filter' => 'trim|upper']
]);

$v->onBeforeValidate(function (Validation $v) {
    return true;
});

$v->onAfterValidate(function (Validation $v) {

});

$v->validate();
```

## 一个完整的规则示例

一个完整的规则示例, 包含了所有可添加的项。

**注意：**

- 每条规则的第一个元素**必须**是 _要验证的字段_(可以同时配置多个，可以是数组. type:`string|array`)
- 第二个元素**必须**是**一个**验证器(字符串，闭包，可回调的对象或数组. type:`string|Closure|callable`)
- 后面紧跟着 是验证器可能需要的参数信息 (若验证器需要的**参数只有一个，则参数无需带key**)
- 然后就是其他选项配置(`msg`, `on`, `filter`, ...)

```php
// a full rule
[
 // basic validate setting
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

> 字段验证器 `FieldValidation` 的配置类似，只是 **只有一个字段，而验证器允许有多个**

## 规则选项设置

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
    public function rules(): array
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
    public function rules(): array
    {
         return [
            ['title', 'required' ],
            ['tagId', 'number', 'when' => function($data) {
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

> 如何确定值为空 [关于为空](#about-empty-value)

### `isEmpty` -- 是否为空判断

是否为空判断, 这个判断作为 `skipOnEmpty` 的依据. 默认使用 `Validators::isEmpty` 来判断.

你也可以自定义判断规则:

```php
['name', 'string', 'isEmpty' => function($value) {
    return true or false;
 }]
```

自定义 isEmpty 验证时，应留意 $value 是否为 ArrayValueNotExists 实例

```php
['users.*.id', 'each', 'required', 'isEmpty' => function($value) {
    // each value must be verified
    foreach ($value as $item) {
        if ($item instanceof \Inhere\Validate\Exception\ArrayValueNotExists) {
            return true;
    }
    // your code here ...
}]
```

### `filter` -- 使用过滤器

支持在进行验证前对值使用过滤器进行净化过滤[内置过滤器](#built-in-filters)

```php
['tagId,userId,freeTime', 'number', 'filter' => 'int'],
['field', 'validator', 'filter' => 'filter0|filter1...'],

// 需要自定义性更高时，可以使用数组。
['field1', 'validator', 'filter' => [
    'string',
    'trim',
    ['Class', 'method'],
    ['Object', 'method'],
    // 追加额外参数。 传入时，第一个参数总是要过滤的字段值，其余的依次追加
    'myFilter' => ['arg1', 'arg2'],
    // 直接使用闭包
    function($val) {
        return str_replace(' ', '', $val);
    },
]],
```

**提示：**

- 允许同时使用多个过滤器。字符串使用 `|` 分隔，或者配置为数组。
- 注意： 写在当前类里的过滤器方法必须带有后缀 `Filter`, 以防止对内部的其他的方法造成干扰
- 通过类 `Filtration`，可以独立使用过滤器功能
- php内置过滤器请参看 <http://php.net/manual/zh/filter.filters.sanitize.php>

## 场景验证

如果需要让定义的规则在多个类似情形下重复使用，可以设置规则的使用场景。 **在验证时也表明要验证的场景**

### 方式1

在继承类中使用 `scenarios()` 方法：

```php
    // 在继承了 Validation 的子类 ValidationClass 中 ...

    // 定义不同场景需要验证的字段。
    // 功能跟规则里的 'on' 类似，两者尽量不要同时使用，以免混淆。
    public function scenarios(): array
    {
        return [
            'create' => ['user', 'pwd', 'code'],
            'update' => ['user', 'pwd'],
        ];
    }
```

### 方式2

添加规则时配置 `on` 选项：

```php
    // 在继承了 Validation 的子类 ValidationClass 中 ...
    public function rules(): array
    {
         return [
            ['title', 'required' ],
            ['userId', 'number', 'on' => 'create' ],
            ['userId', 'int', 'on' => 'update' ],
            ['name', 'string', 'on' => 'create,update' ],
        ];
    }
```

### 验证使用

```php

$v->setSecne('update')->validate();

```

## 选项设置

### `StopOnError`

`stopOnError` 是否在出现第一个验证错误时就停止验证。 

- 默认 `true`, 总是只会拿到第一个错误,这样性能更好。
- 设置为 `false`, 则会检查全部规则,拿到全部的验证错误。

```php
$v->setStopOnError(false);
```

### `SkipOnEmpty`

- `SkipOnEmpty` 为空是否跳过验证, 默认 `true` (此时需配合 `required` 验证不可为空)

```php
$v->setSkipOnEmpty(false);
```

<a name="built-in-filters"></a>

## 内置的过滤器

> 一些 php 内置的函数可直接使用。 e.g `trim|ucfirst` `json_decode` `md5`

过滤器 | 说明 | 示例
-------|-------------|------------
`abs` | 返回绝对值 | `['field', 'int', 'filter' => 'abs'],`
`int/integer` | 过滤非法字符并转换为`int`类型 **支持数组** | `['userId', 'number', 'filter' => 'int'],`
`bool/boolean` | 转换为 `bool`  [关于bool值](#about-bool-value) | `['argee', 'bool']`
`float` | 过滤非法字符,保留`float`格式的数据 | `['price', 'float', 'filter' => 'float'],`
`string` | 过滤非法字符并转换为`string`类型 | `['userId', 'number', 'filter' => 'string'],`
`trim` | 去除首尾空白字符，支持数组。 | `['username', 'min', 4, 'filter' => 'trim'],`
`nl2br` | 转换 `\n` `\r\n` `\r` 为 `<br/>` | `['content', 'string', 'filter' => 'nl2br'],`
`lower/lowercase` | 字符串转换为小写 | `['description', 'string', 'filter' => 'lowercase'],`
`upper/uppercase` | 字符串转换为大写 | `['title', 'string', 'filter' => 'uppercase'],`
`snake/snakeCase` | 字符串转换为蛇形风格 | `['title', 'string', 'filter' => 'snakeCase'],`
`camel/camelCase` | 字符串转换为驼峰风格 | `['title', 'string', 'filter' => 'camelCase'],`
`timestamp/strToTime` | 字符串日期转换时间戳 | `['pulishedAt', 'number', 'filter' => 'strToTime'],`
`url` | URL 过滤,移除所有不符合 URL 的字符 | `['field', 'url', 'filter' => 'url'],`
`str2list/str2array` | 字符串转数组 `'tag0,tag1' -> ['tag0', 'tag1']` | `['tags', 'strList', 'filter' => 'str2array'],`
`unique` | 去除数组中的重复值(by `array_unique()`) | `['tagIds', 'intList', 'filter' => 'unique'],`
`email` | email 过滤,移除所有不符合 email 的字符 | `['field', 'email', 'filter' => 'email'],`
`encoded` | 去除 URL 编码不需要的字符,与 `urlencode()` 函数很类似 | `['imgUrl', 'url', 'filter' => 'encoded'],`
`clearSpace` | 清理空格 | `['title', 'string', 'filter' => 'clearSpace'],`
`clearNewline` | 清理换行符 | `['title', 'string', 'filter' => 'clearNewline'],`
`clearTags/stripTags` | 相当于使用 `strip_tags()` | `['content', 'string', 'filter' => 'clearTags'],`
`escape/specialChars` | 相当于使用 `htmlspecialchars()` 转义数据 | `['content', 'string', 'filter' => 'specialChars'],`
`quotes` | 应用 `addslashes()` 转义数据 | `['content', 'string', 'filter' => 'quotes'],`

<a name="built-in-validators"></a>

## 内置的验证器

> `/` 分隔的验证器，表明功能是一样的，只是有不同的别名

验证器 | 说明 | 规则示例
----------|-------------|------------
`required`  | 要求此字段/属性是必须的(不为空的)。[关于为空](#about-empty-value) | `['tagId, userId', 'required' ]`
`int/integer`   | 验证是否是 int **支持范围检查** | `['userId', 'int']` `['userId', 'int', 'min'=>4, 'max'=>16]`
`num/number`    | 验证是否是 number(大于0的整数) **支持范围检查** | `['userId', 'number']` `['userId', 'number', 'min'=>4, 'max'=>16]`
`bool/boolean`  | 验证是否是 bool. [关于bool值](#about-bool-value) | `['open', 'bool']`
`float` | 验证是否是 float | `['price', 'float']`
`string`    | 验证是否是 string. **支持长度检查** | `['name', 'string']`, `['name', 'string', 'min'=>4, 'max'=>16]`
`accepted`  | 验证的字段必须为 `yes/on/1/true` 这在确认「服务条款」是否同意时有用(ref laravel) | `['agree', 'accepted']`
`url`   | 验证是否是 url | `['myUrl', 'url']`
`email` | 验证是否是 email | `['userEmail', 'email']`
`alpha`   | 验证值是否仅包含字母字符 | `['name', 'alpha']`
`alphaNum`   | 验证是否仅包含字母、数字 | `['field', 'alphaNum']`
`alphaDash`   | 验证是否仅包含字母、数字、破折号（ - ）以及下划线（ _ ） | `['field', 'alphaDash']`
`map/isMap`   | 验证值是否是一个非自然数组 map (key - value 形式的) | `['goods', 'isMap']`
`list/isList`   | 验证值是否是一个自然数组 list (key是从0自然增长的) | `['tags', 'isList']`
`array/isArray`   | 验证是否是数组 | `['goods', 'isArray']`
`each` | 对数组中的每个值都应用**给定的验证器**(这里的绝大多数验证器都可以使用)，并且要**全部通过** | `['goods.*','each','string']`, `['goods.*','each','string','min'=>3]`
`hasKey`   | 验证数组存在给定的key(s) | `['goods', 'hasKey', 'pear']` `['goods', 'hasKey', ['pear', 'banana']]`
`distinct`   | 数组中的值必须是唯一的 | `['goods', 'distinct']`, `['users.*.id', 'distinct']`
`ints/intList`   | 验证字段值是否是一个 int list | `['tagIds', 'intList']`
`numList`   | 验证字段值是否是一个 number list | `['tagIds', 'numList']`
`strings/strList`   | 验证字段值是否是一个 string list | `['tags', 'strList']`
`arrList`   | 验证字段值是否是一个 array list(多维数组) | `['tags', 'arrList']`
`min`   | 最小边界值验证 | `['title', 'min', 40]`
`max`   | 最大边界值验证 | `['title', 'max', 40]`
`size/range/between`  | 验证大小范围, 可以支持验证 `int`, `string`, `array` 数据类型 | `['tagId', 'size', 'min'=>4, 'max'=>567]`
`length`    | 长度验证（ 跟 `size`差不多, 但只能验证 `string`, `array` 的长度 | `['username', 'length', 'min' => 5, 'max' => 20]`
`fixedSize/sizeEq/lengthEq` | 固定的长度/大小(验证 `string`, `array` 长度, `int` 大小) | `['field', 'fixedSize', 12]`
`startWith` | 值(`string/array`)是以给定的字符串开始 | `['field', 'startWith', 'hell']`
`endWith` | 值(`string/array`)是以给定的字符串结尾 | `['field', 'endWith', 'world']`
`in/enum`  | 枚举验证: 包含 | `['status', 'in', [1,2,3]]`
`notIn`    | 枚举验证: 不包含 | `['status', 'notIn', [4,5,6]]`
`inField`    | 枚举验证: 字段值 存在于 另一个字段（anotherField）的值中 | `['field', 'inField', 'anotherField']`
`eq/mustBe`   | 必须是等于给定值 | `['status', 'mustBe', 1]`
`ne/neq/notBe`   | 不能等于给定值 | `['status', 'notBe', 0]`
`eqField` | 字段值比较: 相同 | `['passwd', 'eqField', 'repasswd']`
`neqField` | 字段值比较: 不能相同 | `['userId', 'neqField', 'targetId']`
`ltField` | 字段值比较: 小于 | `['field1', 'ltField', 'field2']`
`lteField` | 字段值比较: 小于等于 | `['field1', 'lteField', 'field2']`
`gtField` | 字段值比较: 大于 | `['field1', 'gtField', 'field2']`
`gteField` | 字段值比较: 大于等于 | `['field1', 'gteField', 'field2']`
`requiredIf` | 指定的其它字段（ anotherField ）值等于任何一个 `value` 时，此字段为 **必填**(ref laravel) | `['city', 'requiredIf', 'myCity', ['chengdu'] ]`
`requiredUnless` | 指定的其它字段（ anotherField ）值等于任何一个 `value` 时，此字段为 **不必填**(ref laravel) | `['city', 'requiredUnless', 'myCity', ['chengdu'] ]`
`requiredWith` | 指定的字段中的 _任意一个_ 有值且不为空，则此字段为 **必填**(ref laravel) | `['city', 'requiredWith', ['myCity'] ]`
`requiredWithAll` | 如果指定的 _所有字段_ 都有值，则此字段为 **必填**(ref laravel) | `['city', 'requiredWithAll', ['myCity', 'myCity1'] ]`
`requiredWithout` | 如果缺少 _任意一个_ 指定的字段值，则此字段为 **必填**(ref laravel) | `['city', 'requiredWithout', ['myCity', 'myCity1'] ]`
`requiredWithoutAll` | 如果所有指定的字段 **都没有值**，则此字段为 **必填**(ref laravel) | `['city', 'requiredWithoutAll', ['myCity', 'myCity1'] ]`
`date` | 验证是否是 date | `['publishedAt', 'date']`
`dateFormat` | 验证是否是 date, 并且是指定的格式 | `['publishedAt', 'dateFormat', 'Y-m-d']`
`dateEquals` | 验证是否是 date, 并且是否是等于给定日期 | `['publishedAt', 'dateEquals', '2017-05-12']`
`beforeDate` | 验证字段值必须是给定日期之前的值(ref laravel) | `['publishedAt', 'beforeDate', '2017-05-12']`
`beforeOrEqualDate` | 字段值必须是小于或等于给定日期的值(ref laravel) | `['publishedAt', 'beforeOrEqualDate', '2017-05-12']`
`afterOrEqualDate` | 字段值必须是大于或等于给定日期的值(ref laravel) | `['publishedAt', 'afterOrEqualDate', '2017-05-12']`
`afterDate` | 验证字段值必须是给定日期之前的值 | `['publishedAt', 'afterDate', '2017-05-12']`
`json`   | 验证是否是json字符串(默认严格验证，必须以`{` `[` 开始) | `['goods', 'json']` `['somedata', 'json', false]` - 非严格，普通字符串`eg 'test'`也会通过
`file`   | 验证是否是上传的文件 | `['upFile', 'file']`
`image`   | 验证是否是上传的图片文件 | `['avatar', 'image']`, 限定后缀名 `['avatar', 'image', 'jpg,png']`
`ip`    | 验证是否是 IP | `['ipAddr', 'ip']`
`ipv4`    | 验证是否是 IPv4 | `['ipAddr', 'ipv4']`
`ipv6`    | 验证是否是 IPv6 | `['ipAddr', 'ipv6']`
`macAddress`    | 验证是否是 mac Address | `['field', 'macAddress']`
`md5`    | 验证是否是 md5 格式的字符串 | `['passwd', 'md5']`
`sha1`    | 验证是否是 sha1 格式的字符串 | `['passwd', 'sha1']`
`color`    | 验证是否是html color | `['backgroundColor', 'color']`
`regex/regexp` | 使用正则进行验证 | `['name', 'regexp', '/^\w+$/']`
`safe`    | 用于标记字段是安全的，无需验证 | `['createdAt, updatedAt', 'safe']`

### `safe` 验证器,标记属性/字段是安全的

特殊验证器 用于标记字段是安全的，无需验证，直接加入到安全数据中。

比如我们在写入数据库之前手动追加的字段: 创建时间，更新时间。

```php
['createdAt, updatedAt', 'safe']
```

### 一些补充说明

<a name="about-empty-value"></a>

#### 关于为空判断

字段符合下方任一条件时即为「空」

- 该值为 `null`.
- 该值为空字符串 `''`
- 该值为空数组 `[]`
- 该值为空对象 -- 空的 `可数` 对象
- 该值为没有路径的上传文件

<a name="about-bool-value"></a>

#### 关于布尔值

值符合下列的任意一项即认为是为bool值(不区分大小写)

- 是 "1"、"true"、"on" 和 "yes" (`TRUE`)
- 是 "0"、"false"、"off"、"no" 和 "" (`FALSE`)

#### 关于文件验证

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

#### 提示和注意

- **请将 `required*` 系列规则写在规则列表的最前面**
- 规则上都支持添加过滤器
- 验证大小范围 `int` 是比较大小。 `string` 和 `array` 是检查长度。大小范围 是包含边界值的
- `size/range` `length` 可以只定义 `min` 或者  `max` 值
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

- 支持对数组的子级值进行遍历验证

```php
[
  'goods' => [
       'apple' => 34,
       'pear' => 50,
   ],
  'users' => [
       ['id' => 34, 'name' => 'tom'],
       ['id' => 89, 'name' => 'john'],
   ]
]
```

```php
    ['goods.*', 'each', 'number'], //goods 下的 每个值 都必须为大于0 的整数
    // 写法是等效的
    // ['goods', 'each', 'number'], //goods 下的 每个值 都必须为大于0 的整数

    // 多维数组
    ['users.*.id', 'each', 'required'],
    ['users.*.id', 'each', 'number', 'min' => 34],
    ['users.*.name', 'each', 'string', 'min' => 5],
```

- 对于带有通配符`*`的字段, 添加过滤器是无效的

## 一些关键方法API

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
public function isFail()
public function hasError() // isFail() 的别名方法

// 成功通过验证
public function isOk()
public function isPassed()
```

获取验证是否通过(是否有验证失败)。

### 获取所有错误信息

```php
public function getErrors(): array
```

获取所有的错误信息, 包含所有错误的字段和错误信息的多维数组。 eg:

```php
[
    ['name' => 'field1', 'msg' => 'error Message1' ],
    ['name' => 'field2', 'msg' => 'error Message2' ],
    ...
]
```

> 同一个属性/字段也可能有多个错误消息，当为它添加了多个验证规则时。

### 得到第一个错误信息

```php
public function firstError($onlyMsg = true)
```

- `$onlyMsg` 是否只返回消息字符串。当为 false，返回的则是数组 eg: `['name' => 'field', 'msg' => 'error message']`

### 得到最后一个错误信息

```php
public function lastError($onlyMsg = true)
```

- `$onlyMsg` 是否只返回消息字符串。当为 false，返回的则是数组 eg: `['name' => 'field', 'msg' => 'error message']`

### 获取所有验证通过的数据

```php
public function getSafeData(): array|\stdClass
```

获取所有 **验证通过** 的安全数据.

- 此数据数组只包含加入了规则验证的字段数据，不会含有额外的字段。(可直接省去后续的字段收集)
- 推荐使用此数据进行后续操作，比如存入数据库等。

> 注意： 当有验证失败出现时，安全数据 `safeData` 将会被重置为空。 即只有全部通过验证，才能获取到 `safeData`

### 根据字段名获取安全值

```php
public function val(string $key, $default = null) // getSafe() 的别名方法
public function getValid(string $key, $default = null) // getSafe() 的别名方法
public function getSafe(string $key, $default = null)
```

从 **验证通过** 的数据中取出对应 key 的值

### 获取所有原始数据

```php
public function all(): array
```

获取验证时传入的所有数据

### 根据字段名获取原始数据的值

```php
public function getRaw(string $key, $default = null)
```

从验证时传入的数据中取出对应 key 的值

## 代码示例

可运行示例请看 `example`

## 单元测试

```sh
phpunit
```

## License

[MIT](LICENSE)

## 我的其他项目

- [inhere/console](https://github.com/inhere/php-validate) 轻量且功能丰富的命令行应用, 控制台交互，工具库
- [inhere/sroute](https://github.com/inhere/php-srouter) 轻量且快速的HTTP请求路由库

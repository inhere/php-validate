## a simple php data validator

### Install

- use composer
    - in command: `composer require inhere/php-validate`
    - or edit `composer.json`

_require_ add

```
"inhere/php-validate": "^1.0",
```


run: `composer update`

<a name="how-to-use"></a>
### how to use

- Method 1: create a new class
    e.g.

```php

    use inhere\validate\Validation;

    class PageRequest extends Validation
    {
        public function rules()
        {
            return [
                ['tagId,title,userId,freeTime', 'required', 'msg' => '{attr} is required!'],
                ['tagId', 'size', 'min'=>4, 'max'=>567], // 4<= tagId <=567
                ['title', 'min', 'min' => 40],
                ['freeTime', 'number'],
                ['tagId', 'number', 'when' => function($data)
                {
                    return isset($data['status']) && $data['status'] > 2;
                }],
                ['userId', 'number', 'on' => 'scene1' ],
                ['userId', 'int', 'on'    => 'scene2' ],
                ['title', 'customValidator', 'msg' => '{attr} error msg!' ],
                ['status', function($status)
                {

                    if ( .... ) {
                        return true;
                    }
                    return false;
                }],
            ];
        }
        
        // custom validator at the class. return a bool.
        protected function customValidator($title)
        {
            // some logic ...

            return true; // Or false;
        }

        // define field attribute's translate.
        public function attrTrans()
        {
            return [
              'userId' => '用户Id',
            ];
        }

        // custom validator message, more {@see ValidationTrait::_defaultMessages}
        public function messages()
        {
            return [
              'required' => '{attr} 是必填项。',
            ];
        }
    }
```

use, at other class

```php

$valid = PageRequest::make($_POST,)->validate();
if ( $valid->fail() ) {
    return $valid->getErrors();
}
...

```


- Method 2: direct use

```php

    use inhere\validate\Validation;

    class SomeClass
    {
        public function demo()
        {
            $valid = Validation::make($_POST,[
                // add rule
                ['title', 'min', 'min' => 40],
                ['freeTime', 'number'],
            ])->validate();

            if ( $valid->fail() ) {
                return $valid->getErrors();
            }

            //
            // some logic ... ...
        }
    }
```

### how to add custom validator

- add in the subclass of the `inhere\validate\Validation`. see [how-to-use](#how-to-use) method 1.
- add validator by method `addValidator`. e.g:

```php

$valid = Validation::make($_POST,[
            // add rule
            ['title', 'min', 'min' => 40],
            ['freeTime', 'number'],
            ['title', 'checkTitle', 'msg' => 'Title didn\'t pass the validate!' ],
        ])
        ->addValidator('checkTitle',function($title){
            // some logic ...

            return true; // if validate fail, return false.
        }, '{attr} default message!')
        ->validate();

```

### keywords

- scene -- 设置验证场景

> 如果需要让一个验证器在多个类似情形下使用,在验证时也表明要验证的场景

```php

// at a subclass of the Validation class
<?php

    public function rules()
    {
         return [
            ['title', 'required' ],
            ['userId', 'number', 'on' => 'scene1' ],
            ['userId', 'int',    'on' => 'scene2' ],
            ['name', 'string',    'on' => 'scene1,scene2' ],
        ];
    }
```

> 在下面设置了场景时，将会使用上面的第 1,3,4 条规则. (第 1 条没有限制规则使用场景的，在所有场景都可用)

```php

// at logic
<?php

    ...
    $valid = ValidationClass::make($_POST)->setScene('scene2')->validate();
    ...

```

- when -- 规则的前置条件

> 只有在先满足了(`when`)前置条件时才会验证这条规则
如在下面的例子中，检查到第二条规则时，会先执行闭包(`when`)，
当其返回 `true` 验证此条规则，
否则不会验证此条规则

```php

// at a subclass of the Validation class
<?php
    public function rules()
    {
         return [
            ['title', 'required' ],
            ['tagId', 'number', 'when' => function($data, $validator)
            {
               return isset($data['status']) && $data['status'] > 2;
            }],
        ];
    }
```

- skipOnEmpty -- 为空是否跳过验证

为空是否跳过验证,默认值是 `true`. (reference yii2)

> 'required' 规则不在此限制内.

如,有一条规则:

```
['name', 'string'],
```

提交的数据中 没有'name'字段或者 `$data['name']` 等于空都不会进行 `string` 验证;只有当`$data['name']`有值时才会验证是否是string


如果要想为空时也检查, 请将此字段同时加入 'required' 规则中.
也可以设置 `'skipOnEmpty' => false`:

```
['name', 'string', 'skipOnEmpty' => false ]
```

- isEmpty -- 是否为空判断

是否为空判断, 这个判断作为 `skipOnEmpty` 的依据.
默认使用 `empty($data['attr'])` 来判断.

你也可以自定义判断规则

```
['name', 'string', 'isEmpty' => function($data, $attr) {
    return true or false;
 }]
```

### Existing validators

validator | description | rule example
----------|-------------|------------
`int`   | validate int | ....
`number`    | validate number | ....
`bool`  | validate bool | ....
`float` | validate float | ....
`regexp`    | validate regexp | ....
`url`   | validate url | ....
`email` | validate email | ....
`ip`    | validate ip | ....
`required`  | validate required | `['tagId,userId', 'required' ]`
`length`    | validate length | ....
`size`  | validate size | `['tagId', 'size', 'min'=>4, 'max'=>567]`
`min`   | validate min | `['title', 'min', 'value' => 40],]`
`max`   | validate max | ....
`in`    | validate in | `['id', 'in', 'value' => [1,2,3],]`
`string`    | validate string | ....
`isArray`   | validate is Array | ....
`callback`  | validate by custom callback | ....
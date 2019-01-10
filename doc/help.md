# help


```php

    /**
     * @link http://php.net/manual/zh/function.filter-input.php
     * @param  int $type INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV
     * @param $varName
     * @param  array $filter 过滤/验证器 {@link http://php.net/manual/zh/filter.filters.php}
     * @param  array $options 一个选项的关联数组，或者按位区分的标示。
     *                         如果过滤器接受选项，可以通过数组的 "flags" 位去提供这些标示。
     * 如果成功的话返回所请求的变量。
     * 如果成功的话返回所请求的变量。
     * 如果过滤失败则返回 FALSE ，
     * 如果 varName 不存在的话则返回 NULL 。
     * 如果标示 FILTER_NULL_ON_FAILURE 被使用了，那么当变量不存在时返回 FALSE ，当过滤失败时返回 NULL 。
     */
    public static function input($type, $varName, $filter, array $options = [])
    {
    }

    public static function multi(array $data, array $filters = [])
    {
    }

    /**
     * @link http://php.net/manual/zh/function.filter-input-array.php
     * 检查(验证/过滤)输入数据中的多个变量名 like filter_input_array()
     * 当需要获取很多变量却不想重复调用 filter_input()时很有用。
     * @param  int $type One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV. 要检查的输入数据
     * @param  mixed $definition 一个定义参数的数组。
     *                            一个有效的键必须是一个包含变量名的string，
     *                            一个有效的值要么是一个filter type，或者是一个array 指明了过滤器、标示和选项。
     *                            如果值是一个数组，那么它的有效的键可以是 :
     *                                filter， 用于指明 filter type，
     *                                flags 用于指明任何想要用于过滤器的标示，
     *                                options 用于指明任何想要用于过滤器的选项。
     *                            参考下面的例子来更好的理解这段说明。
     * @param  bool $addEmpty 在返回值中添加 NULL 作为不存在的键。
     * 如果成功的话返回一个所请求的变量的数组，
     * 如果失败的话返回 FALSE 。
     * 对于数组的值，
     *     如果过滤失败则返回 FALSE ，
     *     如果 variable_name 不存在的话则返回 NULL 。
     * 如果标示 FILTER_NULL_ON_FAILURE 被使用了，那么当变量不存在时返回 FALSE ，当过滤失败时返回 NULL 。
     */
    public static function inputMulti($type, $definition, $addEmpty = true)
    {
    }

    /**
     * 检查变量名是否存在
     * @param  int $type One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV. 要检查的输入数据
     * @param  string $varName Name of a variable to check. 要检查的变量名
     */
    public static function inputHasVar($type, $varName)
    {
    }
```
<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-11-07
 * Time: 10:23
 */

namespace Inhere\Validate;

/**
 * Interface ValidationInterface
 * @package Inhere\Validate
 */
interface ValidationInterface
{
    /**
     * 进行数据验证
     * @author inhere
     * @date   2015-08-11
     * @param  array $onlyChecked 可以设置此次需要验证的字段
     * @param  bool|null $stopOnError 是否出现错误即停止验证
     * @return static
     * @throws \RuntimeException
     */
    public function validate(array $onlyChecked = [], $stopOnError = null);
}
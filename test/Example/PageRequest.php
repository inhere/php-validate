<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 23:35
 */

namespace Inhere\ValidateTest\Example;

use Inhere\Validate\Validation;

/**
 * Class PageRequest
 */
class PageRequest extends Validation
{
    public function rules(): array
    {
        return [
            ['tagId,userId,freeTime', 'required'],
            ['tagId', 'size', 'min' => 4, 'max' => 567], // 4<= tagId <=567
            ['title', 'min', 'min' => 40],
            ['freeTime', 'number', 'msg' => '{attr} is require number!'],
            [
                'test',
                'number',
                'when' => function ($data) {
                    return isset($data['status']) && $data['status'] > 2;
                }
            ],
            ['userId', 'number', 'on' => 'other'],
            //            ['userId', function($value){ return false;}],
        ];
    }

    public function translates(): array
    {
        return [
            'userId' => '用户Id',
        ];
    }

    /**
     * custom validator message
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'required' => '{attr} 是必填项。',
        ];
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2019-01-11
 * Time: 00:10
 */

namespace Inhere\ValidateTest\Filter;

use Inhere\Validate\Filter\Filtration;
use PHPUnit\Framework\TestCase;

/**
 * Class FiltrationTest
 * @package Inhere\ValidateTest\Filter
 */
class FiltrationTest extends TestCase
{
    /**
     *
     */
    public function testFiltering()
    {
        $data = [
            'name'    => ' tom ',
            'status'  => ' 23 ',
            'word'    => 'word',
            'toLower' => 'WORD',
            'title'   => 'helloWorld',
        ];

        $rules = [
            ['name', 'string|trim'],
            ['status', 'trim|int'],
            ['word', 'string|trim|upper'],
            ['toLower', 'lower'],
            [
                'title',
                [
                    'string',
                    'snake' => ['-'],
                    'ucfirst',
                ]
            ],
        ];

        $fl = Filtration::make($data);
        $fl->setRules($rules);

        // get cleaned data
        $cleaned = $fl->filtering();
        $this->assertSame('tom', $cleaned['name']);
        $this->assertSame(' tom ', $fl->get('name'));
        $this->assertSame('default', $fl->get('not-exist', null, 'default'));
        $this->assertSame('TOM', $fl->get('name', 'trim|upper'));

        $fl->reset(true);

        $this->assertEmpty($fl->all());
        $this->assertEmpty($fl->getData());
        $this->assertEmpty($fl->getRules());
    }
}

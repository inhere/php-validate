<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-11-24
 * Time: 18:20
 */

use PHPUnit\Framework\TestCase;
use Inhere\Validate\Filter\Filtration;

/**
 * Class FiltrationTest
 * @covers \Inhere\Validate\Filter\Filtration
 */
class FiltrationTest extends TestCase
{
    public function testFiltration()
    {
        $data = [
            'name' => ' tom ',
            'status' => ' 23 ',
            'word' => 'word',
            'toLower' => 'WORD',
            'title' => 'helloWorld',
        ];

        $rules = [
            ['name', 'string|trim'],
            ['status', 'trim|int'],
            ['word', 'string|trim|upper'],
            ['toLower', 'lower'],
            ['title', [
                'string',
                'snake' => ['-'],
                'ucfirst',
            ]],
        ];

        $cleaned = Filtration::make($data, $rules)->filtering();

        $this->assertSame($cleaned['name'], 'tom');
        $this->assertSame($cleaned['status'], 23);
        $this->assertSame($cleaned['word'], 'WORD');
        $this->assertSame($cleaned['toLower'], 'word');
        $this->assertSame($cleaned['title'], 'Hello-world');
    }
}

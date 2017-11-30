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
 */
class FiltrationTest extends TestCase
{
    public function testFiltration()
    {
        $data = [
            'name' => ' tom ',
            'status' => ' 23 ',
        ];
        $rules = [
            ['name', 'string|trim'],
        ];

        $cleaned = Filtration::make($data, $rules)->filtering();

        $this->assertSame($cleaned['name'], 'tom');
    }
}

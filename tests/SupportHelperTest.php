<?php


use Exylon\Fuse\Support\Attributes;
use Illuminate\Support\Collection;

class SupportHelperTest extends \PHPUnit\Framework\TestCase
{

    public function testStrReplaceAssoc()
    {
        $this->assertEquals("one two three", str_replace_assoc([
            '1' => 'one',
            '2' => 'two',
            '3' => 'three',
        ], "1 2 3"));
        $this->assertEquals("No foo will change", str_replace_assoc([
            'foo' => 'bar',
            'bar' => 'foo'
        ], "No foo will change"));
        $this->assertEquals("oranges are color orange", str_replace_assoc([
            'apple' => 'orange',
            'red'   => 'orange'
        ], "apples are color red"));

    }

    public function testRandomHexString()
    {
        $output = random_hex_string(16);
        $this->assertEquals(16, strlen($output));
        $this->assertRegExp('/^[a-f0-9]{1,}$/is', $output);
    }

    public function testRandomIntString()
    {
        $output = random_int_string(16);
        $this->assertEquals(16, strlen($output));
        $this->assertRegExp('/^[0-9]{1,}$/is', $output);

        $output = random_int_string(16, 0, 'x');
        $this->assertEquals(16, strlen($output));
        $this->assertRegExp('/^[x]*[0-9]{1,}$/is', $output);
    }

    public function testSnakeCaseToTitle()
    {
        $output = snake_to_title_case('lorem_ipsum_dolor_sit_amet');
        $this->assertEquals('Lorem Ipsum Dolor Sit Amet', $output);

        $output = snake_to_title_case('lorem_ipsum_dolor_sit-amet');
        $this->assertEquals('Lorem Ipsum Dolor Sit-amet', $output);
    }


    public function testAttributes()
    {
        $items = [
            'red'    => 'apple',
            'orange' => 'orange',
            'yellow' => [
                'mangoes' => 'foo',
                'pear'    => 'bar'
            ]
        ];

        $arr = new Attributes($items, [
            'pula' => 'red'
        ]);
        $this->assertArrayHasKey('red', $arr);
        $this->assertInstanceOf(Attributes::class, $arr->yellow);
        $this->assertTrue($arr->yellow === $arr->yellow);
        $this->assertTrue($arr->yellow->mangoes === 'foo');
        $this->assertTrue($arr->yellow->pear === 'bar');
        $this->assertEquals('apple', $arr['red']);
        $this->assertEquals('apple', $arr['pula']);
        $this->assertEquals('foo', $arr['yellow']['mangoes']);
        $this->assertInstanceOf(Collection::class, $arr->toCollection());
    }
}

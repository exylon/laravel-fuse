<?php

namespace Tests;

use Exylon\Fuse\Support\Arr;
use Exylon\Fuse\Support\Attributes;
use Exylon\Fuse\Support\Eloquent\CascadeDelete;
use Illuminate\Validation\ValidationException;
use Tests\Models\Customer;

class SupportHelperTest extends TestCase
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

    public function testValidate()
    {
        $validated = validate([
            'name' => 'John Howe',
            'age'  => 24
        ], [
            'name' => 'string'
        ]);

        $this->assertArraySubset([
            'name' => 'John Howe'
        ], $validated, true);
        $this->assertArrayNotHasKey('age', $validated);
    }

    public function testFailedValidate()
    {
        $this->expectException(ValidationException::class);

        $validated = validate([
            'name' => 'John Howe',
            'age'  => 24
        ], [
            'name' => 'numeric',
            'age'  => 'string'
        ]);
    }

    public function testRandomHexString()
    {
        $output = str_random_hex(16);
        $this->assertEquals(16, strlen($output));
        $this->assertRegExp('/^[a-f0-9]{1,}$/is', $output);
    }

    public function testRandomIntString()
    {
        $output = str_random_int(16);
        $this->assertEquals(16, strlen($output));
        $this->assertRegExp('/^[0-9]{1,}$/is', $output);

        $output = str_random_int(16, 0, 'x');
        $this->assertEquals(16, strlen($output));
        $this->assertRegExp('/^[x]*[0-9]{1,}$/is', $output);
    }

    public function testProperCase()
    {
        $output = proper_case('lorem_ipsum_dolor_sit_amet', '_');
        $this->assertEquals('Lorem Ipsum Dolor Sit Amet', $output);

        $output = proper_case('lorem_ipsum_dolor_sit-amet', '_');
        $this->assertEquals('Lorem Ipsum Dolor Sit-Amet', $output);

        $output = proper_case('lorem_ipsum_dolor_sit-amet', ['_', '-']);
        $this->assertEquals('Lorem Ipsum Dolor Sit Amet', $output);
    }

    public function testArrayDotReverse()
    {
        $arr = [
            'one.a'   => 'red',
            'one.b'   => 'blue',
            'one.c'   => 'green',
            'one.d.d' => 'white'
        ];

        $test = Arr::dotReverse($arr);
        $this->assertArraySubset([
            'one' => [
                'a' => 'red',
                'b' => 'blue',
                'c' => 'green',
                'd' => [
                    'd' => 'white'
                ]
            ]
        ], $test);
    }

    public function testArrayDot()
    {
        $this->assertArraySubset([
            'red.apple' => ['sweet', 'salty']
        ], Arr::dot([
            'red' => [
                'apple' => [
                    'sweet',
                    'salty'
                ]
            ]
        ]));

        $this->assertArraySubset([
            'red.apple.sweet' => true,
            'red.apple.0'     => 'salty'
        ], Arr::dot([
            'red' => [
                'apple' => [
                    'sweet' => true,
                    'salty'
                ]
            ]
        ]));
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
    }

    public function testHasTrait()
    {
        $customer = new Customer();

        self::assertTrue(has_trait($customer, CascadeDelete::class));

        self::assertTrue(has_trait(Customer::class, CascadeDelete::class));
    }
}

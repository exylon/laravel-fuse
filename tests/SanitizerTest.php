<?php

namespace Tests;

use Exylon\Fuse\Sanitizer\Sanitizer;

class SanitizerTest extends TestCase
{

    public function testGlobalSanitizer()
    {
        $sanitizer = \Exylon\Fuse\Facades\Sanitizer::getFacadeRoot();
        $rules = [
            '*'     => 'trim::string',
            'email' => 'strtolower',
            'names' => function ($arr) {
                return $arr[0];
            }
        ];
        $data = [
            'email' => '    EXAMPLE@EXAMPLE.COM    ',
            'names' => [
                'foo',
                'bar'
            ]
        ];

        $sanitizer->setGlobalRules($rules);

        $data = $sanitizer->sanitize($data);

        $this->assertEquals('example@example.com', $data['email']);
        $this->assertEquals('foo', $data['names']);

    }


    public function testValueSanitizer()
    {
        $sanitizer = \Exylon\Fuse\Facades\Sanitizer::getFacadeRoot();
        $this->assertEquals('example@example.com',
            $sanitizer->sanitizeValue('  EXAMPLE@EXAMPLE.COM   ', 'trim|strtolower'));
        $this->assertEquals('example@example.com',
            $sanitizer->sanitizeValue('  EXAMPLE@EXAMPLE.COM   ', [
                'trim',
                function ($value) {
                    return strtolower($value);
                }
            ]));
        $this->assertEquals('example@example.com',
            $sanitizer->sanitizeValue('  EXAMPLE@EXAMPLE.COM   ', [
                'trim',
                'Tests\SanitizerHelper@toLower'
            ]));
        $this->assertEquals('example@example.com',
            $sanitizer->sanitizeValue('  EXAMPLE@EXAMPLE.COM   ',
                'trim|Tests\SanitizerHelper@toLower'
            ));
    }

    public function testInlineSanitizer()
    {
        $sanitizer = \Exylon\Fuse\Facades\Sanitizer::getFacadeRoot();
        $rules = [
            '*'     => 'trim::string',
            'email' => 'strtolower',
            'names' => function ($arr) {
                return $arr[0];
            }
        ];
        $data = [
            'email' => '    EXAMPLE@EXAMPLE.COM    ',
            'names' => [
                'foo',
                'bar'
            ]
        ];


        $data = $sanitizer->sanitize($data, $rules);

        $this->assertEquals('example@example.com', $data['email']);
        $this->assertEquals('foo', $data['names']);
    }

    public function testInlineWithGlobalSanitizer()
    {

        $sanitizer = \Exylon\Fuse\Facades\Sanitizer::getFacadeRoot();
        $globalRules = [
            'email' => 'trim',
            'names' => function ($arr) {
                return $arr[0];
            }
        ];
        $rules = [
            'email' => 'strtolower',
        ];
        $data = [
            'email' => '    EXAMPLE@EXAMPLE.COM    ',
            'names' => [
                'foo',
                'bar'
            ]
        ];
        $sanitizer->setGlobalRules($globalRules);
        $data = $sanitizer->sanitize($data, $rules);

        $this->assertEquals('example@example.com', $data['email']);
        $this->assertEquals('foo', $data['names']);
    }

    public function testCustomSanitizers()
    {
        $sanitizer = \Exylon\Fuse\Facades\Sanitizer::getFacadeRoot();
        $sanitizer->register('slugify', function ($item) {
            return str_slug($item);
        });
        $sanitizer->register('upper', 'Tests\SanitizerHelper@toUpper');
        $this->assertEquals('foo-bar',
            $sanitizer->sanitizeValue('Foo Bar', 'trim|slugify'));
        $this->assertEquals('FOO BAR',
            $sanitizer->sanitizeValue('foo bar', ['trim', 'upper']));
    }

}

class SanitizerHelper
{
    public static function toUpper($value)
    {
        return strtoupper($value);
    }

    public static function toLower($value)
    {
        return strtolower($value);
    }
}

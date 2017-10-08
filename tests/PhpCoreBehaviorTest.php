<?php


class PhpCoreBehaviorTest extends \PHPUnit\Framework\TestCase
{
    public function testInstanceOf()
    {
        $one = new SampleClass();
        $two = new SampleClass();
        $three = $one;

        $this->assertTrue($one instanceof $two);
        $this->assertTrue($two instanceof $one);
        $this->assertTrue($two == $one);
        $this->assertFalse($two === $one);
        $this->assertTrue($three === $one);
        $this->assertTrue($one instanceof SampleClass);
        $this->assertTrue($two instanceof SampleClass);
        $this->assertTrue($three instanceof SampleClass);
    }
}


class SampleClass
{

    public function greet(string $name)
    {
        return "Hello, $name!";
    }
}

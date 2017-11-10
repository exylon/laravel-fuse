<?php


namespace Test\Http\Responses;


use Illuminate\Http\Request;
use Tests\TestCase;

class EntityResponseBuilderTest extends TestCase
{

    public function testExpectedJsonResponse()
    {
        $request = \Mockery::mock(Request::class)->shouldReceive('expectsJson')->andReturn(true)->getMock();
        $expected = [
            'name' => 'Alpha',
            'slug' => 'alpha-test'
        ];
        $response = entity($expected)->toResponse($request);
        $this->assertJsonStringEqualsJsonString(json_encode($expected), $response->getContent());
    }

    public function testFilteredWithOnly()
    {
        $request = \Mockery::mock(Request::class)->shouldReceive('expectsJson')->andReturn(true)->getMock();
        $entity = [
            'name' => 'Alpha',
            'slug' => 'alpha-test'
        ];
        $response = entity($entity)->only(['name'])->toResponse($request);
        $this->assertJsonStringEqualsJsonString(json_encode([
            'name' => 'Alpha'
        ]), $response->getContent());
    }

    public function testFilteredWithExcept()
    {
        $request = \Mockery::mock(Request::class)->shouldReceive('expectsJson')->andReturn(true)->getMock();
        $entity = [
            'name' => 'Alpha',
            'slug' => 'alpha-test'
        ];
        $response = entity($entity)->except(['name'])->toResponse($request);
        $this->assertJsonStringEqualsJsonString(json_encode([
            'slug' => 'alpha-test'
        ]), $response->getContent());
    }
}

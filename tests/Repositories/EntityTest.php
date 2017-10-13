<?php


namespace Tests\Repositories;


use Exylon\Fuse\Repositories\Entity;
use Tests\TestCase;

class EntityTest extends TestCase
{

    public function testPrimaryKey()
    {
        $expectedKey = 1;
        $entity = new Entity($expectedKey, []);
        $this->assertEquals($expectedKey, $entity->getKey());
    }
}

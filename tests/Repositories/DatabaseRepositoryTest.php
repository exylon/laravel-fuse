<?php


namespace Tests\Repositories;


use Exylon\Fuse\Repositories\Database\Repository;
use Exylon\Fuse\Repositories\Entity;
use Illuminate\Support\Collection;
use Tests\TestCase;

class DatabaseRepositoryTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--realpath' => realpath(__DIR__ . '/../migrations')
        ]);

        $this->artisan('migrate', ['--database' => 'testing']);
    }

    public function testAll()
    {
        $repo = new Repository('users');

        $results = $repo->all();
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(Entity::class, $results->first());
        $this->assertEquals(1, $results->first()->getKey());
    }

    public function testCreate()
    {
        $repo = new Repository('users');

        $entity = $repo->create([
            'name' => 'John Smith'
        ]);

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('John Smith', $entity->name);
        $this->assertNotNull($entity->getKey());
        $this->assertDatabaseHas('users', [
            'name' => 'John Smith'
        ]);

    }

    public function testMake()
    {
        $repo = new Repository('users');

        $entity = $repo->make([
            'name' => 'John Baker'
        ]);

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('John Baker', $entity->name);
        $this->assertNull($entity->getKey());
        $this->assertDatabaseMissing('users', [
            'name' => 'John Baker'
        ]);

    }
}

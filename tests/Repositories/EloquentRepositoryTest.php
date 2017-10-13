<?php


namespace Tests\Repositories;

use Exylon\Fuse\Repositories\Eloquent\Repository;
use Exylon\Fuse\Repositories\Entity;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Models\User;
use Tests\TestCase;

class EloquentRepositoryTest extends TestCase
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
        $repo = new Repository(new User());

        $results = $repo->all();
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(Entity::class, $results->first());
        $this->assertEquals(1, $results->first()->getKey());

        $results = $repo->with(['avatars'])->all();
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(Entity::class, $results->first());
        $this->assertEquals(1, $results->first()->getKey());
        $this->assertNotNull($results->first()->avatars);
    }

    public function testCreate()
    {
        $repo = new Repository(new User());

        $entity = $repo->create([
            'name' => 'John Smith'
        ]);

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('John Smith', $entity->name);
        $this->assertNotNull($entity->getKey());

    }

    public function testCreateWithValidation()
    {
        $this->expectException(ValidationException::class);

        $repo = new Repository(new User());

        $repo->setValidationRules([
            'name' => ['required', 'string']
        ], [
        ]);

        $entity = $repo->withValidation()->create([
            'name' => 1234
        ]);

    }

    public function testMake()
    {
        $repo = new Repository(new User());

        $entity = $repo->make([
            'name' => 'John Baker'
        ]);

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('John Baker', $entity->name);
        $this->assertNull($entity->getKey());

    }

    public function testMakeWithValidation()
    {
        $this->expectException(ValidationException::class);

        $repo = new Repository(new User());

        $repo->setValidationRules([
            'name' => ['required', 'string']
        ], [

        ]);

        $entity = $repo->withValidation()->make([
            'name' => 1234
        ]);

    }

    public function testUpdate()
    {
        $repo = new Repository(new User());

        $entity = $repo->update(1, [
            'name' => 'Sarah Smith'
        ]);

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('Sarah Smith', $entity->name);
        $this->assertEquals(1, $entity->getKey());

    }


    public function testUpdateWithValidation()
    {
        $this->expectException(ValidationException::class);

        $repo = new Repository(new User());

        $repo->setValidationRules([], [
            'name' => ['required', 'string']
        ]);
        $entity = $repo->withValidation()->update(1, [
            'name' => 1234
        ]);

    }

    public function testUpdateWhere()
    {
        $repo = new Repository(new User());

        $original = $repo->create([
            'name' => 'John Donner'
        ]);

        $entity = $repo->updateWhere([
            'name' => 'John Donner'
        ], [
            'name' => 'Sarah Connor'
        ]);

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('Sarah Connor', $entity->name);
        $this->assertEquals($original->getKey(), $entity->getKey());

    }


    public function testDelete()
    {
        $repo = new Repository(new User());

        $original = $repo->create([
            'name' => 'John Bravo'
        ]);

        $result = $repo->delete($original->getKey());
        $this->assertTrue($result);
        $this->assertFalse(DB::table('users')->where('name', 'John Bravo')->exists());


        $original = $repo->create([
            'name' => 'John Bravo'
        ]);

        $result = $repo->delete($original);
        $this->assertTrue($result);
        $this->assertFalse(DB::table('users')->where('name', 'John Bravo')->exists());

    }


    public function testDeleteWhere()
    {
        $repo = new Repository(new User());

        $original = $repo->create([
            'name' => 'John Norton'
        ]);

        $result = $repo->deleteWhere([
            'name' => 'John Norton'
        ]);

        $this->assertTrue($result);
        $this->assertFalse(DB::table('users')->where('name', 'John Norton')->exists());

    }

    public function testFind()
    {
        $repo = new Repository(new User());

        $original = $repo->create([
            'name' => 'John Wiggs'
        ]);

        $entity = $repo->find($original->getKey());
        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('John Wiggs', $entity->name);
        $this->assertNotNull($entity->getKey());

        $entity = $repo->find($original);
        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('John Wiggs', $entity->name);
        $this->assertNotNull($entity->getKey());
    }

    public function testFailingFind()
    {
        $this->expectException(ModelNotFoundException::class);
        $repo = new Repository(new User());
        $repo->find(null);
    }

    public function testFindBy()
    {
        $repo = new Repository(new User());

        $original = $repo->create([
            'name' => 'John Chrysler'
        ]);

        $entity = $repo->findBy('name', 'John Chrysler');
        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('John Chrysler', $entity->name);
        $this->assertNotNull($entity->getKey());
    }

    public function testFindAllBy()
    {
        $repo = new Repository(new User());

        $original = $repo->create([
            'name' => 'John Andersen'
        ]);


        $results = $repo->findAllBy('name', 'John Andersen');
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(Entity::class, $results->first());
        $this->assertEquals($original->getKey(), $results->first()->getKey());
    }

    public function testFindWhere()
    {
        $repo = new Repository(new User());

        $original = $repo->create([
            'name' => 'John Nye'
        ]);

        $entity = $repo->findWhere([
            'name' => 'John Nye'
        ]);
        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('John Nye', $entity->name);
        $this->assertNotNull($entity->getKey());
    }

    public function testFindAllWhere()
    {
        $repo = new Repository(new User());

        $original = $repo->create([
            'name' => 'John Hensley'
        ]);

        $results = $repo->findAllWhere([
            'name' => 'John Hensley'
        ]);
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(Entity::class, $results->first());
        $this->assertEquals($original->getKey(), $results->first()->getKey());
    }

    public function testTransformer()
    {
        $repo = new Repository(new User());

        $original = $repo->create([
            'name' => 'John Haywood'
        ]);

        $result = $repo->withTransformer(function ($item) {
            return 'callback';
        })->find($original);
        $this->assertEquals('callback', $result);


        $classCallback = \Mockery::mock()
            ->shouldReceive('transform')
            ->andReturn('class_callback')
            ->getMock();
        $result = $repo->withTransformer($classCallback)->find($original);
        $this->assertEquals('class_callback', $result);


        $classCallback = \Mockery::mock()
            ->shouldReceive('doTransform')
            ->andReturn('class_callback')
            ->getMock();
        $result = $repo->withTransformer([$classCallback, 'doTransform'])->find($original);
        $this->assertEquals('class_callback', $result);
    }

    public function testPaginate()
    {
        $repo = new Repository(new User());

        $count = 5;

        for ($i = 0; $i < $count; $i++) {
            $repo->create([
                'name' => 'Maximilian'
            ]);
        }

        $results = $repo->paginate(1);
        $this->assertInstanceOf(LengthAwarePaginator::class, $results);
        $this->assertCount(1, $results->getCollection());
        $this->assertInstanceOf(Entity::class, $results->getCollection()->first());
        $this->assertTrue($results->hasMorePages());


        $results = $repo->paginateWhere([
            'name' => 'Maximilian'
        ], 1);
        $this->assertEquals($count, $results->total());
        $this->assertTrue($results->hasMorePages());
    }

    public function testSimplePaginate()
    {
        $repo = new Repository(new User());

        $count = 5;

        for ($i = 0; $i < $count; $i++) {
            $repo->create([
                'name' => 'Maximilian'
            ]);
        }

        $results = $repo->withOptions([
            'pagination_method' => 'simple'
        ])->paginate(1);
        $this->assertInstanceOf(Paginator::class, $results);
        $this->assertCount(1, $results->getCollection());
        $this->assertInstanceOf(Entity::class, $results->getCollection()->first());
        $this->assertTrue($results->hasMorePages());


        $results = $repo->withOptions([
            'pagination_method' => 'simple'
        ])->paginateWhere([
            'name' => 'Maximilian'
        ], 1);
        $this->assertTrue($results->hasMorePages());
    }

    public function testComplexWhere()
    {
        $repo = new Repository(new User());

        $repo->create([
            'name' => 'Mike Doner'
        ]);
        $repo->create([
            'name' => 'Mike Kasmer'
        ]);
        $repo->create([
            'name' => 'Mike Hyde'
        ]);

        $results = $repo->findAllWhere([
            'name' => ['like', 'Mike%'],
        ]);
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(Entity::class, $results->first());
        $this->assertCount(3, $results);

        $repo->create([
            'name' => 'Sam Hilliard'
        ]);
        $repo->create([
            'name' => 'Sam Layton'
        ]);
        $repo->create([
            'name' => 'Sam Bullock'
        ]);

        $results = $repo->findAllWhere([
            'name'     => ['like', 'Mike%'],
            'or--name' => [
                'operation'  => 'like',
                'parameters' => 'Sam%'
            ]
        ]);

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(Entity::class, $results->first());
        $this->assertCount(6, $results);


        $results = $repo->findAllWhere([
            'name'    => ['like', 'Mike%'],
            'or-name' => ['like', 'Sam%']
        ]);

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(Entity::class, $results->first());
        $this->assertCount(6, $results);
    }

}

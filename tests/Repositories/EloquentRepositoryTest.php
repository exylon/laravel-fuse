<?php


namespace Tests\Repositories;

use Exylon\Fuse\Repositories\Eloquent\Repository;
use Exylon\Fuse\Repositories\Entity;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
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

        $results = $repo->with('avatars')->all();
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(Entity::class, $results->first());
        $this->assertEquals(1, $results->first()->getKey());
        $this->assertNotNull($results->first()->avatars);
    }


    public function testAllUsingBuilder()
    {
        $repo = new Repository(User::active());

        $results = $repo->all();
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(Entity::class, $results->first());
        $this->assertEquals(2, $results->first()->getKey());

        $results = $repo->with(['avatars'])->all();
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(Entity::class, $results->first());
        $this->assertEquals(2, $results->first()->getKey());
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
        $this->assertDatabaseHas('users', [
            'name' => 'John Smith'
        ]);

    }

    public function testCreateUsingBuilder()
    {
        $repo = new Repository(User::active());

        $entity = $repo->create([
            'name' => 'John Smith Jr.'
        ]);

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('John Smith Jr.', $entity->name);
        $this->assertNotNull($entity->getKey());
        $this->assertDatabaseHas('users', [
            'name' => 'John Smith Jr.',
        ]);

    }

    public function testCreateWithValidation()
    {
        $this->expectException(ValidationException::class);

        $repo = new Repository(new User(), [], app('validator'));

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

        $repo = new Repository(new User(), [], app('validator'));

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

        $entity = $repo->append('gender')->update(1, [
            'name' => 'Sarah Smith'
        ]);

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('Sarah Smith', $entity->name);
        $this->assertEquals(1, $entity->getKey());
        $this->assertEquals('male', $entity->gender);

    }

    public function testUpdateWithBuilder()
    {
        $repo = new Repository(User::active());

        $entity = $repo->update(2, [
            'name' => 'Sarah Smith, PHD'
        ]);

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('Sarah Smith, PHD', $entity->name);
        $this->assertEquals(2, $entity->getKey());
    }

    public function testUpdateWithBuilderWithUnmatchedScope()
    {
        $this->expectException(ModelNotFoundException::class);

        $repo = new Repository(User::active());

        $entity = $repo->update(1, [
            'name' => 'Sarah Smith, PHD'
        ]);
    }


    public function testUpdateWithValidation()
    {
        $this->expectException(ValidationException::class);

        $repo = new Repository(new User(), [], app('validator'));

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

        $entity = $repo->append('gender')->updateWhere([
            'name' => 'John Donner'
        ], [
            'name' => 'Sarah Connor'
        ]);

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('Sarah Connor', $entity->name);
        $this->assertEquals($original->getKey(), $entity->getKey());
        $this->assertEquals('male', $entity->gender);

    }

    public function testUpdateAllWhere()
    {
        $repo = new Repository(new User());

        $repo->create([
            'name'   => 'John Fiat',
            'status' => 'denied'
        ]);

        $repo->create([
            'name'   => 'John Porsche',
            'status' => 'denied'
        ]);

        $repo->create([
            'name'   => 'John Zedd',
            'status' => 'denied'
        ]);

        $affectedRows = $repo->updateAllWhere(['status' => 'denied'], ['status' => 'accepted']);
        $this->assertEquals(3, $affectedRows);
    }

    public function testUpdateWhereWithBuilder()
    {
        $repo = new Repository(User::active());

        $original = $repo->create([
            'name'   => 'John Donner Jr.',
            'status' => 'active'
        ]);
        $repo->create([
            'name'   => 'John Donner Jr.',
            'status' => 'unknown'
        ]);

        $entity = $repo->updateWhere([
            'name' => 'John Donner Jr.'
        ], [
            'name' => 'Sarah Connor, PHD'
        ]);

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('Sarah Connor, PHD', $entity->name);
        $this->assertEquals($original->getKey(), $entity->getKey());
        $this->assertDatabaseHas('users', [
            'name'   => 'John Donner Jr.',
            'status' => 'unknown'
        ]);

    }


    public function testDelete()
    {
        $repo = new Repository(new User());

        $original = $repo->create([
            'name' => 'John Bravo'
        ]);

        $result = $repo->delete($original->getKey());
        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', [
            'name' => 'John Bravo'
        ]);


        $original = $repo->create([
            'name' => 'John Bravo'
        ]);

        $result = $repo->delete($original);
        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', [
            'name' => 'John Bravo'
        ]);

    }

    public function testDeleteWithBuilder()
    {
        $repo = new Repository(User::active());

        $original = $repo->create([
            'name'   => 'John Bravo Jr.',
            'status' => 'active'
        ]);

        $repo->create([
            'name'   => 'John Bravo Jr.',
            'status' => 'unknown'
        ]);

        $result = $repo->delete($original->getKey());
        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', [
            'name'   => 'John Bravo Jr.',
            'status' => 'active'
        ]);
        $this->assertDatabaseHas('users', [
            'name'   => 'John Bravo Jr.',
            'status' => 'unknown'
        ]);
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
        $this->assertDatabaseMissing('users', [
            'name' => 'John Norton'
        ]);

    }


    public function testDeleteWhereWithBuilder()
    {
        $repo = new Repository(User::active());

        $original = $repo->create([
            'name'   => 'John Norton Jr.',
            'status' => 'active'
        ]);
        $repo->create([
            'name'   => 'John Norton Jr.',
            'status' => 'unknown'
        ]);


        $result = $repo->deleteWhere([
            'name' => 'John Norton Jr.'
        ]);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', [
            'name'   => 'John Norton Jr.',
            'status' => 'active'
        ]);
        $this->assertDatabaseHas('users', [
            'name'   => 'John Norton Jr.',
            'status' => 'unknown'
        ]);

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

        $entity = $repo->append('gender')->find($original);
        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('John Wiggs', $entity->name);
        $this->assertNotNull($entity->getKey());
        $this->assertEquals('male', $entity->gender);
    }

    public function testFindWithBuilder()
    {
        $repo = new Repository(User::active());

        $original = $repo->create([
            'name'   => 'John Wiggs Jr.',
            'status' => 'active'
        ]);
        $repo->create([
            'name'   => 'John Wiggs Jr.',
            'status' => 'unknown'
        ]);

        $entity = $repo->find($original->getKey());
        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('John Wiggs Jr.', $entity->name);
        $this->assertNotNull($entity->getKey());

        $entity = $repo->find($original);
        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('John Wiggs Jr.', $entity->name);
        $this->assertNotNull($entity->getKey());
    }

    public function testFailingFind()
    {
        $this->expectException(ModelNotFoundException::class);
        $repo = new Repository(new User());
        $repo->find(null);
    }

    public function testFailingFindWithBuilder()
    {

        $this->expectException(ModelNotFoundException::class);
        $repo = new Repository(User::active());

        $repo->create([
            'name'   => 'John Cena Jr.',
            'status' => 'active'
        ]);
        $original = $repo->create([
            'name'   => 'John Cena Jr.',
            'status' => 'unknown'
        ]);
        $entity = $repo->find($original);
    }

    public function testFindBy()
    {
        $repo = new Repository(new User());

        $original = $repo->create([
            'name' => 'John Chrysler'
        ]);

        $entity = $repo->append('gender')->findBy('name', 'John Chrysler');
        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('John Chrysler', $entity->name);
        $this->assertNotNull($entity->getKey());
        $this->assertEquals('male', $entity->gender);
    }

    public function testFindByWithBuilder()
    {
        $repo = new Repository(User::active());

        $original = $repo->create([
            'name'   => 'John Chrysler Jr.',
            'status' => 'active'
        ]);
        $repo->create([
            'name'   => 'John Chrysler Jr.',
            'status' => 'unknown'
        ]);

        $entity = $repo->findBy('name', 'John Chrysler Jr.');
        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('John Chrysler Jr.', $entity->name);
        $this->assertEquals($original->getKey(), $entity->getKey());
    }

    public function testFindAllBy()
    {
        $repo = new Repository(new User());

        $original = $repo->create([
            'name' => 'John Andersen'
        ]);


        $results = $repo->append('gender')->findAllBy('name', 'John Andersen');
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(Entity::class, $results->first());
        $this->assertEquals($original->getKey(), $results->first()->getKey());
        $this->assertEquals('male', $results->first()->gender);
    }

    public function testFindAllByWithBuilder()
    {
        $repo = new Repository(User::active());

        $original = $repo->create([
            'name'   => 'John Andersen',
            'status' => 'active'
        ]);
        $repo->create([
            'name'   => 'John Andersen',
            'status' => 'unknown'
        ]);


        $results = $repo->findAllBy('name', 'John Andersen');
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(Entity::class, $results->first());
        $this->assertEquals($original->getKey(), $results->first()->getKey());
        $this->assertCount(1, $results);
    }

    public function testFindWhere()
    {
        $repo = new Repository(new User());

        $original = $repo->create([
            'name' => 'John Nye'
        ]);

        $entity = $repo->append('gender')->findWhere([
            'name' => 'John Nye'
        ]);
        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('John Nye', $entity->name);
        $this->assertNotNull($entity->getKey());
        $this->assertEquals('male', $entity->gender);
    }

    public function testFindWhereWithBuilder()
    {
        $repo = new Repository(User::active());

        $original = $repo->create([
            'name'   => 'John Nye Jr.',
            'status' => 'active'
        ]);

        $repo->create([
            'name'   => 'John Nye Jr.',
            'status' => 'unknown'
        ]);

        $entity = $repo->findWhere([
            'name' => 'John Nye Jr.'
        ]);
        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('John Nye Jr.', $entity->name);
        $this->assertEquals($original->getKey(), $entity->getKey());
    }

    public function testFindAllWhere()
    {
        $repo = new Repository(new User());

        $original = $repo->create([
            'name' => 'John Hensley'
        ]);

        $results = $repo->append('gender')->findAllWhere([
            'name' => 'John Hensley'
        ]);
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(Entity::class, $results->first());
        $this->assertEquals($original->getKey(), $results->first()->getKey());
        $this->assertEquals('male', $results->first()->gender);
    }

    public function testFindAllWhereWithBuilder()
    {
        $repo = new Repository(User::active());

        $original = $repo->create([
            'name'   => 'John Hensley Jr.',
            'status' => 'active'
        ]);
        $repo->create([
            'name'   => 'John Hensley Jr.',
            'status' => 'unknown'
        ]);

        $results = $repo->findAllWhere([
            'name' => 'John Hensley Jr.'
        ]);
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(Entity::class, $results->first());
        $this->assertEquals($original->getKey(), $results->first()->getKey());
    }

    public function testExists()
    {
        $repo = new Repository(new User());

        $repo->create([
            'name' => 'John Carter'
        ]);
        $repo->create([
            'name' => 'John Jackson'
        ]);

        $this->assertTrue($repo->exists([
            'name' => 'John Carter'
        ]));


        $repo->deleteWhere([
            'name' => 'John Jackson'
        ]);

        $this->assertFalse($repo->exists([
            'name' => 'John Jackson'
        ]));

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
            ->with(\Mockery::type(User::class), \Mockery::type('array'))
            ->andReturn('class_callback')
            ->getMock();
        $result = $repo->withTransformer($classCallback)->find($original);
        $this->assertEquals('class_callback', $result);


        $classCallback = \Mockery::mock()
            ->shouldReceive('doTransform')
            ->with(\Mockery::type(User::class), \Mockery::type('array'))
            ->andReturn('class_callback')
            ->getMock();
        $result = $repo->withTransformer([$classCallback, 'doTransform'])->find($original);
        $this->assertEquals('class_callback', $result);

        $result = $repo->withTransformer('Tests\Repositories\SimpleTransformer@transform')->find($original);
        $this->assertEquals('John Haywood', $result);
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


        $results = $repo->findAllWhere([
            'id' => [
                'method'     => 'between',
                'parameters' => [1, 3]
            ]
        ]);

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(Entity::class, $results->first());
        $this->assertCount(3, $results);
    }

    public function testAppend()
    {

        $repo = new Repository(new User());

        $repo->create([
            'name' => 'Mike Dosner'
        ]);

        $user = $repo->append('age', 'gender')->findWhere([
            'name' => 'Mike Dosner'
        ]);

        $this->assertEquals(18, $user->age);
        $this->assertEquals('male', $user->gender);
    }

}

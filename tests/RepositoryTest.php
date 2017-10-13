<?php


class RepositoryTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Exylon\Fuse\Repositories\Eloquent\Repository
     */
    protected $repository;

    protected function setUp()
    {
        parent::setUp();





        $this->repository = new \Exylon\Fuse\Repositories\Eloquent\Repository($this->createMockedModel());
    }

    private function createMockedModel()
    {
        $mock = Mockery::mock(\Illuminate\Database\Eloquent\Model::class);
        $mock->shouldReceive('newInstance')->andReturn($mock);
        return $mock;
    }

    public function testTransformer()
    {
        $ret = $this->repository->withTransformer(function ($item) {
            return "foobar";
        })->find($this->createMockedModel());
        $this->assertEquals("foobar", $ret);

        $ret = $this->repository->withTransformer(new Transformer())->find($this->createMockedModel());
        $this->assertEquals("foobar", $ret);
    }
}


class Transformer
{
    public function transform($item)
    {
        return 'foobar';
    }

}

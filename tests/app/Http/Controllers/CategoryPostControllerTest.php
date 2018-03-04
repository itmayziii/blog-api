<?php

namespace Tests\Http\Controllers;

use App\Category;
use App\Http\Controllers\CategoryPostController;
use App\Http\JsonApi;
use App\Repositories\CacheRepository;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Response;
use Mockery;
use Mockery\Mock;
use Tests\TestCase;

class CategoryPostControllerTest extends TestCase
{
    /**
     * @var Response | Mock
     */
    private $responseMock;
    /**
     * @var JsonApi | Mock
     */
    private $jsonApiMock;
    /**
     * @var CategoryRepository | Mock
     */
    private $categoryRepositoryMock;
    /**
     * @var CacheRepository | Mock
     */
    private $cacheRepositoryMock;
    /**
     * @var Category | Mock
     */
    private $categoryMock;
    /**
     * @var CategoryPostController
     */
    private $categoryPostController;

    public function setUp()
    {
        parent::setUp();
        $this->responseMock = Mockery::mock(Response::class);
        $this->jsonApiMock = Mockery::mock(JsonApi::class);
        $this->categoryRepositoryMock = Mockery::mock(CategoryRepository::class);
        $this->cacheRepositoryMock = Mockery::mock(CacheRepository::class);
        $this->categoryMock = Mockery::mock(Category::class);

        $this->categoryPostController = new CategoryPostController($this->jsonApiMock, $this->categoryRepositoryMock, $this->cacheRepositoryMock);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_show_responds_not_found_if_category_does_not_exist()
    {
        $this->cacheRepositoryMock
            ->shouldReceive('remember')
            ->once()
            ->andReturn(null);

        $this->jsonApiMock
            ->shouldReceive('respondResourceNotFound')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->categoryPostController->show($this->responseMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_responds_with_category_if_category_exists()
    {
        $this->cacheRepositoryMock
            ->shouldReceive('remember')
            ->once()
            ->andReturn($this->categoryMock);

        $this->jsonApiMock
            ->shouldReceive('respondResourceFound')
            ->once()
            ->withArgs([$this->responseMock, $this->categoryMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->categoryPostController->show($this->responseMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }
}
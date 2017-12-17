<?php

use App\Post;
use App\Category;
use App\Http\Controllers\CategoryPostController;

class CategoryPostControllerTest extends \TestCase
{
    /**
     * @var CategoryPostController
     */
    private $categoryPostController;

    public function setUp()
    {
        parent::setUp();
        $this->categoryPostController = new CategoryPostController($this->jsonApiMock);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_not_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceNotFound')->once()->andReturn('Category With Posts Not Found');

        $actualResponse = $this->categoryPostController->show(897324986234563353);

        $this->assertThat($actualResponse, $this->equalTo('Category With Posts Not Found'));
    }

    public function test_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceFound')->once()->andReturn('Category With Posts Found');

        $category = $this->create_categories_with_posts();
        $actualResponse = $this->categoryPostController->show($category->id);

        $this->assertThat($actualResponse, $this->equalTo('Category With Posts Found'));
    }

    private function create_categories_with_posts()
    {
        $category = $this->keepTryingIntegrityConstraints(function () {
            return factory(Category::class, 1)->create()->first();
        });

        $category->posts()->saveMany([
            factory(Post::class)->create()->first(),
            factory(Post::class)->create()->all()[1]
        ]);

        return $category;
    }
}
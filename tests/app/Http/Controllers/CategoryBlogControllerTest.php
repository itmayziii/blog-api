<?php

use App\Blog;
use App\Category;
use App\Http\Controllers\CategoryBlogController;

class CategoryBlogControllerTest extends \TestCase
{
    /**
     * @var CategoryBlogController
     */
    private $categoryBlogController;

    public function setUp()
    {
        parent::setUp();
        $this->categoryBlogController = new CategoryBlogController($this->jsonApiMock);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_not_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceNotFound')->once()->andReturn('Category With Blogs Not Found');

        $actualResponse = $this->categoryBlogController->show(897324986234563353);

        $this->assertThat($actualResponse, $this->equalTo('Category With Blogs Not Found'));
    }

    public function test_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceFound')->once()->andReturn('Category With Blogs Found');

        $category = $this->create_categories_with_blogs();
        $actualResponse = $this->categoryBlogController->show($category->id);

        $this->assertThat($actualResponse, $this->equalTo('Category With Blogs Found'));
    }

    private function create_categories_with_blogs()
    {
        $category = $this->keepTryingIntegrityConstraints(function () {
            return factory(Category::class, 1)->create()->first();
        });

        $category->blogs()->saveMany([
            factory(Blog::class)->create()->first(),
            factory(Blog::class)->create()->all()[1]
        ]);

        return $category;
    }
}
<?php

use App\Category;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Request;

class CategoryControllerTest extends \TestCase
{
    /**
     * @var CategoryController
     */
    private $categoryController;

    public function setUp()
    {
        parent::setUp();
        $this->categoryController = new CategoryController($this->jsonApiMock);
    }

    public function test_listing()
    {
        $this->jsonApiMock->shouldReceive('respondResourcesFound')->once()->andReturn('Categories Found');

        $request = Request::create('v1/categories');
        $response = $this->categoryController->index($request);

        $this->assertThat($response, $this->equalTo('Categories Found'));
    }

    public function test_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceFound')->once()->andReturn('Category Found');

        $category = $this->createCategory();
        $response = $this->categoryController->show($category->id);

        $this->assertThat($response, $this->equalTo('Category Found'));
    }

    public function test_not_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceNotFound')->once()->andReturn('Category Not Found');

        $response = $this->categoryController->show(186846348632);

        $this->assertThat($response, $this->equalTo('Category Not Found'));
    }

    public function test_create_authorization()
    {
        $this->jsonApiMock->shouldReceive('respondUnauthorized')->once()->andReturn('Category Creation Authorization Failed');

        $this->actAsStandardUser();
        $request = Request::create('v1/categories', 'POST');
        $response = $this->categoryController->store($request);

        $this->assertThat($response, $this->equalTo('Category Creation Authorization Failed'));
    }

    public function test_create_validation_failed()
    {
        $this->jsonApiMock->shouldReceive('respondValidationFailed')->once()->andReturn('Category Create Validation Failed');

        $this->actAsAdministrator();

        $request = Request::create(
            'v1/categories',
            'POST',
            []
        );
        $response = $this->categoryController->store($request);

        $this->assertThat($response, $this->equalTo('Category Create Validation Failed'));
    }

    public function test_create_successful()
    {
        $this->jsonApiMock->shouldReceive('respondResourceCreated')->once()->andReturn('Category Create Successful');

        $this->actAsAdministrator();

        $request = Request::create(
            'v1/categories',
            'POST',
            [
                'name' => 'Technology'
            ]
        );
        $response = $this->categoryController->store($request);

        $this->assertThat($response, $this->equalTo('Category Create Successful'));
    }

    private function createCategory()
    {
        return factory(Category::class, 1)->create()->first();
    }
}
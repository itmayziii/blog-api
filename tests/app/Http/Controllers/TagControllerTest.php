<?php

use App\Http\Controllers\TagController;
use App\Tag;
use Illuminate\Support\Facades\Request;

class TagControllerTest extends \TestCase
{
    /**
     * @var tagController
     */
    private $tagController;

    public function setUp()
    {
        parent::setUp();
        $this->tagController = new TagController($this->jsonApiMock);
    }

    public function test_listing()
    {
        $this->jsonApiMock->shouldReceive('respondResourcesFound')->once()->andReturn('Tags Found');

        $request = Request::create('v1/Tags');
        $response = $this->tagController->index($request);

        $this->assertThat($response, $this->equalTo('Tags Found'));
    }

    public function test_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceFound')->once()->andReturn('Tag Found');

        $tag = $this->createTag();
        $response = $this->tagController->show($tag->id);

        $this->assertThat($response, $this->equalTo('Tag Found'));
    }

    public function test_not_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceNotFound')->once()->andReturn('Tag Not Found');

        $response = $this->tagController->show(186846348632);

        $this->assertThat($response, $this->equalTo('Tag Not Found'));
    }

    public function test_create_authorization()
    {
        $this->jsonApiMock->shouldReceive('respondUnauthorized')->once()->andReturn('Tag Creation Authorization Failed');

        $this->actAsStandardUser();
        $request = Request::create('v1/Tags', 'POST');
        $response = $this->tagController->store($request);

        $this->assertThat($response, $this->equalTo('Tag Creation Authorization Failed'));
    }

    public function test_create_validation_failed()
    {
        $this->jsonApiMock->shouldReceive('respondValidationFailed')->once()->andReturn('Tag Create Validation Failed');

        $this->actAsAdministrator();

        $request = Request::create(
            'v1/Tags',
            'POST',
            []
        );
        $response = $this->tagController->store($request);

        $this->assertThat($response, $this->equalTo('Tag Create Validation Failed'));
    }

    public function test_create_successful()
    {
        $this->jsonApiMock->shouldReceive('respondResourceCreated')->once()->andReturn('Tag Create Successful');

        $this->actAsAdministrator();

        $request = Request::create(
            'v1/Tags',
            'POST',
            [
                'name' => 'Crazy Tag'
            ]
        );
        $response = $this->tagController->store($request);

        $this->assertThat($response, $this->equalTo('Tag Create Successful'));
    }

    public function test_update_authorization()
    {
        $this->jsonApiMock->shouldReceive('respondUnauthorized')->once()->andReturn('Tag Update Authorization Failed');

        $this->actAsStandardUser();
        $request = Request::create('v1/Tags/28', 'PATCH');
        $response = $this->tagController->update($request, 28);

        $this->assertThat($response, $this->equalTo('Tag Update Authorization Failed'));
    }

    public function test_update_not_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceNotFound')->once()->andReturn('Tag Not Found');

        $this->actAsAdministrator();

        $request = Request::create('v1/Tags/9674325643254', 'PATCH');
        $response = $this->tagController->update($request, 9674325643254);

        $this->assertThat($response, $this->equalTo('Tag Not Found'));
    }

    public function test_update_successful()
    {
        $this->jsonApiMock->shouldReceive('respondResourceUpdated')->once()->andReturn('Tag Updated');

        $this->actAsAdministrator();

        $tag = $this->createTag();
        $request = Request::create('v1/Tags/' . $tag->id, 'PATCH', ['name' => 'Crazy Tag']);
        $response = $this->tagController->update($request, $tag->id);

        $this->assertThat($response, $this->equalTo('Tag Updated'));
    }

    public function test_delete_authorization()
    {
        $this->jsonApiMock->shouldReceive('respondUnauthorized')->once()->andReturn('Tag Delete Authorization Failed');

        $this->actAsStandardUser();

        $response = $this->tagController->delete(100);

        $this->assertThat($response, $this->equalTo('Tag Delete Authorization Failed'));
    }

    public function test_delete_not_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceNotFound')->once()->andReturn('Tag Not Found');

        $this->actAsAdministrator();

        $response = $this->tagController->delete(9674325643254);

        $this->assertThat($response, $this->equalTo('Tag Not Found'));
    }

    public function test_successful_deletion()
    {
        $this->jsonApiMock->shouldReceive('respondResourceDeleted')->once()->andReturn('Tag Deleted');

        $this->actAsAdministrator();

        $tag = $this->createTag();
        $response = $this->tagController->delete($tag->id);

        $this->assertThat($response, $this->equalTo('Tag Deleted'));
    }

    private function createTag()
    {
        return $this->keepTryingIntegrityConstraints(function () {
            return factory(Tag::class, 1)->create()->first();
        });
    }
}
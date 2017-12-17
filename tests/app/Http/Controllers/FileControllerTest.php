<?php

use App\Post;
use App\Http\Controllers\FileController;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileControllerTest extends \TestCase
{
    /**
     * @var FileController
     */
    private $fileController;
    /**
     * @var Filesystem
     */
    private $fileSystemMock;

    public function setUp()
    {
        parent::setUp();
        $this->fileSystemMock = \Mockery::mock(Filesystem::class)->shouldDeferMissing();
        $this->fileController = new FileController($this->fileSystemMock);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_uploadImage_authorization()
    {
        $this->actAsStandardUser();
        $request = Request::create('v1/images', 'POST');
        $response = $this->fileController->uploadImage($request);

        $this->assertThat($response->getContent(), $this->equalTo('Unauthorized'));
        $this->assertThat($response->getStatusCode(), $this->equalTo(401));
    }

    public function test_uploadImage_no_files_uploaded()
    {
        $this->actAsAdministrator();
        $request = Request::create('v1/images', 'POST');

        $response = $this->fileController->uploadImage($request);

        $this->assertThat($response->getContent(), $this->equalTo('No file given to upload'));
        $this->assertThat($response->getStatusCode(), $this->equalTo(400));
    }

    public function test_uploadImage_successful()
    {
        $this->fileSystemMock->shouldReceive('put')->once()->andReturn(123);

        $this->actAsAdministrator();
        $file = new UploadedFile('tmp/123/zzz', '/path/to/fake/file.jpg', null, null, 2, true);
        $request = Request::create('/v1/images', 'POST', [], [], [$file]);

        $response = $this->fileController->uploadImage($request);

        $this->assertThat($response->getContent(), $this->equalTo('["/path/to/fake/file.jpg"'));
        $this->assertThat($response->getStatusCode(), $this->equalTo(200));
    }
}

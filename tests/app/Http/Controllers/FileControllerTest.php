<?php

namespace Tests\Http\Controllers;

use App\Http\Controllers\FileController;
use App\Http\JsonApi;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class FileControllerTest extends TestCase
{
    /**
     * @var Response | Mock
     */
    private $responseMock;
    /**
     * @var Request | Mock
     */
    private $requestMock;
    /**
     * @var Filesystem | Mock
     */
    private $fileSystemMock;
    /**
     * @var JsonApi | Mock
     */
    private $jsonApiMock;
    /**
     * @var Gate | Mock
     */
    private $gateMock;
    /**
     * @var LoggerInterface | Mock
     */
    private $loggerMock;
    /**
     * @var Mock
     */
    private $fileMock;
    /**
     * @var FileController
     */
    private $fileController;

    public function setUp()
    {
        parent::setUp();

        $this->responseMock = Mockery::mock(Response::class);
        $this->requestMock = Mockery::mock(Request::class);
        $this->fileSystemMock = Mockery::mock(Filesystem::class);
        $this->jsonApiMock = Mockery::mock(JsonApi::class);
        $this->gateMock = Mockery::mock(Gate::class);
        $this->loggerMock = Mockery::mock(LoggerInterface::class);
        $this->fileMock = Mockery::mock('file');

        $this->fileController = new FileController($this->fileSystemMock, $this->jsonApiMock, $this->gateMock, $this->loggerMock);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_uploadImages_authorization_failure_responds_forbidden()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['store', $this->fileSystemMock])
            ->andReturn(true);

        $this->jsonApiMock
            ->shouldReceive('respondForbidden')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->fileController->uploadImages($this->requestMock, $this->responseMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_uploadImages_responds_as_bad_request_when_no_files_are_on_request()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['store', $this->fileSystemMock])
            ->andReturn(false);

        $this->jsonApiMock
            ->shouldReceive('respondBadRequest')
            ->once()
            ->withArgs([$this->responseMock, 'No file given to upload'])
            ->andReturn($this->responseMock);

        $this->requestMock
            ->shouldReceive('allFiles')
            ->once()
            ->andReturn([]);

        $actualResult = $this->fileController->uploadImages($this->requestMock, $this->responseMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_uploadImages_responds_images_successfully_uploaded_on_good_request()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['store', $this->fileSystemMock])
            ->andReturn(false);

        $this->jsonApiMock
            ->shouldReceive('respondImagesUploaded')
            ->once()
            ->andReturn($this->responseMock);

        $this->requestMock
            ->shouldReceive('allFiles')
            ->once()
            ->andReturn([
                'file1' => 'path/to/file1',
                'file2' => 'path/to/file2'
            ]);

        $this->requestMock
            ->shouldReceive('file')
            ->once()
            ->withArgs(['file1'])
            ->andReturn($this->fileMock);

        $this->requestMock
            ->shouldReceive('file')
            ->once()
            ->withArgs(['file2'])
            ->andReturn($this->fileMock);

        $this->fileMock
            ->shouldReceive('getClientOriginalName')
            ->times(6);

        $this->fileMock
            ->shouldReceive('move')
            ->twice();

        $actualResult = $this->fileController->uploadImages($this->requestMock, $this->responseMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }
}
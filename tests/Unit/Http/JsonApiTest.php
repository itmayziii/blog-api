<?php

namespace Tests\Unit\Http;

use App\Http\JsonApi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;
use Mockery;
use Mockery\Mock;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Document\Link;
use Tests\TestCase;

class JsonApiTest extends TestCase
{
    /**
     * @var Response | Mock;
     */
    private $responseMock;
    /**
     * @var EncoderInterface | Mock
     */
    private $encoderMock;
    /**
     * @var JsonApi
     */
    private $jsonApi;

    public function setUp()
    {
        parent::setUp();
        $this->responseMock = Mockery::mock(Response::class);
        $this->encoderMock = Mockery::mock(EncoderInterface::class);
        $this->jsonApi = new JsonApi($this->encoderMock);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_respondResourceFound_encodes_content_and_returns_a_200_status_code()
    {
        $this->setupResponseMock(200);

        $this->encoderMock
            ->shouldReceive('encodeData')
            ->once()
            ->withArgs(['Encode This', null])
            ->andReturn('Encoded Data');

        $actualResult = $this->jsonApi->respondResourceFound($this->responseMock, 'Encode This');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_respondResourcesFound_encodes_content_and_returns_a_200_status_code()
    {
        $this->setupResponseMock(200);
        $paginatorMock = Mockery::mock(LengthAwarePaginator::class);
        $paginatorMock
            ->shouldReceive('url')
            ->once()
            ->withArgs([1])
            ->andReturn('https://test/1');

        $paginatorMock
            ->shouldReceive('lastPage')
            ->once()
            ->andReturn(11);

        $paginatorMock
            ->shouldReceive('url')
            ->once()
            ->withArgs([11])
            ->andReturn('https://test/11');

        $paginatorMock
            ->shouldReceive('previousPageUrl')
            ->once()
            ->andReturn(null);

        $paginatorMock
            ->shouldReceive('nextPageUrl')
            ->once()
            ->andReturn('https://test/2');

        $this->encoderMock
            ->shouldReceive('withLinks')
            ->once()
            ->withArgs([
                [
                    'first' => new Link('https://test/1', null, true),
                    'last'  => new Link('https://test/11', null, true),
                    'next'  => new Link('https://test/2', null, true)
                ]
            ])
            ->andReturn($this->encoderMock);

        $this->encoderMock
            ->shouldReceive('encodeData')
            ->once()
            ->withArgs([$paginatorMock])
            ->andReturn('Encoded Data');

        $actualResult = $this->jsonApi->respondResourcesFound($this->responseMock, $paginatorMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_respondResourceCreated_encodes_content_and_returns_a_201_status_code()
    {
        $this->setupResponseMock(201);

        $this->encoderMock
            ->shouldReceive('encodeData')
            ->once()
            ->withArgs(['Encode This'])
            ->andReturn('Encoded Data');

        $actualResult = $this->jsonApi->respondResourceCreated($this->responseMock, 'Encode This');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_respondImagesUploaded_encodes_content_and_returns_a_201_status_code()
    {
        $this->setupResponseMock(201);

        $this->encoderMock
            ->shouldReceive('encodeData')
            ->once()
            ->withArgs(['Encode This'])
            ->andReturn('Encoded Data');

        $actualResult = $this->jsonApi->respondImagesUploaded($this->responseMock, 'Encode This');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_respondResourceUpdated_encodes_content_and_returns_a_200_status_code()
    {
        $this->setupResponseMock(200);

        $this->encoderMock
            ->shouldReceive('encodeData')
            ->once()
            ->withArgs(['Encode This', null])
            ->andReturn('Encoded Data');

        $actualResult = $this->jsonApi->respondResourceUpdated($this->responseMock, 'Encode This');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_respondResourceDeleted_returns_a_204_status_code()
    {
        $this->responseMock
            ->shouldReceive('setStatusCode')
            ->once()
            ->withArgs([204])
            ->andReturn($this->responseMock);

        $actualResult = $this->jsonApi->respondResourceDeleted($this->responseMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_respondResourceNotFound_encodes_error_and_returns_a_404_status_code()
    {
        $this->setupResponseMock(404);

        $this->encoderMock
            ->shouldReceive('encodeError')
            ->once()
            ->andReturn('Encoded Data');

        $actualResult = $this->jsonApi->respondResourceNotFound($this->responseMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_respondUnauthorized_encodes_error_and_returns_401_status_code()
    {
        $this->setupResponseMock(401);

        $this->encoderMock
            ->shouldReceive('encodeError')
            ->once()
            ->andReturn('Encoded Data');

        $actualResult = $this->jsonApi->respondUnauthorized($this->responseMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_respondForbidden_encodes_error_and_returns_403_status_code()
    {
        $this->setupResponseMock(403);

        $this->encoderMock
            ->shouldReceive('encodeError')
            ->once()
            ->andReturn('Encoded Data');

        $actualResult = $this->jsonApi->respondForbidden($this->responseMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_respondValidationFailed_encodes_errors_and_returns_a_422_status_code()
    {
        $this->setupResponseMock(422);

        $messageBagMock = Mockery::mock(MessageBag::class);
        $messageBagMock
            ->shouldReceive('toArray')
            ->once()
            ->andReturn([]);

        $this->encoderMock
            ->shouldReceive('encodeError')
            ->once()
            ->andReturn('Encoded Data');

        $actualResult = $this->jsonApi->respondValidationFailed($this->responseMock, $messageBagMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_respondBadRequest_encodes_error_and_returns_a_400_status_code()
    {
        $this->setupResponseMock(400);

        $this->encoderMock
            ->shouldReceive('encodeError')
            ->once()
            ->andReturn('Encoded Data');

        $actualResult = $this->jsonApi->respondBadRequest($this->responseMock, 'An Error Occurred');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_respondBadRequest_encodes_multiple_errors_and_returns_a_400_status_code()
    {
        $this->setupResponseMock(400);

        $this->encoderMock
            ->shouldReceive('encodeErrors')
            ->once()
            ->andReturn('Encoded Data');

        $actualResult = $this->jsonApi->respondBadRequest($this->responseMock, []);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_respondServerError_encodes_error_and_returns_a_500_status_code()
    {
        $this->setupResponseMock(500);

        $this->encoderMock
            ->shouldReceive('encodeError')
            ->once()
            ->andReturn('Encoded Data');


        $actualResult = $this->jsonApi->respondServerError($this->responseMock, 'Error');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    private function setupResponseMock(int $statusCode)
    {
        $this->responseMock
            ->shouldReceive('setStatusCode')
            ->once()
            ->withArgs([$statusCode])
            ->andReturn($this->responseMock);

        $this->responseMock
            ->shouldReceive('withHeaders')
            ->once()
            ->withArgs([['Content-Type' => 'application/vnd.api+json']])
            ->andReturn($this->responseMock);

        $this->responseMock
            ->shouldReceive('setContent')
            ->once()
            ->withArgs(['Encoded Data'])
            ->andReturn($this->responseMock);
    }
}
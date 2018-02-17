<?php

namespace Tests\App\Http;

use App\Http\JsonApi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
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

    public function test_respondResourceFound_returns_encoded_content_and_a_200_status_code()
    {
        $this->responseMock
            ->shouldReceive('setStatusCode')
            ->once()
            ->withArgs([200])
            ->andReturn($this->responseMock);

        $this->responseMock
            ->shouldReceive('setContent')
            ->once()
            ->withArgs(['Encoded Data'])
            ->andReturn($this->responseMock);

        $this->encoderMock
            ->shouldReceive('encodeData')
            ->once()
            ->withArgs(['Encode This'])
            ->andReturn('Encoded Data');

        $actualResult = $this->jsonApi->respondResourceFound($this->responseMock, 'Encode This');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_respondResourcesFound_returns_encoded_content_and_a_200_status_code()
    {
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

        $this->responseMock
            ->shouldReceive('setStatusCode')
            ->once()
            ->withArgs([200])
            ->andReturn($this->responseMock);

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

        $this->responseMock
            ->shouldReceive('setContent')
            ->once()
            ->withArgs(['Encoded Data'])
            ->andReturn($this->responseMock);

        $actualResult = $this->jsonApi->respondResourcesFound($this->responseMock, $paginatorMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }
}
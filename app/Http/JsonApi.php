<?php

namespace App\Http;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Http\Response;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Document\Link;

class JsonApi
{
    /**
     * @var EncoderInterface
     */
    private $encoder;

    public function __construct(EncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function respondResourceFound(Response $response, $resource, EncodingParametersInterface $encodingParameters = null)
    {
        $content = $this->encoder->encodeData($resource, $encodingParameters);

        return $this->respond($response, Response::HTTP_OK, $content);
    }

    public function respondResourcesFound(Response $response, LengthAwarePaginator $paginator): Response
    {
        $firstUrl = $paginator->url(1);
        $lastUrl = $paginator->url($paginator->lastPage());
        $previousUrl = $paginator->previousPageUrl();
        $nextUrl = $paginator->nextPageUrl();

        $links = [];

        if (!is_null($firstUrl)) {
            $links['first'] = new Link($firstUrl, null, true);
        }

        if (!is_null($lastUrl)) {
            $links['last'] = new Link($lastUrl, null, true);
        }

        if (!is_null($previousUrl)) {
            $links['prev'] = new Link($previousUrl, null, true);
        }

        if (!is_null($nextUrl)) {
            $links['next'] = new Link($nextUrl, null, true);
        }

        $content = $this->encoder
            ->withLinks($links)
            ->encodeData($paginator);

        return $this->respond($response, Response::HTTP_OK, $content);
    }

    public function respondResourceCreated(Response $response, $resource): Response
    {
        $content = $this->encoder->encodeData($resource);

        return $this->respond($response, Response::HTTP_CREATED, $content);
    }

    public function respondResourceUpdated(Response $response, $resource): Response
    {
        return $this->respondResourceFound($response, $resource);
    }

    public function respondResourceDeleted(Response $response): Response
    {
        $response = $response->setStatusCode(204);

        return $response;
    }

    public function respondResourceNotFound(Response $response): Response
    {
        $error = new Error(null, null, Response::HTTP_NOT_FOUND, null, 'Not Found');
        $content = $this->encoder->encodeError($error);

        return $this->respond($response, Response::HTTP_NOT_FOUND, $content);
    }

    public function respondUnauthorized(Response $response): Response
    {
        $error = new Error(null, null, Response::HTTP_FORBIDDEN, null, 'Unauthorized');
        $content = $this->encoder->encodeError($error);

        return $this->respond($response, Response::HTTP_FORBIDDEN, $content);
    }

    public function respondValidationFailed(Response $response, MessageBag $messageBag): Response
    {
        $errors = [];
        foreach ($messageBag->toArray() as $errorField => $errorDetails) {
            foreach ($errorDetails as $errorDetail) {
                $errors[] = new Error(null, null, Response::HTTP_BAD_REQUEST, null, 'Bad Request', $errorDetail);
            }
        }

        $content = $this->encoder->encodeErrors($errors);

        return $this->respond($response, Response::HTTP_BAD_REQUEST, $content);
    }


    public function respondServerError(Response $response, $message): Response
    {
        $error = new Error(null, null, Response::HTTP_INTERNAL_SERVER_ERROR, 'null', 'Internal Server Error', $message);
        $content = $this->encoder->encodeError($error);

        return $this->respond($response, Response::HTTP_INTERNAL_SERVER_ERROR, $content);
    }

    private function respond(Response $response, int $statusCode, $content = null): Response
    {
        return $response->setStatusCode($statusCode)
            ->setContent($content)
            ->withHeaders([
                'Content-Type' => 'application/vnd.api+json'
            ]);
    }
}
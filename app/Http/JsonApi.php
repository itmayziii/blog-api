<?php

namespace App\Http;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Document\Link;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Http\Response;

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
        $response = $response
            ->setStatusCode(Response::HTTP_OK)
            ->setContent($this->encoder->encodeData($resource, $encodingParameters));

        return $response;
    }

    public function respondResourcesFound(Response $response, LengthAwarePaginator $paginator)
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

        $response = $response
            ->setStatusCode(Response::HTTP_OK)
            ->setContent($this->encoder
                ->withLinks($links)
                ->encodeData($paginator)
            );

        return $response;
    }

    public function respondResourceCreated(Response $response, $resource)
    {
        $response = $response
            ->setStatusCode(Response::HTTP_CREATED)
            ->setContent($this->encoder->encodeData($resource));

        return $response;
    }

    public function respondResourceUpdated(Response $response, $resource)
    {
        return $this->respondResourceFound($response, $resource);
    }

    public function respondResourceDeleted(Response $response)
    {
        $response = $response->setStatusCode(204);

        return $response;
    }

    public function respondResourceNotFound(Response $response)
    {
        $error = new Error(null, null, Response::HTTP_NOT_FOUND, null, 'Not Found');
        $response = $response
            ->setStatusCode(Response::HTTP_NOT_FOUND)
            ->setContent($this->encoder->encodeError($error));

        return $response;
    }

    public function respondUnauthorized(Response $response)
    {
        $error = new Error(null, null, Response::HTTP_FORBIDDEN, null, 'Unauthorized');
        $response = $response
            ->setStatusCode(Response::HTTP_FORBIDDEN)
            ->setContent($this->encoder->encodeError($error));

        return $response;
    }

    public function respondValidationFailed(Response $response, MessageBag $messageBag)
    {
        $errors = [];
        foreach ($messageBag->toArray() as $errorField => $errorDetails) {
            foreach ($errorDetails as $errorDetail) {
                $errors[] = new Error(null, null, Response::HTTP_BAD_REQUEST, null, 'Bad Request', $errorDetail);
            }
        }

        $response = $response
            ->setStatusCode(Response::HTTP_BAD_REQUEST)
            ->setContent($this->encoder->encodeErrors($errors));

        return $response;
    }


    public function respondServerError(Response $response, $message)
    {
        $error = new Error(null, null, Response::HTTP_INTERNAL_SERVER_ERROR, 'null', 'Internal Server Error', $message);
        $response = $response
            ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->setContent($this->encoder->encodeError($error));

        return $response;
    }
}
<?php

namespace App\Http;

use App\Post;
use App\Schemas\PostSchema;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Document\Link;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Encoder\EncoderOptions;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JsonApi
{
    private $schemas = [
        Post::class => PostSchema::class
    ];

    public function respondResourceFound(Request $request, Response $response, $resource)
    {
        $encoder = $this->createEncoder($request);

        $response = $response
            ->setStatusCode(Response::HTTP_OK)
            ->setContent($encoder->encodeData($resource));

        return $response;
    }

    public function respondResourcesFound(Request $request, Response $response, Model $model)
    {
        $encoder = $this->createEncoder($request);

        $size = $request->query('size', 15);
        $page = $request->query('page', 1);
        $resources = $model->where('status', 'draft')
            ->orderBy('created_at', 'desc')
            ->paginate($size, null, 'page', $page);

        $firstUrl = $resources->url(1);
        $lastUrl = $resources->url($resources->lastPage());
        $previousUrl = $resources->previousPageUrl();
        $nextUrl = $resources->nextPageUrl();

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
            ->setContent($encoder
                ->withLinks($links)
                ->encodeData($resources)
            );

        return $response;
    }

    public function respondResourceCreated(Request $request, Response $response, $resource)
    {
        $encoder = $this->createEncoder($request);
        $response = $response
            ->setStatusCode(Response::HTTP_CREATED)
            ->setContent($encoder->encodeData($resource));

        return $response;
    }

    public function respondResourceUpdated(Request $request, Response $response, $resource)
    {
        return $this->respondResourceFound($request, $response, $resource);
    }

    public function respondResourceDeleted(Request $request, Response $response)
    {
        $response = $response->setStatusCode(204);

        return $response;
    }

    public function respondResourceNotFound(Request $request, Response $response)
    {
        $encoder = $this->createEncoder($request);
        $error = new Error(null, null, Response::HTTP_NOT_FOUND, null, 'Not Found');

        $response = $response
            ->setStatusCode(Response::HTTP_NOT_FOUND)
            ->setContent($encoder->encodeError($error));

        return $response;
    }

    public function respondUnauthorized(Request $request, Response $response)
    {
        $encoder = $this->createEncoder($request);
        $error = new Error(null, null, Response::HTTP_FORBIDDEN, null, 'Unauthorized');
        $response = $response
            ->setStatusCode(Response::HTTP_FORBIDDEN)
            ->setContent($encoder->encodeError($error));

        return $response;
    }

    public function respondValidationFailed(Request $request, Response $response, MessageBag $messageBag)
    {
        $encoder = $this->createEncoder($request);

        $errors = [];
        foreach ($messageBag->toArray() as $errorField => $errorDetails) {
            foreach ($errorDetails as $errorDetail) {
                $errors[] = new Error(null, null, Response::HTTP_BAD_REQUEST, null, 'Bad Request', $errorDetail);
            }
        }

        $response = $response
            ->setStatusCode(Response::HTTP_BAD_REQUEST)
            ->setContent($encoder->encodeErrors($errors));

        return $response;
    }


    public function respondServerError(Request $request, Response $response, $message)
    {
        $encoder = $this->createEncoder($request);
        $error = new Error(null, null, Response::HTTP_INTERNAL_SERVER_ERROR, 'null', 'Internal Server Error', $message);

        $response = $response
            ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->setContent($encoder->encodeError($error));

        return $response;
    }

    private function createEncoder(Request $request)
    {
        $prettyPrintQueryString = $request->query('pretty');
        ($prettyPrintQueryString === 'false') ? $prettyPrintInt = 0 : $prettyPrintInt = JSON_PRETTY_PRINT;

        $encoder = Encoder::instance(
            $this->schemas,
            new EncoderOptions(JSON_UNESCAPED_SLASHES | $prettyPrintInt, env('API_URI') . '/v1'))
            ->withJsonApiVersion();

        return $encoder;
    }
}
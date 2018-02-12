<?php

namespace App\Http\Controllers;

use App\Post;
use App\Schemas\PostSchema;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller as BaseController;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Document\Link;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Encoder\EncoderOptions;

class Controller extends BaseController
{
    private $schemas = [
        Post::class => PostSchema::class
    ];

    /**
     * Resolve the validator from the service container with specific rules applied.
     *
     * @param Request $request
     * @param $rules
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function initializeValidation(Request $request, $rules)
    {
        $validator = $this->getValidationFactory();
        $validation = $validator->make($request->all(), $rules);

        return $validation;
    }

    protected function respondResourceFound(Request $request, Response $response, $resource)
    {
        $encoder = $this->createEncoder($request);

        return $response
            ->setStatusCode(Response::HTTP_OK)
            ->setContent($encoder->encodeData($resource));
    }

    protected function respondResourcesFound(Request $request, Response $response, Model $model)
    {
        $encoder = $this->createEncoder($request);

        $size = $request->query('size', 15);
        $page = $request->query('page', 1);
        $resources = $model::where('status', 'draft')
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

        return $response
            ->setStatusCode(Response::HTTP_OK)
            ->setContent($encoder
                ->withLinks($links)
                ->encodeData($resources)
            );
    }

    protected function respondResourceCreated(Request $request, Response $response, $resource)
    {
        $encoder = $this->createEncoder($request);

        return $response
            ->setStatusCode(Response::HTTP_CREATED)
            ->setContent($encoder->encodeData($resource));
    }

    protected function respondResourceUpdated(Request $request, Response $response, $resource)
    {
        return $this->respondResourceFound($request, $response, $resource);
    }

    protected function respondResourceDeleted(Request $request, Response $response)
    {
        return $response->setStatusCode(204);
    }

    protected function respondResourceNotFound(Request $request, Response $response)
    {
        $encoder = $this->createEncoder($request);
        $error = new Error(null, null, Response::HTTP_NOT_FOUND, null, 'Not Found');

        return $response
            ->setStatusCode(Response::HTTP_NOT_FOUND)
            ->setContent($encoder->encodeError($error));
    }

    protected function respondUnauthorized(Request $request, Response $response)
    {
        $encoder = $this->createEncoder($request);
        $error = new Error(null, null, Response::HTTP_FORBIDDEN, null, 'Unauthorized');
        return $response
            ->setStatusCode(Response::HTTP_FORBIDDEN)
            ->setContent($encoder->encodeError($error));
    }

    protected function respondValidationFailed(Request $request, Response $response, MessageBag $messageBag)
    {
        $encoder = $this->createEncoder($request);

        $errors = [];
        foreach ($messageBag->toArray() as $errorField => $errorDetails) {
            foreach ($errorDetails as $errorDetail) {
                $errors[] = new Error(null, null, Response::HTTP_BAD_REQUEST, null, 'Bad Request', $errorDetail);
            }
        }

        return $response
            ->setStatusCode(Response::HTTP_BAD_REQUEST)
            ->setContent($encoder->encodeErrors($errors));
    }


    protected function respondServerError(Request $request, Response $response, $message)
    {
        $encoder = $this->createEncoder($request);
        $error = new Error(null, null, Response::HTTP_INTERNAL_SERVER_ERROR, 'null', 'Internal Server Error', $message);

        return $response
            ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->setContent($encoder->encodeError($error));
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

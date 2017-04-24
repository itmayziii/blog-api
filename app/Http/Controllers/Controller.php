<?php

namespace App\Http\Controllers;

use App\ApiModel;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\Support\MessageBag;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @var Integer
     */
    private $statusCode;

    /**
     * @return Integer
     */
    public function getStatusCode()
    {
        return $this->statusCode;

    }

    /**
     * @param Integer $statusCode
     * @return Controller
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @param ApiModel $model
     * @return Response
     */
    public function respondResourceCreated(ApiModel $model)
    {
        $url = $model->getResourceUrl();
        $data = $this->createJsonApiResourceObject($model);

        return $this->setStatusCode(201)->respond(['data' => $data],
            [
                'Last-Modified' => $model->updated_at->format(DateTime::RFC850),
                'Location'      => $url
            ]
        );
    }

    /**
     * @param ApiModel $model
     * @return Response
     */
    public function respondResourceFound(ApiModel $model)
    {
        $data = $this->createJsonApiResourceObject($model);
        return $this->setStatusCode(200)->respond(['data' => $data]);
    }

    /**
     * @param ApiModel $model
     * @param Request $request
     * @return Response
     */
    public function respondResourcesFound(ApiModel $model, Request $request)
    {
        $requestPage = $request->query('page');
        $page = ($requestPage) ? $requestPage : 1;

        $requestSize = $request->query('size');
        $size = ($requestSize) ? $requestSize : 20;

        $paginator = $model::orderBy('created_at', 'desc')->paginate($size, null, 'page', $page);

        $prev = $paginator->previousPageUrl();
        $next = $paginator->nextPageUrl();
        $first = $paginator->url(1);
        $last = $paginator->url($paginator->lastPage());
        $data = [];

        foreach ($paginator->getCollection() as $model) {
            $data[] = $this->createJsonApiResourceObject($model);
        }

        return $this->setStatusCode(200)->respond([
            'links' => [
                'prev'  => $prev,
                'next'  => $next,
                'first' => $first,
                'last'  => $last
            ],
            'data'  => $data
        ]);
    }

    /**
     * @return Response
     */
    public function respondResourceNotFound()
    {
        $errors = [
            [
                'status' => 404,
                'title'  => 'Not Found',
                'detail' => 'Could not find the requested resource.'
            ]
        ];

        return $this->setStatusCode(404)->respond(['errors' => $errors]);
    }

    /**
     * @param MessageBag $messageBag
     * @return Response
     */
    public function respondValidationFailed(MessageBag $messageBag)
    {
        $failedFields = [];
        $failedFieldMessages = [];

        foreach ($messageBag->messages() as $key => $value) {
            $failedFields[] = $key;
            $failedFieldMessages[$key] = $value;
        }

        $errors = [
            'status' => 422,
            'title'  => 'Validation Failed',
            'detail' => 'Validation failed for the following input (' . implode(", ", $failedFields) . '), check the source member for more details.',
            'source' => $failedFieldMessages
        ];

        return $this->setStatusCode(422)->respond(['errors' => $errors]);
    }

    /**
     * @return Response
     */
    public function respondUnauthorized()
    {
        $errors = [
            'status' => 403,
            'title'  => 'Forbidden',
            'detail' => 'Authorization checks failed.'
        ];

        return $this->setStatusCode(403)->respond(['errors' => $errors]);
    }

    /**
     * @return Response
     */
    public function respondUnauthenticated()
    {
        $errors = [
            'status' => 401,
            'title'  => 'Unauthorized',
            'detail' => 'You are not authenticated, please try again once authenticated.'
        ];

        return $this->setStatusCode(401)->respond(['errors' => $errors]);
    }

    /**
     * @param $content
     * @param array $headers
     * @return Response
     */
    private function respond($content, $headers = [])
    {
        $response = new Response($content, $this->getStatusCode());

        $headers['Date'] = Carbon::now()->format(DateTime::RFC850);
        $headers['Content-Type'] = 'application/vnd.api+json';
        $response->withHeaders($headers);

        return $response;
    }

    /**
     * @param ApiModel $model
     * @return array
     */
    private function createJsonApiResourceObject(ApiModel $model)
    {
        $type = $model->getResourceName();
        $id = $model->getKey();
        $attributes = $model;
        $links = [
            'self' => $model->getResourceUrl()
        ];

        return [
            'type'       => $type,
            'id'         => $id,
            'attributes' => $attributes,
            'links'      => $links
        ];
    }

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
}

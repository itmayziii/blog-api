<?php

namespace App\Http\Controllers;

use App\ApiModel;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;
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

    public function respondResourceFound(ApiModel $model)
    {
        $data = $this->createJsonApiResourceObject($model);
        return $this->setStatusCode(200)->respond(['data' => $data]);
    }

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

    private function respond($content, $headers = [])
    {
        $response = new Response($content, $this->getStatusCode());

        $headers['Date'] = Carbon::now()->format(DateTime::RFC850);
        $headers['Content-Type'] = 'application/vnd.api+json';
        $response->withHeaders($headers);

        return $response;
    }

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

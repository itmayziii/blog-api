<?php

namespace App\Http\Controllers;

use App\ApiModel;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
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

        return $this->setStatusCode(201)->respondSuccessful($data,
            [
                'Last-Modified' => $model->updated_at->format(DateTime::RFC850),
                'Location'      => $url
            ]
        );
    }

    public function respondResourceFound(ApiModel $model)
    {
        $data = $this->createJsonApiResourceObject($model);
        return $this->setStatusCode(200)->respondSuccessful($data);
    }

    public function respondResourcesFound(LengthAwarePaginator $paginator)
    {
        $first = 1;
        $last = $paginator->lastPage();
        $prev = $paginator->previousPageUrl();
        $next = $paginator->nextPageUrl();
        $data = [];

        foreach ($paginator->getCollection() as $model) {
            $data[] = $this->createJsonApiResourceObject($model);
        }

        return $this->setStatusCode(200)->respond([
            'first' => $first,
            'last'  => $last,
            'prev'  => $prev,
            'next'  => $next,
            'data'  => $data
        ]);
    }

    public function respondResourceNotFound()
    {
        $errors = [
            [
                'status' => 404,
                'title'  => 'Not Found',
                'detail' => 'Could not find the requested resource'
            ]
        ];

        return $this->setStatusCode(404)->respondError($errors);
    }

    private function respond($content, $headers = [])
    {
        $response = new Response($content, $this->getStatusCode());

        $headers['Date'] = Carbon::now()->format(DateTime::RFC850);
        $headers['Content-Type'] = 'application/vnd.api+json';
        $response->withHeaders($headers);

        return $response;
    }

    private function respondSuccessful($data, $headers = [])
    {
        return $this->respond(['data' => $data], $headers);
    }

    private function respondError($errors, $headers = [])
    {
        return $this->respond(['errors' => $errors], $headers);
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
}

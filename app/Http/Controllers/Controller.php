<?php

namespace App\Http\Controllers;

use App\ApiModel;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Response;
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

    private function respond($data, $headers)
    {
        $response = new Response(['data' => $data], $this->getStatusCode());

        $headers['Date'] = Carbon::now()->format(DateTime::RFC850);
        $response->withHeaders($headers);

        return $response;
    }

    private function respondSuccessful($data, $headers = [])
    {
        $headers['Content-Type'] = 'application/vnd.api+json';
        return $this->respond($data, $headers);
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

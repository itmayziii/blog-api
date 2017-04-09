<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
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

    private function respondSuccessful($data, $headers = [])
    {
        $response = new Response(['data' => $data], $this->getStatusCode(), $headers);
        $response->header('Content-Type', 'application/json');
        return $response;
    }

    public function respondCreated($resourcePath, Model $model)
    {
        $keyName = $model->getRouteKeyName();
        $modelId = $model->$keyName;
        $path = env('APP_URL') . '/' . $resourcePath . '/' . $modelId;

        return $this->setStatusCode(201)->respondSuccessful(['created' => $path]);
    }
}

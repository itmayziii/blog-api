<?php

namespace App\Http\Controllers;

use App\Http\JsonApi;
use App\Repositories\WebPageRepository;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebPageController
{
    /**
     * @var WebPageRepository
     */
    private $webPageRepository;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Response
     */
    private $response;
    /**
     * @var JsonApi
     */
    private $jsonApi;
    /**
     * @var Gate
     */
    private $gate;
    /**
     * @var Guard
     */
    private $guard;

    public function __construct(Request $request, Response $response, JsonApi $jsonApi, WebPageRepository $webPageRepository, Gate $gate, Guard $guard)
    {
        $this->request = $request;
        $this->response = $response;
        $this->jsonApi = $jsonApi;
        $this->webPageRepository = $webPageRepository;
        $this->gate = $gate;
        $this->guard = $guard;
    }

    /**
     * Find a web page by the path
     *
     * @param string $path
     *
     * @return Response
     */
    public function show($path)
    {
        $webPage = $this->webPageRepository->findByPath($path);
        if ($webPage === false) {
            return $this->jsonApi->respondResourceNotFound($this->response);
        }

        if ($webPage->isLive() === false) {
            if ($this->guard->guest()) {
                return $this->jsonApi->respondUnauthorized($this->response);
            }

            if ($this->gate->denies('show', $webPage)) {
                return $this->jsonApi->respondForbidden($this->response);
            }
        }

        return $this->jsonApi->respondResourceFound($this->response, $webPage);
    }
}

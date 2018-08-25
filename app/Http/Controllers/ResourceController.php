<?php

namespace App\Http\Controllers;

use App\Contracts\ResourceInterface;
use App\Http\JsonApi;
use Error;
use Exception;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Guard;
use \Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Log\LoggerInterface;

class ResourceController
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Response
     */
    private $response;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var JsonApi
     */
    private $jsonApi;
    /**
     * @var Guard
     */
    private $guard;
    /**
     * @var Gate
     */
    private $gate;

    public function __construct(Request $request, Response $response, JsonApi $jsonApi, Config $config, LoggerInterface $logger, Guard $guard, Gate $gate)
    {
        $this->request = $request;
        $this->response = $response;
        $this->jsonApi = $jsonApi;
        $this->config = $config;
        $this->logger = $logger;
        $this->guard = $guard;
        $this->gate = $gate;
    }

    /**
     * List the paginated resources
     *
     * @param string $resourceUrlId
     *
     * @return Response
     */
    public function index($resourceUrlId)
    {
        $resource = $this->determineResource($resourceUrlId);

        if (is_null($resource) || !$resource instanceof ResourceInterface) {
            return $this->jsonApi->respondResourceNotFound($this->response);
        }

        $size = $this->request->query('size', 15);
        $page = $this->request->query('page', 1);

        $resourceObjects = $resource->findResourceObjects($page, $size);
        return $this->jsonApi->respondResourcesFound($this->response, $resourceObjects);
    }

    /**
     * Show a specific resource
     *
     * @param string $resourceUrlId
     * @param string|integer $id
     *
     * @return Response
     */
    public function show($resourceUrlId, $id)
    {
        $resource = $this->determineResource($resourceUrlId);

        if (is_null($resource) || !$resource instanceof ResourceInterface) {
            return $this->jsonApi->respondResourceNotFound($this->response);
        }

        $resourceObject = $resource->findResourceObject($id);
        if (is_null($resourceObject)) {
            return $this->jsonApi->respondResourceNotFound($this->response);
        }

        if ($resource->skipShowAuthentication($resourceObject)) {
            return $this->jsonApi->respondResourceFound($this->response, $resourceObject);
        }

        if ($this->guard->guest()) {
            return $this->jsonApi->respondUnauthorized($this->response);
        }

        if ($this->gate->denies('show', $resourceObject)) {
            return $this->jsonApi->respondForbidden($this->response);
        }

        return $this->jsonApi->respondResourceFound($this->response, $resourceObject);
    }

    /**
     * Create a resource
     *
     * @param string $resourceUrlId
     *
     * @return Response
     */
    public function store($resourceUrlId)
    {
        $resource = $this->determineResource($resourceUrlId);

        if (is_null($resource) || !$resource instanceof ResourceInterface) {
            return $this->jsonApi->respondResourceNotFound($this->response);
        }

        if (!$resource->skipStoreAuthentication()) {

            if ($this->guard->guest()) {
                return $this->jsonApi->respondUnauthorized($this->response);
            }

            if ($this->gate->denies('store', $resource->getResourceType())) {
                return $this->jsonApi->respondForbidden($this->response);
            }

        }

        $resourceObject = $resource->storeResourceObject($this->request->all());
        if (is_null($resourceObject)) {
            return $this->jsonApi->respondServerError($this->response, "Unable to create resource");
        }

        return $this->jsonApi->respondResourceCreated($this->response, $resourceObject);
    }

    private function determineResource($resourceUrlId)
    {
        $resources = $this->config->get('resources');

        if (isset($resources[$resourceUrlId])) {
            try {
                $resourceObject = app()->make($resources[$resourceUrlId]);
            } catch (Exception $exception) {
                $this->logger->error(ResourceController::class . ": Unable to create resource of class {$resources[$resourceUrlId]}");
                return null;
            }

            return $resourceObject;
        }

        $this->logger->debug(ResourceController::class . ": Unable to find a resource by name: {$resourceUrlId}");
        return null;
    }
}

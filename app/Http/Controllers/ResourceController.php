<?php

namespace App\Http\Controllers;

use App\Contracts\ResourceInterface;
use App\Http\JsonApi;
use Exception;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
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
    /**
     * @var ValidationFactory
     */
    private $validationFactory;

    public function __construct(
        Request $request,
        Response $response,
        JsonApi $jsonApi,
        Config $config,
        LoggerInterface $logger,
        Guard $guard,
        Gate $gate,
        ValidationFactory $validationFactory
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->jsonApi = $jsonApi;
        $this->config = $config;
        $this->logger = $logger;
        $this->guard = $guard;
        $this->gate = $gate;
        $this->validationFactory = $validationFactory;
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
        if (is_null($resource) || !$resource instanceof ResourceInterface || !in_array('index', $resource->getAllowedResourceActions())) {
            return $this->jsonApi->respondResourceNotFound($this->response);
        }

        if ($resource->requireIndexAuthorization()) {
            if ($this->guard->guest()) {
                return $this->jsonApi->respondUnauthorized($this->response);
            }

            if ($this->gate->denies('index', $resource->getResourceType())) {
                return $this->jsonApi->respondForbidden($this->response);
            }
        }

        $resourceObjects = $resource->findResourceObjects($this->request->query());
        return $this->jsonApi->respondResourcesFound($this->response, $resourceObjects);
    }

    /**
     * Show a specific resource
     *
     * @param string $resourceUrlId
     *
     * @return Response
     */
    public function show($resourceUrlId)
    {
        $resource = $this->determineResource($resourceUrlId);
        if (is_null($resource) || !$resource instanceof ResourceInterface || !in_array('show', $resource->getAllowedResourceActions())) {
            return $this->jsonApi->respondResourceNotFound($this->response);
        }

        $pathSegments = array_slice($this->request->segments(), 2);
        try {
            $resourceObject = $resource->findResourceObject($pathSegments, $this->request->query());
        } catch (Exception $exception) {
            $this->logger->error(ResourceController::class . ": unable to find resource with exception: {$exception->getMessage()}");
            return $this->jsonApi->respondServerError($this->response, "Unable to find resource");
        }

        if (is_null($resourceObject)) {
            return $this->jsonApi->respondResourceNotFound($this->response);
        }

        if ($resource->requireShowAuthorization($resourceObject)) {
            if ($this->guard->guest()) {
                return $this->jsonApi->respondUnauthorized($this->response);
            }

            if ($this->gate->denies('show', $resourceObject)) {
                return $this->jsonApi->respondForbidden($this->response);
            }
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
        if (is_null($resource) || !$resource instanceof ResourceInterface || !in_array('store', $resource->getAllowedResourceActions())) {
            return $this->jsonApi->respondResourceNotFound($this->response);
        }

        if ($resource->requireStoreAuthorization()) {
            if ($this->guard->guest()) {
                return $this->jsonApi->respondUnauthorized($this->response);
            }

            if ($this->gate->denies('store', $resource->getResourceType())) {
                return $this->jsonApi->respondForbidden($this->response);
            }
        }

        $requestData = $this->request->all();
        $validation = $this->validationFactory->make($requestData, $resource->getStoreValidationRules($requestData));
        if ($validation->fails()) {
            $this->logger->error(ResourceController::class . ": Unable to create {$resource->getResourceType()}, validation failed: " . print_r($validation->errors(), true));
            return $this->jsonApi->respondValidationFailed($this->response, $validation->getMessageBag());
        }

        try {
            $resourceObject = $resource->storeResourceObject($requestData, $this->guard->user());
        } catch (Exception $exception) {
            $this->logger->error(ResourceController::class . ": unable to store resource with exception: {$exception->getMessage()}");
            $resourceObject = null;
        }

        if (is_null($resourceObject)) {
            return $this->jsonApi->respondServerError($this->response, "Unable to create resource");
        }

        return $this->jsonApi->respondResourceCreated($this->response, $resourceObject);
    }

    /**
     * @param string $resourceUrlId
     *
     * @return Response
     */
    public function update($resourceUrlId)
    {
        $resource = $this->determineResource($resourceUrlId);
        if (is_null($resource) || !$resource instanceof ResourceInterface || !in_array('update', $resource->getAllowedResourceActions())) {
            return $this->jsonApi->respondResourceNotFound($this->response);
        }

        $pathSegments = array_slice($this->request->segments(), 2);
        try {
            $resourceObject = $resource->findResourceObject($pathSegments, $this->request->query());
        } catch (Exception $exception) {
            $this->logger->error(ResourceController::class . ": unable to update resource with exception: {$exception->getMessage()}");
            return $this->jsonApi->respondServerError($this->response, "Unable to update resource");
        }

        if (is_null($resourceObject)) {
            return $this->jsonApi->respondResourceNotFound($this->response);
        }

        if ($resource->requireUpdateAuthorization($resourceObject)) {
            if ($this->guard->guest()) {
                return $this->jsonApi->respondUnauthorized($this->response);
            }

            if ($this->gate->denies('store', $resourceObject)) {
                return $this->jsonApi->respondForbidden($this->response);
            }
        }

        $requestData = $this->request->all();
        $validation = $this->validationFactory->make($requestData, $resource->getUpdateValidationRules($resourceObject, $requestData));
        if ($validation->fails()) {
            $this->logger->error(ResourceController::class . ": unable to update {$resource->getResourceType()}, validation failed: " . print_r($validation->errors(), true));
            return $this->jsonApi->respondValidationFailed($this->response, $validation->getMessageBag());
        }

        try {
            $resourceObject = $resource->updateResourceObject($resourceObject, $requestData, $this->guard->user());
        } catch (Exception $exception) {
            $this->logger->error(ResourceController::class . ": unable to update resource with exception: {$exception->getMessage()}");
        }

        if (is_null($resourceObject)) {
            return $this->jsonApi->respondServerError($this->response, "Unable to update resource");
        }

        return $this->jsonApi->respondResourceUpdated($this->response, $resourceObject);
    }

    /**
     * @param string $resourceUrlId
     *
     * @return Response
     */
    public function delete($resourceUrlId)
    {
        $resource = $this->determineResource($resourceUrlId);
        if (is_null($resource) || !$resource instanceof ResourceInterface || !in_array('delete', $resource->getAllowedResourceActions())) {
            return $this->jsonApi->respondResourceNotFound($this->response);
        }

        $pathSegments = array_slice($this->request->segments(), 2);
        try {
            $resourceObject = $resource->findResourceObject($pathSegments, $this->request->query());
        } catch (Exception $exception) {
            $this->logger->error(ResourceController::class . ": unable to delete resource with exception: {$exception->getMessage()}");
            return $this->jsonApi->respondServerError($this->response, "Unable to delete resource");
        }

        if (is_null($resourceObject)) {
            return $this->jsonApi->respondResourceNotFound($this->response);
        }

        if ($resource->requireDeleteAuthorization($resourceObject)) {
            if ($this->guard->guest()) {
                return $this->jsonApi->respondUnauthorized($this->response);
            }

            if ($this->gate->denies('delete', $resourceObject)) {
                return $this->jsonApi->respondForbidden($this->response);
            }
        }

        try {
            $resourceDeleted = $resource->deleteResourceObject($resourceObject);
        } catch (Exception $exception) {
            $this->logger->error(ResourceController::class . ": unable to delete resource with exception: {$exception->getMessage()}");
            $resourceDeleted = false;
        }

        if ($resourceDeleted === false) {
            $this->jsonApi->respondServerError($this->response, 'Unable to delete resource');
        }

        return $this->jsonApi->respondResourceDeleted($this->response);
    }

    private function determineResource($resourceUrlId)
    {
        $resources = $this->config->get('resources');

        if (isset($resources[$resourceUrlId])) {
            try {
                $resourceObject = app()->make($resources[$resourceUrlId]);
            } catch (Exception $exception) {
                $this->logger->critical(ResourceController::class . ": unable to create resource of class {$resources[$resourceUrlId]} with exception {$exception->getMessage()}");
                return null;
            }

            return $resourceObject;
        }

        $this->logger->debug(ResourceController::class . ": unable to find a resource by name: {$resourceUrlId}");
        return null;
    }
}

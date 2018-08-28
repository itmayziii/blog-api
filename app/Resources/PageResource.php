<?php

use App\Contracts\ResourceInterface;
use App\Page;

class PageResource implements ResourceInterface
{
    public function __construct()
    {

    }

    /**
     * @inheritdoc
     */
    public function getResourceType()
    {
        return Page::class;
    }

    /**
     * @inheritdoc
     */
    public function getAllowedResourceActions()
    {
        return ['index', 'show', 'store', 'update', 'delete'];
    }

    /**
     * @inheritdoc
     */
    public function findResourceObject($id)
    {
        // TODO: Implement findResourceObject() method.
    }

    /**
     * @inheritdoc
     */
    public function findResourceObjects($page, $size)
    {
        // TODO: Implement findResourceObjects() method.
    }

    /**
     * @inheritdoc
     */
    public function storeResourceObject($attributes, \Illuminate\Contracts\Auth\Authenticatable $user = null)
    {
        // TODO: Implement storeResourceObject() method.
    }

    /**
     * @inheritdoc
     */
    public function updateResourceObject($resourceObject, $attributes, \Illuminate\Contracts\Auth\Authenticatable $user = null)
    {
        // TODO: Implement updateResourceObject() method.
    }

    /**
     * @inheritdoc
     */
    public function deleteResourceObject($resourceObject)
    {
        // TODO: Implement deleteResourceObject() method.
    }

    /**
     * @inheritdoc
     */
    public function getStoreValidationRules()
    {
        // TODO: Implement getStoreValidationRules() method.
    }

    /**
     * @inheritdoc
     */
    public function getUpdateValidationRules($resourceObject, $attributes)
    {
        // TODO: Implement getUpdateValidationRules() method.
    }

    public function requireIndexAuthorization()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function requireShowAuthorization($resourceObject)
    {
        // TODO: Implement requireShowAuthorization() method.
    }

    /**
     * @inheritdoc
     */
    public function requireStoreAuthorization()
    {
        // TODO: Implement requireStoreAuthorization() method.
    }

    /**
     * @inheritdoc
     */
    public function requireUpdateAuthorization($resourceObject)
    {
        // TODO: Implement requireUpdateAuthorization() method.
    }

    /**
     * @inheritdoc
     */
    public function requireDeleteAuthorization($resourceObject)
    {
        // TODO: Implement requireDeleteAuthorization() method.
    }
}

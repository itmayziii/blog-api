<?php

namespace App\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ResourceInterface
{
    /**
     * @return mixed
     */
    public function getResourceType();

    /**
     * @param string|integer $id
     *
     * @return mixed|null
     */
    public function findResourceObject($id);

    /**
     * @param string|integer $page
     * @param string|integer $size
     *
     * @return LengthAwarePaginator
     */
    public function findResourceObjects($page, $size);

    /**
     * @param array $attributes
     * @param Authenticatable $user
     *
     * @return mixed
     */
    public function storeResourceObject($attributes, Authenticatable $user = null);

    /**
     * @param mixed $resourceObject
     * @param array $attributes
     * @param Authenticatable $user
     *
     * @return mixed
     */
    public function updateResourceObject($resourceObject, $attributes, Authenticatable $user = null);

    /**
     * @param mixed $resourceObject
     *
     * @return boolean
     */
    public function deleteResourceObject($resourceObject);

    /**
     * @return array
     */
    public function getStoreValidationRules();

    /**
     * @param mixed $resourceObject
     * @param array $attributes
     *
     * @return array
     */
    public function getUpdateValidationRules($resourceObject, $attributes);

    /**
     * Determine if a resource needs authentication / authorization in order to show it
     *
     * @param mixed $resourceObject
     *
     * @return bool
     */
    public function requireShowAuthorization($resourceObject);

    /**
     * Determine if a resource needs authentication / authorization in order to create it
     *
     * @return bool
     */
    public function requireStoreAuthorization();

    /**
     * Determine if a resource needs authentication / authorization in order to delete it
     *
     * @param mixed $resourceObject
     *
     * @return mixed
     */
    public function requireUpdateAuthorization($resourceObject);
}

<?php

namespace App\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ResourceInterface
{
    /**
     * @return string
     */
    public function getResourceType(): string;

    /**
     * @return array
     */
    public function getAllowedResourceActions(): array;

    /**
     * @param string | integer $id
     *
     * @return mixed | bool
     */
    public function findResourceObject($id);

    /**
     * @param string|integer $page
     * @param string|integer $size
     *
     * @return LengthAwarePaginator
     */
    public function findResourceObjects($page, $size): LengthAwarePaginator;

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
    public function deleteResourceObject($resourceObject): bool;

    /**
     * @return array
     */
    public function getStoreValidationRules(): array;

    /**
     * @param mixed $resourceObject
     * @param array $attributes
     *
     * @return array
     */
    public function getUpdateValidationRules($resourceObject, $attributes): array;

    /**
     * Determine if a resource needs authentication / authorization in order to index it
     *
     * @return bool
     */
    public function requireIndexAuthorization(): bool;

    /**
     * Determine if a resource needs authentication / authorization in order to show it
     *
     * @param mixed $resourceObject
     *
     * @return bool
     */
    public function requireShowAuthorization($resourceObject): bool;

    /**
     * Determine if a resource needs authentication / authorization in order to create it
     *
     * @return bool
     */
    public function requireStoreAuthorization(): bool;

    /**
     * Determine if a resource needs authentication / authorization in order to update it
     *
     * @param mixed $resourceObject
     *
     * @return bool
     */
    public function requireUpdateAuthorization($resourceObject): bool;

    /**
     * Determine if a resource needs authentication / authorization in order to delete it
     *
     * @param mixed $resourceObject
     *
     * @return bool
     */
    public function requireDeleteAuthorization($resourceObject): bool;
}

<?php

namespace App\Contracts;

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
     *
     * @return mixed
     */
    public function storeResourceObject($attributes);

    /**
     * Determine if a resource needs authentication / authorization in order to show it
     *
     * @param mixed $resource
     *
     * @return bool
     */
    public function skipShowAuthentication($resource);

    /**
     * Determine if a resource needs authentication / authorization in order to create it
     *
     * @return bool
     */
    public function skipStoreAuthentication();
}

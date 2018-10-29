<?php

namespace App\Resources;

use Illuminate\Support\Arr;

abstract class BaseResource
{
    const SHOW_ACTION = 'show';
    const SHOW_RESOURCE_IDENTIFIER_ACTION = 'showResourceIdentifiers';
    const SHOW_RELATED_RESOURCE_ACTION = 'showRelatedResource';
    const INDEX_ACTION = 'index';
    const STORE_ACTION = 'store';
    const UPDATE_ACTION = 'update';
    const DELETE_ACTION = 'delete';

    /**
     * @param array $queryParams
     * @param string $included
     *
     * @return bool
     */
    protected function isRelationshipIncluded($queryParams, $included)
    {
        $includedRelationshipsString = Arr::get($queryParams, 'included');
        if (is_null($includedRelationshipsString)) {
            return false;
        }

        $includedRelationships = explode(',', $includedRelationshipsString);
        return in_array($included, $includedRelationships);
    }
}

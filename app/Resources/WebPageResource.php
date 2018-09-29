<?php

namespace App\Resources;

use App\Contracts\ResourceInterface;
use App\Repositories\WebPageRepository;
use App\WebPage;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class WebPageResource implements ResourceInterface
{
    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var WebPageRepository
     */
    private $webPageRepository;
    /**
     * @var Gate
     */
    private $gate;

    public function __construct(Cache $cache, WebPageRepository $webPageRepository, Gate $gate)
    {
        $this->cache = $cache;
        $this->webPageRepository = $webPageRepository;
        $this->gate = $gate;
    }

    /**
     * @inheritdoc
     */
    public function getResourceType(): string
    {
        return WebPage::class;
    }

    /**
     * @inheritdoc
     */
    public function getAllowedResourceActions(): array
    {
        return ['index', 'show', 'store', 'update', 'delete'];
    }

    /**
     * @inheritdoc
     */
    public function findResourceObject($urlSegments, $queryParams)
    {
        if (count($urlSegments) !== 2) {
            return false;
        }
        [$type, $slug] = $urlSegments;
        return $this->webPageRepository->findBySlug($type, $slug);
    }

    /**
     * @inheritdoc
     */
    public function findResourceObjects($queryParams): LengthAwarePaginator
    {
        $type = Arr::get($queryParams, 'type', '');
        $page = Arr::get($queryParams, 'page', 1);
        $size = Arr::get($queryParams, 'size', 15);

        $isAllowedToIndexAllPosts = $this->gate->allows('indexAllWebPages', WebPage::class);
        return $this->webPageRepository->paginateWebPages($page, $size, $type, !$isAllowedToIndexAllPosts);
    }

    /**
     * @inheritdoc
     */
    public function storeResourceObject($attributes, Authenticatable $user = null)
    {
        return $this->webPageRepository->create($attributes, $user);
    }

    /**
     * @inheritdoc
     */
    public function updateResourceObject($resourceObject, $attributes, Authenticatable $user = null)
    {
        $this->webPageRepository->update($resourceObject, $attributes, $user);
    }

    /**
     * @inheritdoc
     */
    public function deleteResourceObject($resourceObject): bool
    {
        return $this->webPageRepository->delete($resourceObject);
    }

    /**
     * @inheritdoc
     */
    public function getStoreValidationRules($attributes): array
    {
        return [
            'category_id'       => 'integer|exists:categories,id',
            'slug'              => "required|max:255|composite_unique:webpages,type_id,{$attributes['type_id']}",
            'type_id'           => 'required|integer|exists:webpage_types,id',
            'is_live'           => 'required|boolean',
            'title'             => 'required|max:255',
            'modules'           => 'array',
            'short_description' => 'max:1000',
            'image_path_sm'     => 'max:255',
            'image_path_md'     => 'max:255',
            'image_path_lg'     => 'max:255',
            'image_path_meta'   => 'max:255'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getUpdateValidationRules($resourceObject, $attributes): array
    {
        $validationRules = $this->getStoreValidationRules($attributes);

        // Removing the unique validation on some fields if they have not changed
        if ($resourceObject->getAttribute('slug') === $attributes['slug'] && $resourceObject->getAttribute('type_id') === $attributes['type_id']) {
            $validationRules['slug'] = 'required|max:255';
        }

        return $validationRules;
    }

    public function requireIndexAuthorization(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function requireShowAuthorization($resourceObject): bool
    {
        return $resourceObject->isLive() === false;
    }

    /**
     * @inheritdoc
     */
    public function requireStoreAuthorization(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function requireUpdateAuthorization($resourceObject): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function requireDeleteAuthorization($resourceObject): bool
    {
        return true;
    }
}

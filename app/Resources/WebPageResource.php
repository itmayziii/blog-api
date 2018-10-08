<?php

namespace App\Resources;

use App\Contracts\ResourceInterface;
use App\Repositories\WebPageRepository;
use App\Models\WebPage;
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
        if (count($urlSegments) === 1) {
            [$id] = $urlSegments;
            if (!is_numeric($id)) {
                return null;
            }

            return $this->webPageRepository->findById($id);
        }

        if (count($urlSegments) !== 2) {
            return null;
        }

        [$categorySlug, $slug] = $urlSegments;
        return $this->webPageRepository->findBySlug($categorySlug, $slug);
    }

    /**
     * @inheritdoc
     */
    public function findResourceObjects($queryParams): LengthAwarePaginator
    {
        $type = Arr::get($queryParams, 'type', null);
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
        return $this->webPageRepository->update($resourceObject, $attributes, $user);
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
        $validationRules = [
            'category_id'       => 'required|integer|exists:categories,id',
            'slug'              => "required|max:255|alpha_dash",
            'is_live'           => 'required|boolean',
            'title'             => 'required|max:255',
            'modules'           => 'array',
            'short_description' => 'max:1000',
            'image_path_sm'     => 'max:255',
            'image_path_md'     => 'max:255',
            'image_path_lg'     => 'max:255',
            'image_path_meta'   => 'max:255'
        ];

        if (isset($attributes['category_id'])) {
            $validationRules['slug'] = $validationRules['slug'] .= "|composite_unique:webpages,category_id,{$attributes['category_id']}";
        }

        return $validationRules;
    }

    /**
     * @inheritdoc
     */
    public function getUpdateValidationRules($resourceObject,
        $attributes): array
    {
        $validationRules = $this->getStoreValidationRules($attributes);

        // Removing the unique validation on some fields if they have not changed
        $newSlug = Arr::get($attributes, 'slug');
        $newCategoryId = Arr::get($attributes, 'category_id');
        if ($resourceObject->getAttribute('slug') === $newSlug && $resourceObject->getAttribute('category_id') === $newCategoryId) {
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

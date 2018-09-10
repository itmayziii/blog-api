<?php

namespace App\Resources;

use App\Contracts\ResourceInterface;
use App\Repositories\WebPageRepository;
use App\WebPage;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
    public function findResourceObject($id)
    {
        return $this->webPageRepository->findById($id);
    }

    /**
     * @inheritdoc
     */
    public function findResourceObjects($page, $size): LengthAwarePaginator
    {
        $isAllowedToIndexAllPosts = $this->gate->allows('indexAllWebPages', WebPage::class);
        return $isAllowedToIndexAllPosts ? $this->webPageRepository->pageinateAllWebPages($page, $size) : $this->webPageRepository->paginateLiveWebPages($page, $size);
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
    public function getStoreValidationRules(): array
    {
        return [
            'category_id' => 'required',
            'title'       => 'required|max:200|unique:webpages',
            'is_live'     => 'required|boolean',
            'slug'        => 'required|max:255|unique:webpages',
            'content'     => 'max:10000'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getUpdateValidationRules($resourceObject, $attributes): array
    {
        $validationRules = [
            'category_id' => 'required',
            'title'       => 'required|max:200',
            'is_live'     => 'required|boolean',
            'slug'        => 'required|max:255|unique:webpages',
            'content'     => 'max:10000'
        ];

        // Removing the unique validation on some fields if they have not changed
        if (isset($attributes['slug']) && $resourceObject->getAttribute('slug') === $attributes['slug']) {
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

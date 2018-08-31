<?php

namespace App\Resources;

use App\Contracts\ResourceInterface;
use App\Post;
use App\Repositories\PostRepository;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PostResource implements ResourceInterface
{
    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var PostRepository
     */
    private $postRepository;
    /**
     * @var Gate
     */
    private $gate;

    public function __construct(Cache $cache, PostRepository $postRepository, Gate $gate)
    {
        $this->cache = $cache;
        $this->postRepository = $postRepository;
        $this->gate = $gate;
    }

    /**
     * @inheritdoc
     */
    public function getResourceType(): string
    {
        return Post::class;
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
    public function findResourceObject($slug)
    {
        return $this->postRepository->findBySlug($slug);
    }

    /**
     * @inheritdoc
     */
    public function findResourceObjects($page, $size): LengthAwarePaginator
    {
        $isAllowedToIndexAllPosts = $this->gate->allows('indexAllPosts', app()->make(Post::class));
        return $isAllowedToIndexAllPosts ? $this->postRepository->paginateAllPosts($page, $size) : $this->postRepository->paginateLivePosts($page, $size);
    }

    /**
     * @inheritdoc
     */
    public function storeResourceObject($attributes, Authenticatable $user = null)
    {
        return $this->postRepository->create($attributes, $user);
    }

    /**
     * @inheritdoc
     */
    public function updateResourceObject($resourceObject, $attributes, Authenticatable $user = null)
    {
        $this->postRepository->update($resourceObject, $attributes, $user);
    }

    /**
     * @inheritdoc
     */
    public function deleteResourceObject($resourceObject): bool
    {
        return $this->postRepository->delete($resourceObject);
    }

    /**
     * @inheritdoc
     */
    public function getStoreValidationRules(): array
    {
        return [
            'category-id' => 'required',
            'title'       => 'required|max:200|unique:posts',
            'status'      => 'required',
            'slug'        => 'required|max:255|unique:posts',
            'content'     => 'max:10000'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getUpdateValidationRules($resourceObject, $attributes): array
    {
        $validationRules = [
            'category-id' => 'required',
            'title'       => 'required|max:200',
            'status'      => 'required',
            'slug'        => 'required|max:255',
            'content'     => 'max:10000'
        ];

        // Removing the unique validation on some fields if they have not changed
        if (isset($attributes['slug']) && $resourceObject->getAttribute('slug') === $attributes['slug']) {
            $validationRules['slug'] = 'required|max:255';
        }
        if (isset($attributes['title']) && $resourceObject->getAttribute('title') === $attributes['title']) {
            $validationRules['title'] = 'required|max:200';
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

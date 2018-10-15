<?php

namespace App\Repositories;

use App\Models\Tag;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Psr\Log\LoggerInterface;

class TagRepository
{
    /**
     * @var Tag | Builder
     */
    private $tag;
    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Tag $contact, Cache $cache, LoggerInterface $logger)
    {
        $this->tag = $contact;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * @param int $page
     * @param int $size
     *
     * @return LengthAwarePaginator
     */
    public function paginate($page, $size)
    {
        $cacheKey = "tags:page.$page:size.$size";
        return $this->cache->remember($cacheKey, 60, function () use ($page, $size) {
            return $this->tag
                ->orderBy('updated_at', 'desc')
                ->paginate($size, null, 'page', $page);
        });
    }

    /**
     * @param string | int $id
     *
     * @return Tag | null
     */
    public function findById($id)
    {
        $tag = $this->cache->remember("tag:id.$id", 60, function () use ($id) {
            return $this->tag->find($id);
        });

        if (is_null($tag)) {
            $this->logger->notice(TagRepository::class . ": unable to find tag with id: {$id}");
            return null;
        }

        return $tag;
    }

    /**
     * @param string $slug
     *
     * @return Tag | null
     */
    public function findBySlug($slug)
    {
        $tag = $this->cache->remember("tag:slug.$slug", 60, function () use ($slug) {
            return $this->tag
                ->where('slug', $slug)
                ->first();
        });

        if (is_null($tag)) {
            $this->logger->notice(TagRepository::class . ": unable to find tag with slug: {$slug}");
            return null;
        }

        return $tag;
    }

//    /**
//     * @param array $attributes
//     *
//     * @return Contact | null
//     */
//    public function create($attributes)
//    {
//        $attributes = $this->mapAttributes($attributes);
//        try {
//            $contact = $this->contact->create($attributes);
//        } catch (Exception $exception) {
//            $this->logger->error(ContactRepository::class . ": unable to create contact with exception: {$exception->getMessage()}");
//            return null;
//        }
//
//        $this->deleteContactCache();
//        return $contact;
//    }

//    /**
//     * @param array $attributes
//     *
//     * @return array
//     */
//    private function mapAttributes($attributes)
//    {
//        return [
//            'first_name' => isset($attributes['first_name']) ? $attributes['first_name'] : null,
//            'last_name'  => isset($attributes['last_name']) ? $attributes['last_name'] : null,
//            'email'      => isset($attributes['email']) ? $attributes['email'] : null,
//            'comments'   => isset($attributes['comments']) ? $attributes['comments'] : null
//        ];
//    }
//
//    /**
//     * @return void
//     */
//    private function deleteContactCache()
//    {
//        try {
//            $laravelCachePrefix = $this->cache->getPrefix();
//            $contactCacheKeys = $this->cache->connection()->keys($laravelCachePrefix . 'contact*');
//            $this->cache->connection()->del($contactCacheKeys);
//        } catch (Exception $exception) {
//            $this->logger->error(ContactRepository::class . ": unable to delete contact cache with exception {$exception->getMessage()}");
//        }
//    }
}

<?php

namespace App\Repositories;

use App\Models\Contact;
use Exception;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Container\Container;
use Psr\Log\LoggerInterface;

class ContactRepository
{
    /**
     * @var Container
     */
    private $container;
    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Container $container, Cache $cache, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function paginate($page, $size)
    {
        $cacheKey = "contacts:page.$page:size.$size";
        return $this->cache->remember($cacheKey, 60, function () use ($page, $size) {
            $contactBuilder = $this->container->make(Contact::class);
            return $contactBuilder
                ->orderBy('created_at', 'desc')
                ->paginate($size, null, 'page', $page);
        });
    }

    /**
     * @param string | int $id
     *
     * @return Contact | null
     */
    public function findById($id)
    {
        $contact = $this->cache->remember("contact:id.$id", 60, function () use ($id) {
            $contactBuilder = $this->container->make(Contact::class);
            return $contactBuilder->find($id);
        });

        return $contact;
    }

    /**
     * @param array $attributes
     *
     * @return Contact | null
     */
    public function create($attributes)
    {
        $attributes = $this->mapAttributes($attributes);
        try {
            $contactBuilder = $this->container->make(Contact::class);
            $contact = $contactBuilder->create($attributes);
        } catch (Exception $exception) {
            $this->logger->error(ContactRepository::class . ": unable to create contact with exception: {$exception->getMessage()}");
            return null;
        }

        $this->deleteContactCache();
        return $contact;
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    private function mapAttributes($attributes)
    {
        return [
            'first_name' => isset($attributes['first_name']) ? $attributes['first_name'] : null,
            'last_name'  => isset($attributes['last_name']) ? $attributes['last_name'] : null,
            'email'      => isset($attributes['email']) ? $attributes['email'] : null,
            'comments'   => isset($attributes['comments']) ? $attributes['comments'] : null
        ];
    }

    /**
     * @return void
     */
    private function deleteContactCache()
    {
        try {
            $laravelCachePrefix = $this->cache->getPrefix();
            $contactCacheKeys = $this->cache->connection()->keys($laravelCachePrefix . 'contact*');
            if (empty($contactCacheKeys)) {
                return;
            }
            $this->cache->connection()->del($contactCacheKeys);
        } catch (Exception $exception) {
            $this->logger->error(ContactRepository::class . ": unable to delete contact cache with exception {$exception->getMessage()}");
        }
    }
}

<?php

namespace App\Repositories;

use App\Models\Contact;
use Exception;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\Eloquent\Builder;
use Psr\Log\LoggerInterface;

class ContactRepository
{
    /**
     * @var Contact | Builder
     */
    private $contact;
    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Contact $contact, Cache $cache, LoggerInterface $logger)
    {
        $this->contact = $contact;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function paginate($page, $size)
    {
        $cacheKey = "contacts:page.$page:size.$size";
        return $this->cache->remember($cacheKey, 60, function () use ($page, $size) {
            return $this->contact
                ->orderBy('updated_at', 'desc')
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
            return $this->contact->find($id);
        });

        if (is_null($contact)) {
            $this->logger->notice(ContactRepository::class . ": unable to find contact with id: {$id}");
            return null;
        }

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
            $contact = $this->contact->create($attributes);
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

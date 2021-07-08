<?php

namespace App\Repositories;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Psr\Log\LoggerInterface;

class UserRepository
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
    /**
     * @var Hasher
     */
    private $hasher;

    public function __construct(Container $container, Cache $cache, LoggerInterface $logger, Hasher $hasher)
    {
        $this->container = $container;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->hasher = $hasher;
    }

    /**
     * @param int $page
     * @param int $size
     *
     * @return LengthAwarePaginator
     */
    public function paginate($page, $size)
    {
        $cacheKey = "users:page.$page:size.$size";
        return $this->cache->remember($cacheKey, 60, function () use ($page, $size) {
            $userBuilder = $this->container->make(User::class);
            return $userBuilder
                ->orderBy('created_at', 'desc')
                ->paginate($size, null, 'page', $page);
        });
    }

    /**
     * @param string | int $id
     *
     * @return User | null
     */
    public function findById($id)
    {
        $user = $this->cache->remember("user:id.$id", 60, function () use ($id) {
            $userBuilder = $this->container->make(User::class);
            return $userBuilder->find($id);
        });

        return $user;
    }

    /**
     * @param string $email
     *
     * @return User | null
     */
    public function findByEmail($email)
    {
        $user = $this->cache->remember("user:email.$email", 60, function () use ($email) {
            $userBuilder = $this->container->make(User::class);
            return $userBuilder
                ->where('email', $email)
                ->first();
        });

        return $user;
    }

    /**
     * @param string $apiToken
     *
     * @return User | null
     */
    public function findByApiToken($apiToken)
    {
        $user = $this->cache->remember("user:token.$apiToken", 60, function () use ($apiToken) {
            $userBuilder = $this->container->make(User::class);
            return $userBuilder
                ->where('api_token', $apiToken)
                ->first();
        });

        return $user;
    }

    /**
     * @param array $attributes
     *
     * @return User | null
     */
    public function create($attributes)
    {
        $attributes = $this->mapAttributes($attributes);
        $attributes['api_token'] = sha1(str_random());
        $attributes['api_token_expiration'] = Carbon::now()->addDay();

        try {
            $userBuilder = $this->container->make(User::class);
            $user = $userBuilder->create($attributes);
        } catch (Exception $exception) {
            $this->logger->error(UserRepository::class . ": unable to create user with exception: {$exception->getMessage()}");
            return null;
        }

        $this->deleteUserCache();
        return $user;
    }

    /**
     * @param User $user
     * @param $attributes
     *
     * @return User
     */
    public function update(User $user, $attributes)
    {
        $attributes = $this->mapAttributes($attributes);
        try {
            $user->update($attributes);
        } catch (Exception $exception) {
            $this->logger->error(UserRepository::class . ": unable to update user with exception: {$exception->getMessage()}");
            return null;
        }

        $this->deleteUserCache();
        return $user;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $user)
    {
        try {
            $user->delete();
        } catch (Exception $exception) {
            $this->logger->error(UserRepository::class . ": unable to delete user with with exception: {$exception->getMessage()}");
            return false;
        }

        $this->deleteUserCache();
        return true;
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
            'password'   => isset($attributes['password']) ? $this->hasher->make($attributes['password']) : null,
            'api_limit'  => isset($attributes['api_limit']) ? $attributes['api_limit'] : 1000,
            'role'       => isset($attributes['role']) ? $attributes['role'] : null,
        ];
    }

    /**
     * @return void
     */
    private function deleteUserCache()
    {
        try {
            $laravelCachePrefix = $this->cache->getPrefix();
            $userCacheKeys = $this->cache->connection()->keys($laravelCachePrefix . 'user*');
            if (empty($userCacheKeys)) {
                return;
            }
            $this->cache->connection()->del($userCacheKeys);
        } catch (Exception $exception) {
            $this->logger->error(UserRepository::class . ": unable to delete user cache with exception {$exception->getMessage()}");
        }
    }
}

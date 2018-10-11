<?php

namespace App\Repositories;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Psr\Log\LoggerInterface;

class UserRepository
{
    /**
     * @var User | Builder
     */
    private $user;
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

    public function __construct(User $user, Cache $cache, LoggerInterface $logger, Hasher $hasher)
    {
        $this->user = $user;
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
            return $this->user
                ->orderBy('updated_at', 'desc')
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
            return $this->user->find($id);
        });

        if (is_null($user)) {
            $this->logger->notice(UserRepository::class . ": unable to find user with id: {$id}");
            return null;
        }

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
            return $this->user
                ->where('email', $email)
                ->first();
        });

        if (is_null($user)) {
            $this->logger->notice(UserRepository::class . ": unable to find user with email: {$email}");
            return null;
        }

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
            return $this->user
                ->where('api_token', $apiToken)
                ->first();
        });

        if (is_null($user)) {
            $this->logger->notice(UserRepository::class . ": unable to find user with token: {$apiToken}");
            return null;
        }

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
            $user = $this->user->create($attributes);
        } catch (Exception $exception) {
            $this->logger->error(UserRepository::class . ": unable to create user with exception: {$exception->getMessage()}");
            return null;
        }

        $this->cache->clear();
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

        $this->cache->clear();
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

        $this->cache->clear();
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
}

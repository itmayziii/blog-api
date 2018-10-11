<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Cache\Repository as Cache;
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

    public function __construct(User $user, Cache $cache, LoggerInterface $logger)
    {
        $this->user = $user;
        $this->cache = $cache;
        $this->logger = $logger;
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
}

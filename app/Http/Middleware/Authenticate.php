<?php

namespace App\Http\Middleware;

use App\Http\JsonApi;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;
    /**
     * @var JsonApi
     */
    private $jsonApi;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
        $this->jsonApi = app()->make(JsonApi::class);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($this->auth->guard($guard)->guest()) {
            return $this->jsonApi->respondUnauthorized($next($request));
        }

        $currentUser = $this->auth->guard($guard)->user();
        if (is_null($currentUser->getAttribute('api_token_expiration')) || is_null($currentUser->getAttribute('api_token'))) {
            return $this->jsonApi->respondUnauthorized($next($request));
        }

        $apiTokenExpiration = strtotime($currentUser->getAttribute('api_token_expiration'));
        $now = (new \DateTime())->getTimestamp();
        if ($now > $apiTokenExpiration) {
            return $this->jsonApi->respondUnauthorized($next($request));
        }

        return $next($request);
    }
}

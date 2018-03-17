<?php

namespace App\Http\Controllers;

use App\Http\JsonApi;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Cookie;

class AuthenticateController
{
    const API_TOKEN_NAME = 'API-Token';
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var JsonApi
     */
    private $jsonApi;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Hasher
     */
    private $hasher;
    /**
     * @var ConfigRepository
     */
    private $configRepository;

    public function __construct(JsonApi $jsonApi, UserRepository $userRepository, LoggerInterface $logger, Hasher $hasher, ConfigRepository $configRepository)
    {
        $this->userRepository = $userRepository;
        $this->jsonApi = $jsonApi;
        $this->logger = $logger;
        $this->hasher = $hasher;
        $this->configRepository = $configRepository;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param Carbon $carbon
     *
     * @return Response
     */
    public function authenticate(Request $request, Response $response, Carbon $carbon)
    {
        $authorizationHeader = $request->header('Authorization');

        $authorizationHeaderErrors = $this->validateAuthorizationHeader($authorizationHeader);
        if (!empty($authorizationHeaderErrors)) {
            return $this->jsonApi->respondBadRequest($response, $authorizationHeaderErrors);
        }

        $splitAuthorizationHeader = explode(' ', $authorizationHeader);
        $splitCredentials = explode(':', base64_decode($splitAuthorizationHeader[1]));
        $username = $splitCredentials[0];
        $password = $splitCredentials[1];

        $user = $this->userRepository->retrieveUserByEmail($username);
        if (is_null($user)) {
            return $this->jsonApi->respondResourceNotFound($response);
        }

        if (!$this->hasher->check($password, $user->getAttribute('password'))) {
            return $this->jsonApi->respondUnauthorized($response);
        }

        $apiToken = sha1(str_random());
        $oneDayInTheFuture = $carbon->copy()->addDay();
        try {
            $user->update([
                'api_token'            => $apiToken,
                'api_token_expiration' => $oneDayInTheFuture
            ]);
        } catch (Exception $e) {
            $this->logger->error("Failed to update user with API Token with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, 'Unable to save user with new token.');
        }

        $cookiesConfig = $this->configRepository->get('cookies');
        $response = $this->jsonApi->respondResourceCreated($response, $user);
        $cookie = new Cookie(self::API_TOKEN_NAME, $apiToken, $oneDayInTheFuture, '/', $cookiesConfig['domain'], $cookiesConfig['secure'], $cookiesConfig['http_only']);

        return $response->withCookie($cookie);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param Carbon $carbon
     *
     * @return Response
     */
    public function validateToken(Request $request, Response $response, Carbon $carbon)
    {
        $apiToken = $request->hasHeader(self::API_TOKEN_NAME) ? $request->header(self::API_TOKEN_NAME) : $request->cookie(self::API_TOKEN_NAME);
        if (is_null($apiToken)) {
            $apiTokenName = self::API_TOKEN_NAME;
            return $this->jsonApi->respondBadRequest($response, "Neither $apiTokenName header or cookie is set.");
        }

        $user = $this->userRepository->retrieveUserByToken($apiToken);
        if (is_null($user)) {
            return $this->jsonApi->respondUnauthorized($response);
        }

        $tokenExpiration = strtotime($user->getAttribute('api_token_expiration'));
        $now = $carbon->getTimestamp();
        if ($now > $tokenExpiration) {
            return $this->jsonApi->respondUnauthorized($response);
        }

        return $this->jsonApi->respondResourceFound($response, $user);
    }

    public function logout()
    {

    }

    /**
     * @param string $authorizationHeader
     *
     * @return array
     */
    private function validateAuthorizationHeader($authorizationHeader)
    {
        $splitAuthorizationHeader = explode(' ', $authorizationHeader);
        if (!key_exists(0, $splitAuthorizationHeader) || !key_exists(1, $splitAuthorizationHeader)) {
            return ['Authorization header must have a type and value defined.'];
        }

        $errors = [];

        $authorizationHeaderType = $splitAuthorizationHeader[0];
        if ($authorizationHeaderType !== 'Basic') {
            $errors[] = 'Authorization header must be of "Basic" type.';
        }

        $splitCredentials = explode(':', base64_decode($splitAuthorizationHeader[1]));
        if (!key_exists(0, $splitCredentials) || !key_exists(1, $splitCredentials)) {
            $errors[] = 'Authorization header value has an invalid username:password format.';
        }

        return $errors;
    }
}
<?php

namespace App\Http\Controllers;

use App\ApiToken;
use App\Http\JsonApi;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Log\LoggerInterface;

class AuthenticateController extends Controller
{
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

    public function __construct(JsonApi $jsonApi, UserRepository $userRepository, LoggerInterface $logger, Hasher $hasher)
    {
        $this->userRepository = $userRepository;
        $this->jsonApi = $jsonApi;
        $this->logger = $logger;
        $this->hasher = $hasher;
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
            return $this->jsonApi->respondBadRequest($response, 'User does not exist');
        }

        if (!$this->hasher->check($password, $user->getAttribute('password'))) {
            return $this->jsonApi->respondUnauthorized($response);
        }

        $apiToken = sha1(str_random());
        $user->setAttribute('api_token', $apiToken);
        $user->setAttribute('api_token_expiration', $carbon->addDay());
        try {
            $user->save();
        } catch (Exception $e) {
            $this->logger->error("Failed to update user with API Token with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, 'Unable to create token.');
        }

        $apiToken = new ApiToken();
        $apiToken->token = $user->getAttribute('api_token');
        return $this->jsonApi->respondResourceCreated($response, $apiToken);
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
        $apiTokenHeader = $request->header('API-Token');
        if (is_null($apiTokenHeader)) {
            return $this->jsonApi->respondBadRequest($response, 'API-Token header is not set.');
        }

        $user = $this->userRepository->retrieveUserByToken($apiTokenHeader);
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
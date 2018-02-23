<?php

namespace App\Http\Controllers;

use App\Http\JsonApi;
use App\Repositories\UserRepository;
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
     *
     * @return Response
     */
    public function authenticate(Request $request, Response $response)
    {
        $authorizationHeader = $request->header('Authorization');

        $authorizationHeaderErrors = $this->validateAuthorizationHeader($authorizationHeader);
        if (!empty($authorizationHeaderErrors)) {
            return $this->jsonApi->respondBadRequest($response, $authorizationHeaderErrors);
        }

        $splitAuthorizationHeader = explode(':', base64_decode($authorizationHeader));

        $username = $splitAuthorizationHeader[0];
        $password = $splitAuthorizationHeader[1];

        $user = $this->userRepository->retrieveUserByCredentials($username, $password);
        if (!$user) {
            return new Response(['error' => 'Authentication failed.'], Response::HTTP_UNAUTHORIZED);
        }

        $apiToken = sha1(str_random());
        $twentyFourHoursFromNow = (new \DateTime())->modify("+1 day");
        try {
            $user->setAttribute('api_token', $apiToken);
            $user->setAttribute('api_token_expiration', $twentyFourHoursFromNow);
            $user->save();
        } catch (\Exception $e) {
            $this->logger->error("Failed to update user with API Token with exception: " . $e->getMessage());
            return new Response(['error' => 'There was a problem generating an API Token, please try again'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return new Response(['API-Token' => $apiToken]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function validateToken(Request $request)
    {
        $apiTokenHeader = $request->header('API-Token');
        if (!$apiTokenHeader) {
            return $this->jsonApi->respondBadRequest('API-Token header is not set');
        }

        $user = $this->userRepository->retrieveUserByToken($apiTokenHeader);
        if (!$user) {
            return $this->jsonApi->respondUnauthenticated();
        }

        $tokenExpiration = strtotime($user->getAttribute('api_token_expiration'));
        $now = (new \DateTime())->getTimestamp();
        if ($now > $tokenExpiration) {
            return $this->jsonApi->respondUnauthenticated();
        }

        return $this->jsonApi->respondResourceFound($user);
    }

    /**
     * @param $authorizationHeader
     * @return array
     */
    private function validateAuthorizationHeader($authorizationHeader)
    {
        if (!$authorizationHeader) {
            return ['No authorization header set.'];
        }

        $errors = [];
        $splitAuthorizationHeader = explode(' ', $authorizationHeader);

        $authorizationHeaderType = $splitAuthorizationHeader[0];
        if ($authorizationHeaderType !== 'Basic') {
            $errors[] = 'Authorization header must be of "Basic" type.';
        }

        if (!key_exists(1, $splitAuthorizationHeader)) {
            $errors[] = 'Authorization header must have a value';
        }

        $splitCredentials = explode(':', base64_decode($splitAuthorizationHeader[1]));
        if (!key_exists(0, $splitCredentials) || !key_exists(1, $splitCredentials)) {
            $errors[] = 'Username and Password must be set';
        }

        return $errors;
    }
}
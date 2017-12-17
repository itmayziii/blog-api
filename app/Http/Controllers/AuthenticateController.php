<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use itmayziii\Laravel\JsonApi;

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

    public function __construct(JsonApi $jsonApi, UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->jsonApi = $jsonApi;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function authenticate(Request $request)
    {
        $authorizationHeader = $request->header('Authorization');

        $validityMessage = $this->validateAuthorizationHeader($authorizationHeader);
        if (key_exists('error', $validityMessage)) {
            return new Response($validityMessage, Response::HTTP_BAD_REQUEST);
        }

        $decodedAuthorizationHeaderValue = urldecode($validityMessage['value']);
        $splitDecodedHeader = explode(':', $decodedAuthorizationHeaderValue);

        $username = $splitDecodedHeader[0];
        $password = $splitDecodedHeader[1];

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
            Log::error("Failed to update user with API Token with exception: " . $e->getMessage());
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
            return ['error' => 'No authorization header set.'];
        }

        $splitAuthorizationHeader = explode(' ', $authorizationHeader);

        $authorizationHeaderType = $splitAuthorizationHeader[0];
        if ($authorizationHeaderType !== 'Basic') {
            return ['error' => 'Authorization header must be of "Basic" type.'];
        }

        if (!key_exists(1, $splitAuthorizationHeader)) {
            return ['error' => 'Authorization header must have a value'];
        }

        $splitCredentials = explode(':', $splitAuthorizationHeader[1]);
        if (!key_exists(0, $splitCredentials) || !key_exists(1, $splitCredentials)) {
            return ['error' => 'Username and Password must be set'];
        }

        return ['type' => $authorizationHeaderType, 'value' => $splitAuthorizationHeader[1]];
    }
}
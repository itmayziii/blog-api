<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AuthenticateController extends Controller
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

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
            return new Response(['error' => 'Authentication failed.']);
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

        return ['type' => $authorizationHeaderType, 'value' => $splitAuthorizationHeader[1]];
    }
}
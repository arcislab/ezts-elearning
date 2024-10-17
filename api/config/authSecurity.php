<?php

require 'vendor/autoload.php';
require_once './api/v1/user.php';

use Firebase\JWT\ExpiredException;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$userController = new User();

$key = "cb5254312030541ab442c59423f34a0bed85997607f4953941c0960dd7b2380bcd4cc26ab2276340a3a22b4bbb301640c5f9e5e60cfb4c7f7eb1b456ce2c55ea9757f7d8f93f96d8773b90a7d917f5eecf9d009823a01b3b1029d727acd946d313ab4e78f029f72aa0a8e2ed39566ae455c8ccfcecf685f4aacad06b963ee516409a66e8a970bf1da8f622c752c1e6da2b5da31672de78e7a32748e89dc7e4e6d27942f7fbe28bd5672dbed18758fcf3bcbbb28b0421b52c64316d867493c0f199bcb598e40bf2e9436f63f3fb4366be9f726234b4d0006e92a98d49d6e67eb89ee374cd020501174df692ac217a1427af5a6dfb1ba06618ab45e283782b76e7"; // Define your secret key here

function generateToken($userId)
{
    global $key;
    $issuedAt = time();
    $expirationTime = $issuedAt + 3600; // Token is valid for 1 hour
    $payload = [
        'iat' => $issuedAt,
        'exp' => $expirationTime,
        'userId' => $userId
    ];

    return JWT::encode($payload, $key, 'HS256');
}

function validateToken()
{
    global $key;  // Secret key used for decoding the token

    // Check if the 'token' cookie exists
    if (!isset($_COOKIE['token'])) {
        return [
            'status' => false,
            'message' => 'Token cookie is missing'
        ];
    }

    $token = $_COOKIE['token'];  // Extract the token from the cookie

    try {
        // Decode the JWT token
        $decoded = JWT::decode($token, new Key($key, 'HS256'));

        // Token is valid, return success and user data
        return [
            'status' => true,
            'userId' => $decoded->userId
        ];
    } catch (ExpiredException $e) {
        return [
            'status' => false,
            'message' => 'Token expired'
        ];
    } catch (Exception $e) {
        return [
            'status' => false,
            'message' => 'Invalid token'
        ];
    }
}

function GetUserIdFromToken()
{
    global $key;
    
    if (!isset($_COOKIE['token'])) {
        return [
            'status' => false,
            'message' => 'Token cookie is missing'
        ];
    }

    $token = $_COOKIE['token'];

    try {
        // Decode the JWT token using the key and algorithm HS256
        $decoded = JWT::decode($token, new Key($key, 'HS256'));

        // If we reach this point, the token is valid
        return [
            'status' => true,
            'userId' => $decoded->userId
        ];
    } catch (ExpiredException $e) { // Handle expired token
        return [
            'status' => false,
            'message' => 'Token expired. Please login again.' // Token expired
        ];
    } catch (Exception $e) { // Handle invalid token and other errors
        return [
            'status' => false,
            'message' => 'Unauthorized' // Invalid token
        ];
    }
}

function ValidateOtp($mobileNumber, $otp)
{
    if ($otp === 1234 || $otp === "1234") {
        return true;
    } else {
        return false;
    }
}

// function authenticatedRoute($routeCallback, $checkForAdmin = false)
// {
//     // Authenticate the user
//     $authResponse = Authenticate();

//     if (isset($userId)) {
//         global $userController;
//         $isAdmin = $userController->IsUserAdmin($userId);
//     }

//     if ($authResponse) {
//         return $authResponse; // If authentication fails, return the error response
//     }

//     // If authenticated, run the route callback
//     return $routeCallback();
// }

//Authenticate before requesting the API for private APIs
function Authenticate($checkForAdmin = false)
{
    global $userController;
    $userId = null;

    $response = GetUserIdFromToken();
    if ($response['status'] === true) {
        // Token is valid, retrieve the userId
        $userId = $response['userId'];
        // Check if user exists
        if (!$userController->CheckIfUserExist($userId)) {
            return Response::json(401, [
                'status' => 'error',
                'message' => 'Unauthorized Request'
            ]);
        }

        // Check if admin (only if required)
        if ($checkForAdmin) {
            $userIsAdmin = $userController->IsUserAdmin($userId);
            if (!$userIsAdmin) {
                return Response::json(401, [
                    'status' => 'error',
                    'message' => 'Unauthorized Request'
                ]);
            }
        }

        // Check if token session is valid
        $tokenValidation = validateToken();
        if (!$tokenValidation['status']) {
            return Response::json(401, [
                'status' => 'error',
                'message' => $tokenValidation['message']
            ]);
        }

        // All checks passed
        return $userId;
    } else {
        // Token is invalid
        return Response::json(401, [
            'status' => 'error',
            'message' => $response['message']
        ]);
    }
}

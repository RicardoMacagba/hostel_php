<?php
namespace App\Controllers;

use App\Helpers\JWTHandler;
use App\Models\User;

class ApiController
{
    private $jwtHandler;

    /**
     * Constructor to initialize JWTHandler
     */
    public function __construct()
    {
        

        $this->jwtHandler = new JwtHandler();
    }

    /**
     * Authenticate the request using JWT token
     * 
     * @return array Decoded payload
     */
    public function authenticateRequest()
    {
        // Get Authorization header
        $headers = getallheaders();

        $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!isset($authorization)) {
            $this->sendResponse(401, ['message' => 'Authorization header missing']);
            exit;
        }
        
        // Extract token
        $token = str_replace('Bearer ', '', $authorization);

        // Validate token
        $payload = $this->jwtHandler->validateToken($token);
        if (!$payload) {
            $this->sendResponse(401, ['message' => 'Invalid or expired token']);
            exit;
        }

        return $payload; // Return decoded payload for further use
    }

    /**
     * Send JSON response
     * 
     * @param int $statusCode HTTP status code
     * @param array $data Response data
     */
    public function sendResponse($statusCode, $data)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Return list of users in JSON format
     */
    public function users()
    {
        // Make sure user is authenticated. Add this line to protected api routes or when user should be authenticated
        $this->authenticateRequest();

        // Fetch all users using the User model
        $users = User::findAll();

        $userData = array_map(function($user) {
            return [
                'user_id' => $user->user_id,
                'username' => $user->username,
                'email' => $user->email,
                'image_url' => $user->image_url, // Assuming the user has a picture attribute
            ];
        }, $users);

        // Set the content type to JSON and return the response
        $this->sendResponse(200, $userData);
    }

    /**
     * Handle user login and return JWT token
     * 
     * @param array $params Login parameters
     */
    public function login($params)
    {
        $user = new User();
        $user->email = $params['email'] ?? null;
        $user->password = $params['password'] ?? null;
        $authenticatedUser = $user->login();
        if ($authenticatedUser) {
            $token = $this->jwtHandler->generateToken(['user_id' => $authenticatedUser->user_id, 'email' => $authenticatedUser->email]);
            $this->sendResponse(200, [
                'message' => 'Login successful',
                'token' => $token,
                'user' => ['user_id' => $authenticatedUser->user_id, 'email' => $authenticatedUser->email]
            ]);
        } else {
            $this->sendResponse(401, ['message' => 'Invalid email or password']);
        }
        exit;
    }

    public function profileUpdate($params){
        
        // Authenticate the user
        $this->authenticateRequest();

        $username = trim($params['username']);
        $profilePicture = $params['profilePicture'];


        // Check if valid file types
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($profilePicture['type'], $allowedTypes)) {
            $this->sendResponse(400, ['message' => 'Invalid file type. Only JPEG and PNG are allowed']);
            exit;
        }

        
        // Save the uploaded file
        $uploadDir = __DIR__ . '/../../storage/images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newFileName = uniqid('profile_') . '.' . pathinfo($params['profilePicture']['name'], PATHINFO_EXTENSION);
        $filePath = $uploadDir . $newFileName;

        if (!move_uploaded_file($profilePicture['tmp_name'], $filePath)) {
            $this->sendResponse(500, ['message' => 'Failed to save the uploaded file']);
            exit;
        }

        // Update user data in the database
        $user = User::find($params['user_id']); // Fetch user from the database
        if (!$user) {
            $this->sendResponse(404, ['message' => 'User not found']);
            exit;
        }

        $user->username = $username;
        $user->image_url = $newFileName; // Assuming image_url stores the relative path
        $updateResult = $user->save();

        if ($updateResult) {
            $this->sendResponse(200, [
                'message' => 'Profile updated successfully',
                'user' => [
                    'username' => $user->username,
                    'image_url' => $user->image_url,
                ],
            ]);
        } else {
            $this->sendResponse(500, ['message' => 'Failed to update profile']);
        }
    }

    // api controller for adding a rooms

}

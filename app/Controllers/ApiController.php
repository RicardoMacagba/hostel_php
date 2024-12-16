<?php

namespace App\Controllers;

use App\Helpers\JWTHandler;
use App\Models\User;
use App\Models\Room;

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

        $userData = array_map(function ($user) {
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

    public function profileUpdate($params)
    {

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

    /**
     * Handle user registration
     * 
     * @param array $params Registration parameters
     */
    public function register($params)
    {
        // Input validation
        $email = trim($params['email'] ?? '');
        $password = $params['password'] ?? '';
        $username = trim($params['username'] ?? '');

        if (empty($email) || empty($password) || empty($username)) {
            $this->sendResponse(400, ['message' => 'Email, username, and password are required']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->sendResponse(400, ['message' => 'Invalid email format']);
            return;
        }

        if (strlen($password) < 6) {
            $this->sendResponse(400, ['message' => 'Password must be at least 6 characters long']);
            return;
        }
        //echo "wowopet";

        // Create a new user
        $user = new User();
        $user->email = $email;
        $user->password = password_hash($password, PASSWORD_DEFAULT); // Securely hash the password
        $user->username = $username;

        if ($user->save()) {
            // Respond with a success message
            $this->sendResponse(201, [
                'message' => 'User registered successfully',
                'user' => [
                    'user_id' => $user->user_id,
                    'email' => $user->email,
                    'username' => $user->username,
                ],
            ]);
        } else {
            // Handle failure to save
            $this->sendResponse(500, ['message' => 'Failed to register user']);
        }
    }

    // api controller for adding a rooms
    /**
 * List all rooms
 */
public function listRooms()
{
    // Authenticate the user
    $this->authenticateRequest();

    // Fetch all rooms using the Room model
    try {
        $rooms = Room::findAll();

        if (empty($rooms)) {
            $this->sendResponse(404, ['message' => 'No rooms found']);
            return;
        }

        // Format room data for the response
        $roomData = array_map(function ($room) {
            return [
                'id' => $room->id,
                'name' => $room->name,
                'type' => $room->type,
                'price' => $room->price,
                'status' => $room->status,
            ];
        }, $rooms);

        // Send response with the list of rooms
        $this->sendResponse(200, ['rooms' => $roomData]);
    } catch (\Exception $e) {
        $this->sendResponse(500, ['message' => 'An error occurred while fetching rooms', 'error' => $e->getMessage()]);
    }
}

/**
 * Add a new room
 *
 * @param array $params Room parameters
 */
public function addRoom($params)
{
    // Authenticate the user
    $this->authenticateRequest();

    // Input validation
    $name = trim($params['name'] ?? '');
    $type = trim($params['type'] ?? '');
    $price = trim($params['price'] ?? '');
    $status = $params['status'] ?? 'available'; // Default status

    if (empty($name) || empty($type) || !is_numeric($price) || $price < 0) {
        $this->sendResponse(400, ['message' => 'Invalid input. Name, type, and valid price are required']);
        return;
    }

    try {
        // Create new Room instance
        $room = new Room();
        $room->name = $name;
        $room->type = $type;
        $room->price = (float) $price;
        $room->status = $status;

        // Save room to the database
        if ($room->save()) {
            $this->sendResponse(201, [
                'message' => 'Room added successfully',
                'room' => [
                    'id' => $room->id,
                    'name' => $room->name,
                    'type' => $room->type,
                    'price' => $room->price,
                    'status' => $room->status,
                ],
            ]);
        } else {
            $this->sendResponse(500, ['message' => 'Failed to add room']);
        }
    } catch (\Exception $e) {
        $this->sendResponse(500, ['message' => 'An error occurred while adding the room', 'error' => $e->getMessage()]);
    }
}

/**
 * Update room details
 *
 * @param array $params Room parameters
 */
public function updateRoom($params)
{
    $this->authenticateRequest();

    $roomId = $params['id'] ?? null;
    $name = trim($params['name'] ?? '');
    $type = trim($params['type'] ?? '');
    $price = trim($params['price'] ?? '');
    $status = trim($params['status'] ?? '');

    if (empty($roomId) || (!is_numeric($price) && !empty($price)) || $price < 0) {
        $this->sendResponse(400, ['message' => 'Invalid room ID or input']);
        return;
    }

    try {
        $room = Room::find($roomId);
        if (!$room) {
            $this->sendResponse(404, ['message' => 'Room not found']);
            return;
        }

        if ($name) $room->name = $name;
        if ($type) $room->type = $type;
        if ($price) $room->price = (float) $price;
        if ($status) $room->status = $status;

        if ($room->save()) {
            $this->sendResponse(200, ['message' => 'Room updated successfully', 'room' => $room]);
        } else {
            $this->sendResponse(500, ['message' => 'Failed to update room']);
        }
    } catch (\Exception $e) {
        $this->sendResponse(500, ['message' => 'An error occurred while updating the room', 'error' => $e->getMessage()]);
    }
}

/**
 * Delete a room
 *
 * @param array $params Room ID
 */
public function deleteRoom($params)
{
    $this->authenticateRequest();

    $roomId = $params['id'] ?? null;

    if (empty($roomId)) {
        $this->sendResponse(400, ['message' => 'Room ID is required']);
        return;
    }

    try {
        $room = Room::find($roomId);
        if (!$room) {
            $this->sendResponse(404, ['message' => 'Room not found']);
            return;
        }

        if ($room->delete()) {
            $this->sendResponse(200, ['message' => 'Room deleted successfully']);
        } else {
            $this->sendResponse(500, ['message' => 'Failed to delete room']);
        }
    } catch (\Exception $e) {
        $this->sendResponse(500, ['message' => 'An error occurred while deleting the room', 'error' => $e->getMessage()]);
    }
}


}

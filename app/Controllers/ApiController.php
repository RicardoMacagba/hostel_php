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

    public function listRooms()
    {
        try {
            // Fetch all rooms using the Room model
            $rooms = Room::findAll();

            if (empty($rooms)) {
                // If no rooms are found, send a 404 response
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
                    'capacity' => $room->capacity,
                    'image_url' => !empty($room->image)
                        ? "/storage/images/rooms/{$room->image}"
                        : null, // Include full image URL if available
                ];
            }, $rooms);

            // Send response with the list of rooms
            $this->sendResponse(200, [
                'message' => 'Rooms fetched successfully',
                'rooms' => $roomData,
            ]);
        } catch (\Exception $e) {
            // Handle unexpected errors
            $this->sendResponse(500, [
                'message' => 'An error occurred while fetching rooms',
                'error' => $e->getMessage(),
            ]);
        }
    }


    // api controller for adding a rooms
    public function addRooms($params)
    {
        // Debug logging for incoming request data
        error_log('Request Params: ' . print_r($params, true));
        error_log('Uploaded Files: ' . print_r($_FILES, true));

        // Input validation
        $name = trim($params['name'] ?? '');
        $type = trim($params['type'] ?? '');
        $capacity = isset($params['capacity']) ? (int)$params['capacity'] : 0;
        $price = isset($params['price']) ? (float)$params['price'] : 0.0;
        $status = trim($params['status'] ?? 'available');

        // Validate required fields
        if (empty($name) || empty($type) || $capacity <= 0 || $price <= 0) {
            $this->sendResponse(400, ['message' => 'Name, type, capacity, and price are required']);
            return;
        }

        // Create a new room instance
        $room = new Room();
        $room->name = $name;
        $room->type = $type;
        $room->capacity = $capacity;
        $room->price = $price;
        $room->status = $status;

        // Handle image upload
        $imageUploadResult = $this->handleImageUpload('room_image');
        if ($imageUploadResult['success']) {
            $room->image = $imageUploadResult['filename'];
        } else {
            error_log('Image upload failed: ' . $imageUploadResult['error']); // Log image upload error
            $this->sendResponse(400, ['message' => $imageUploadResult['error']]);
            return;
        }

        // Save the room to the database
        if ($room->save()) {
            $this->sendResponse(201, [
                'message' => 'Room added successfully',
                'room' => [
                    'id' => $room->id,
                    'name' => $room->name,
                    'type' => $room->type,
                    'capacity' => $room->capacity,
                    'price' => $room->price,
                    'status' => $room->status,
                    'image_url' => $this->getImageUrl($room->image), // Construct full image URL
                ],
            ]);
        } else {
            error_log('Room save failed'); // Log save failure
            $this->sendResponse(500, ['message' => 'Failed to add room']);
        }
    }


    /**
     * Handles image upload and returns the result.
     *
     * @param string $fileKey The key of the uploaded file in $_FILES.
     * @return array The result of the upload process with success or error message.
     */
    private function handleImageUpload($fileKey)
    {
        if (isset($_FILES[$fileKey]) && !empty($_FILES[$fileKey]['tmp_name'])) {
            // Ensure the uploaded file is an image
            if (str_starts_with($_FILES[$fileKey]['type'], 'image/')) {
                $uploadDir = __DIR__ . '/../../storage/images/rooms/';

                // Ensure the upload directory exists
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Generate a unique file name
                $uniqueFileName = uniqid('room_') . '.' . pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION);
                $targetFilePath = $uploadDir . $uniqueFileName;

                // Move the uploaded file to the target directory
                if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetFilePath)) {
                    return ['success' => true, 'filename' => $uniqueFileName];
                } else {
                    return ['success' => false, 'error' => "Failed to upload the image file."];
                }
            } else {
                return ['success' => false, 'error' => "The uploaded file is not a valid image."];
            }
        } else {
            return ['success' => false, 'error' => "No image file was uploaded."];
        }
    }

    /**
     * Constructs a full URL for the uploaded image.
     *
     * @param string $imageFilename The filename of the uploaded image.
     * @return string The full URL of the image.
     */
    private function getImageUrl($imageFilename)
    {
        return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/storage/images/rooms/' . $imageFilename;
    }

    // sa delete
    public function deleteRoom($id)
    {
        $room = Room::find($id);

        if (!$room) {
            http_response_code(404);
            echo json_encode(['message' => 'Room not found']);
            return;
        }

        if ($room->delete()) {
            http_response_code(200);
            echo json_encode(['message' => 'Room deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to delete room']);
        }
    }


    public function updateRoom($params, $id)
{
    // Authenticate the request
    $this->authenticateRequest();

    // Find the room
    $room = Room::find($id);
    if (!$room) {
        $this->sendResponse(404, ['message' => 'Room not found']);
        return;
    }

    // Determine input source: JSON body or standard $params
    $inputData = json_decode(file_get_contents('php://input'), true) ?? $params;

    // Validate required fields
    if (empty($inputData['name']) || empty($inputData['price'])) {
        $this->sendResponse(400, ['message' => 'Room name and price are required']);
        return;
    }

    // Update room properties
    $room->name = $inputData['name'] ?? $room->name;
    $room->type = $inputData['type'] ?? $room->type;
    $room->capacity = $inputData['capacity'] ?? $room->capacity;
    $room->price = $inputData['price'] ?? $room->price;
    $room->status = $inputData['status'] ?? $room->status;

    // Handle image update
    if (isset($_FILES['room_image']) && str_starts_with($_FILES['room_image']['type'], 'image/')) {
        $uploadDir = __DIR__ . '/../../storage/images/rooms/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uniqueFileName = uniqid('room_') . '.' . pathinfo($_FILES['room_image']['name'], PATHINFO_EXTENSION);
        $targetFilePath = $uploadDir . $uniqueFileName;

        if (move_uploaded_file($_FILES['room_image']['tmp_name'], $targetFilePath)) {
            $room->image = $uniqueFileName;
        } else {
            $this->sendResponse(500, ['message' => 'Failed to upload the room image']);
            return;
        }
    }

    // Save the room
    if ($room->save()) {
        $this->sendResponse(200, [
            'message' => 'Room updated successfully',
            'data' => [
                'id' => $room->id,
                'name' => $room->name,
                'type' => $room->type,
                'capacity' => $room->capacity,
                'price' => $room->price,
                'status' => $room->status,
                'image' => $room->image,
            ],
        ]);
    } else {
        $this->sendResponse(500, ['message' => 'Failed to update the room']);
    }
}

}

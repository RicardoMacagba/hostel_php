<?php

namespace App\Controllers;

use App\Models\Room;
use PDO;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->layout = "main"; // Set default layout for all actions
        $this->generateCsrfToken();
    }

    /**
     * Display the rooms list
     *
     * @param array $params
     */
    public function listRooms($params)
    {
        $this->pageTitle = 'rooms';
        $rooms = [];

        // Handle deletion
        if (isset($params['delete_room_id'])) {
            $this->validateCsrfToken($params);
            $room = Room::find($params['delete_room_id']);
            if ($room->delete()) {
                self::redirect('/table_room'); // Refresh the page after deletion
            } else {
                $this->errors[] = "Failed to delete the room.";
            }
        }

        // Handle search
        if (isset($params['q'])) {
            $q = "%" . $params['q'] . "%";
            $rooms = Room::filter([
                "name LIKE" => $q,
                "OR type LIKE" => $q,
            ]);
        }

        // If no search request
        if (!isset($params['q'])) {
            $rooms = Room::findAll();
        }

        $this->view('rooms/table_room', [
            'rooms' => $rooms,
        ]);
    }

    /**
     * Edit room details
     *
     * @param array $params
     */
    public function editRooms($params)
    {
        $this->layout = 'main'; // Set the layout for the page
        $this->pageTitle = 'Edit Room';
        //$rooms = [];
        //echo 'wowo';
        

        $id = $params['id'] ?? null; // Get the room ID from parameters
        // if (!$id) {
        //     echo $id;
        //     self::redirect('/listRooms', ['error' => 'Invalid Room ID.']);
        //     return;
        // }

        // Find the room by ID
        $rooms = Room::find($id);
        
        // if (!$rooms) {
        //     echo 'wew';
        //     self::redirect('/listRooms', ['error' => 'Room not found.']);
        //     return;
        //     echo $rooms;
        //     exit;
        // }
        echo 'check';
        echo $rooms;
        echo $id;

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            $this->validateCsrfToken($params);

            // Update the room attributes
            $rooms->name = trim($params['name'] ?? '');
            $rooms->type = trim($params['type'] ?? '');
            $rooms->capacity = isset($params['capacity']) ? $params['capacity'] : 0; // Ensure integer
            $rooms->price = isset($params['price']) ? $params['price'] : 0.0; // Ensure float
            $rooms->status = trim($params['status'] ?? 'available'); // Default status to 'available'

            // Validate and save changes
            if ($rooms->validate()) {
                if ($rooms->save()) {
                    self::redirect('/listRooms', ['success' => 'Room updated successfully!']);
                    return;
                } else {
                    $errors[] = 'Failed to update the room. Please try again.';
                }
            } else {
                $errors = array_merge($errors, $rooms->getErrors());
            }
        }

        //Pass the room data and errors to the view
        $viewData = [
            'rooms' => [
                //'id' => $rooms->id,
                'name' => htmlspecialchars($rooms->name ?? '', ENT_QUOTES),
                'type' => htmlspecialchars($rooms->type ?? '', ENT_QUOTES),
                'capacity' => htmlspecialchars($rooms->capacity ?? '', ENT_QUOTES), // Cast numeric values to string
                'price' => htmlspecialchars($rooms->price ?? '', ENT_QUOTES), // Cast numeric values to string
                'status' => htmlspecialchars($rooms->status ?? '', ENT_QUOTES),
            ],
            'errors' => $errors,
        ];

        // Render the edit room view
        $this->view('rooms/edit_rooms', ['rooms' => $rooms], $viewData); //, //$viewData);
    }


    //method add_rooms here

    public function addRooms($params)
    {
        $this->pageTitle = "Add Room";
        $model = new Room(); // Assuming Room is the model for the 'rooms' table
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Assign attributes from the form
            $model->name = trim($params['name'] ?? '');
            $model->type = trim($params['type'] ?? '');
            $model->capacity = isset($params['capacity']) ? (int)$params['capacity'] : 0;
            $model->price = isset($params['price']) ? (float)$params['price'] : 0.0;
            $model->status = trim($params['status'] ?? 'available');

            // Handle room image upload
            if (isset($_FILES['room_image']) && str_starts_with($_FILES['room_image']['type'], 'image/')) {
                $uploadDir = __DIR__ . '/../../storage/images/rooms/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $uniqueFileName = uniqid('room_') . '.' . pathinfo($_FILES['room_image']['name'], PATHINFO_EXTENSION);
                $targetFilePath = $uploadDir . $uniqueFileName;

                if (move_uploaded_file($_FILES['room_image']['tmp_name'], $targetFilePath)) {
                    // Assign the uploaded image filename to the model
                    $model->image = $uniqueFileName;
                } else {
                    $errors[] = "Failed to upload the room image.";
                }
            } else {
                $errors[] = "Invalid or no image file provided.";
            }

            // Validate the model and save to the database
            if (empty($errors) && $model->validate()) {
                if ($model->save()) {
                    self::redirect('/table_room', ['success' => 'Room added successfully!']);
                } else {
                    $errors[] = "Failed to save the room to the database.";
                }
            } else {
                $errors = array_merge($errors, $model->getErrors());
            }
        }

        // Render the add room view with model data and errors
        $this->view('rooms/add_rooms', [
            'model' => $model,
            'errors' => $errors,
        ]);
    }

     /**
     * Generate a full image URL for the given image filename.
     *
     * @param string $image
     * @return string
     */
    public function getImageUrl($image)
    {
        return rtrim($_ENV['APP_URL'], '/') . '/storage/images/rooms/' . $image;
    }
}

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
     * Add a new room
     *
     * @param array $params
     */
    // public function addRoom($params)
    // {
    //     $this->layout = 'simple';
    //     $this->pageTitle = 'Add Room';

    //     $model = new Room();

    //     if (!empty($params)) {
    //         $this->validateCsrfToken($params);

    //         $model->room_name = $params['name'];
    //         $model->room_type = $params['type'];
    //         $model->price = $params['price'];

    //         if ($model->validate()) {
    //             if ($model->save()) {
    //                 self::redirect('/rooms'); // Redirect to the rooms page after adding
    //             } else {
    //                 $this->errors[] = "Failed to add the room. Please try again.";
    //             }
    //         } else {
    //             foreach ($model->getErrors() as $field => $errors) {
    //                 foreach ($errors as $error) {
    //                     $this->errors[] = $error;
    //                 }
    //             }
    //         }
    //     }

    //     $this->view('room/add_rooms', [
    //         'model' => $model,
    //     ]);
    // }

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
        $this->pageTitle = 'Edit Room';

        // Ensure room data is initialized
        $room = [];



        // Render the edit room view
        $this->view('rooms/edit_rooms', [
            'room' => $room,
            'errors' => $this->errors,
        ]);
    }


    /**
     * Delete a room
     *
     * @param array $params
     */
    // public function deleteRoom($params)
    // {
    //     if (isset($params['id'])) {
    //         $this->validateCsrfToken($params);

    //         $room = Room::find($params['id']);
    //         if ($room && $room->delete()) {
    //             self::redirect('/table_room');
    //         } else {
    //             $this->errors[] = "Failed to delete the room.";
    //         }
    //     }

    //     self::redirect('/rooms');
    // }

    public function editRoom($params)
    {
        // Fetch room by ID
        $roomId = $params['id'] ?? null;

        if (!$roomId) {
            $this->errors[] = "Room ID is required.";
            return;
        }

        $room = Room::find($roomId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle form submission for editing
            $room->name = trim($params['name']);
            $room->type = trim($params['type']);
            $room->price = trim($params['price']);

            if ($room->save()) {
                $this->errors[] = 'Room updated successfully';
            } else {
                $this->errors[] = 'Failed to update room';
            }
        } else {
            // Display edit form with room data
            return [
                'room' => $room,
            ];
        }
    }

    //method add_rooms here

    /**
     * Add a new room
     *
     * @param array $params
     */
    public function addRooms($params)
{
    $this->layout = 'main'; // Set the layout for the page
    $this->pageTitle = 'Add Room';

    $model = new Room();
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF token
        $this->validateCsrfToken($params);

        // Assign attributes to the room model
        $model->name = trim($params['name'] ?? '');
        $model->type = trim($params['type'] ?? '');
        $model->capacity = isset($params['capacity']) ? (int)$params['capacity'] : 0; // Ensure integer
        $model->price = isset($params['price']) ? (float)$params['price'] : 0.0; // Ensure float
        $model->status = trim($params['status'] ?? 'available'); // Default status to 'available'

        // Validate the model and save the room
        if ($model->validate()) {
            if ($model->save()) {
                // Redirect with a success message
                self::redirect('/table_room', ['success' => 'Room added successfully!']);
            } else {
                $errors[] = 'Failed to add the room. Please try again.';
            }
        } else {
            // Collect validation errors
            $errors = array_merge($errors, $model->getErrors());
        }
    }

    // Ensure model properties are valid strings for the view
    $viewData = [
        'model' => [
            'name' => htmlspecialchars($model->name ?? '', ENT_QUOTES),
            'type' => htmlspecialchars($model->type ?? '', ENT_QUOTES),
            'capacity' => htmlspecialchars((string)$model->capacity ?? '', ENT_QUOTES), // Cast numeric values to string
            'price' => htmlspecialchars((string)$model->price ?? '', ENT_QUOTES), // Cast numeric values to string
            'status' => htmlspecialchars($model->status ?? '', ENT_QUOTES),
        ],
        'errors' => $errors,
    ];

    // Render the add room view
    $this->view('rooms/add_rooms', $viewData);
}

}

<?php

namespace App\Controllers;

use App\Models\Room;

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
    public function addRoom($params)
    {
        $this->layout = 'simple';
        $this->pageTitle = 'Add Room';

        $model = new Room();

        if (!empty($params)) {
            $this->validateCsrfToken($params);

            $model->room_name = $params['room_name'];
            $model->room_type = $params['room_type'];
            $model->price = $params['price'];

            if ($model->validate()) {
                if ($model->save()) {
                    self::redirect('/rooms'); // Redirect to the rooms page after adding
                } else {
                    $this->errors[] = "Failed to add the room. Please try again.";
                }
            } else {
                foreach ($model->getErrors() as $field => $errors) {
                    foreach ($errors as $error) {
                        $this->errors[] = $error;
                    }
                }
            }
        }

        $this->view('room/add', [
            'model' => $model,
        ]);
    }

    /**
     * Display the rooms list
     *
     * @param array $params
     */
    public function listRooms($params)
    {
        $this->pageTitle = 'table_room';
        $rooms = [];

        // Handle deletion
        if (isset($params['delete_room_id'])) {
            $this->validateCsrfToken($params);
            $room = Room::find($params['delete_room_id']);
            if ($room->delete()) {
                self::redirect('/listRooms'); // Refresh the page after deletion
            } else {
                $this->errors[] = "Failed to delete the room.";
            }
        }

        // Handle search
        if (isset($params['q'])) {
            $q = "%" . $params['q'] . "%";
            $rooms = Room::filter([
                "room_name LIKE" => $q,
                "OR room_type LIKE" => $q,
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
    public function editRoom($params)
    {
        $this->pageTitle = 'Edit Room';

        if (!isset($params['room_id'])) {
            self::redirect('/rooms'); // Redirect if no room ID is provided
        }

        $model = Room::find($params['room_id']);

        if (!$model) {
            $this->errors[] = "Room not found.";
            self::redirect('/rooms');
        }

        if (!empty($params)) {
            $this->validateCsrfToken($params);

            $model->room_name = $params['room_name'];
            $model->room_type = $params['room_type'];
            $model->price = $params['price'];

            if ($model->validate()) {
                if ($model->save()) {
                    self::redirect('/rooms'); // Redirect after saving
                } else {
                    $this->errors[] = "Failed to update the room.";
                }
            } else {
                foreach ($model->getErrors() as $field => $errors) {
                    foreach ($errors as $error) {
                        $this->errors[] = $error;
                    }
                }
            }
        }

        $this->view('room/edit', [
            'model' => $model,
        ]);
    }

    /**
     * Delete a room
     *
     * @param array $params
     */
    public function deleteRoom($params)
    {
        if (isset($params['room_id'])) {
            $this->validateCsrfToken($params);

            $room = Room::find($params['room_id']);
            if ($room && $room->delete()) {
                self::redirect('/rooms');
            } else {
                $this->errors[] = "Failed to delete the room.";
            }
        }

        self::redirect('/rooms');
    }
}

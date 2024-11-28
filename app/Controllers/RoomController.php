<?php
namespace App\Controllers;

use App\Models\Room;

class RoomController {
    private $model;

    public function __construct($pdo) {
        $this->model = new Room($pdo);
    }

    // Display all rooms
    public function index() {
        $rooms = $this->model->getAllRooms();
        $this->render('rooms/index', ['rooms' => $rooms]);
    }

    // Show a single room
    public function show($roomId) {
        $room = $this->model->getRoomById($roomId);
        $this->render('rooms/show', ['room' => $room]);
    }

    // Handle room addition
    public function store($params) {
        if ($this->model->addRoom($params)) {
            header('Location: /rooms');
        } else {
            echo "Error adding room.";
        }
    }

    // Handle room update
    public function update($roomId, $params) {
        if ($this->model->updateRoom($roomId, $params)) {
            header('Location: /rooms');
        } else {
            echo "Error updating room.";
        }
    }

    // Handle room deletion
    public function destroy($roomId) {
        if ($this->model->deleteRoom($roomId)) {
            header('Location: /rooms');
        } else {
            echo "Error deleting room.";
        }
    }

    // Render a view
    private function render($view, $data = []) {
        extract($data);
        require __DIR__ . "/../Views/$view.php";
    }
}

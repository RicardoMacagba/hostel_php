<?php
namespace App\Models;

use PDO;

class Room {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Fetch all rooms
    public function getAllRooms() {
        $stmt = $this->pdo->query("SELECT * FROM rooms");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch a room by ID
    public function getRoomById($roomId) {
        $stmt = $this->pdo->prepare("SELECT * FROM rooms WHERE room_id = :room_id");
        $stmt->bindParam(':room_id', $roomId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Add a new room
    public function addRoom($roomData) {
        $stmt = $this->pdo->prepare("INSERT INTO rooms (room_type, room_description, room_price, room_image) VALUES (:type, :description, :price, :image)");
        return $stmt->execute($roomData);
    }

    // Update a room
    public function updateRoom($roomId, $roomData) {
        $stmt = $this->pdo->prepare("UPDATE rooms SET room_type = :type, room_description = :description, room_price = :price, room_image = :image WHERE room_id = :room_id");
        $roomData['room_id'] = $roomId;
        return $stmt->execute($roomData);
    }

    // Delete a room
    public function deleteRoom($roomId) {
        $stmt = $this->pdo->prepare("DELETE FROM rooms WHERE room_id = :room_id");
        $stmt->bindParam(':room_id', $roomId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

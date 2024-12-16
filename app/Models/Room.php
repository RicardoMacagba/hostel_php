<?php

namespace App\Models;

use PDO;

class Room extends Model
{
    public function __construct()
    {
        // Specify the table name
        parent::__construct('rooms');

        // Specify the primary key
        $this->primaryKey = 'id';

        // Set validation rules for the model
        // Define validation rules for the Room model
        $this->setRules([
            'name' => [
                'required' => true,
                'maxLength' => 255,
            ],
            'type' => [
                'required' => true,
                'maxLength' => 255,
            ],
            'capacity' => [
                'required' => true,
                'numeric' => true,
                'minValue' => 1,
            ],
            'price' => [
                'required' => true,
                'numeric' => true,
                'minValue' => 0,
            ],
            'status' => [
                'required' => true,
                'inArray' => ['available', 'unavailable'],
            ],
        ]);
    }

    public function setSession()
    {


        // Store room details in the session
        $_SESSION['rooms'] = [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'capacity' => $this->capacity,
            'price' => $this->price,
            'status' => $this->status,
        ];
    }


    /**
     * Get a human-readable type of the room.
     *
     * @return string
     */
    public function getRoomTypeLabel()
    {
        $types = [
            'standard' => 'Standard Room',
            'luxury'   => 'Luxury Room',
            'suite'    => 'Suite',
        ];

        return $types[$this->type] ?? 'Unknown';
    }

    /**
     * Format the room price for display.
     *
     * @return string
     */
    public function getFormattedPrice()
    {
        return "P" . number_format($this->price, 2);
    }

    /**
     * Get all rooms of a specific type.
     *
     * @param string $type
     * @return Room[]
     */
    public static function getRoomsByType($type)
    {
        return self::filter(['type =' => $type]);
    }

    /**
     * Get rooms within a specific price range.
     *
     * @param float $min
     * @param float $max
     * @return Room[]
     */
    public static function getRoomsByPriceRange($min, $max)
    {
        return self::filter([
            'price >=' => $min,
            'AND price <=' => $max,
        ]);
    }

    public function assignRoomAttributes($model, $params)
    {
        $model->room_name = $params['name'] ?? null;
        $model->room_type = $params['type'] ?? null;
        $model->price = $params['price'] ?? null;
    }

    /**
     * Update room attributes and save to the database.
     *
     * @param array $params Attributes to update (name, type, price)
     * @return bool Whether the update was successful
     */
    public function editRoom($params)
    {
        // Assign attributes
        $this->room_name = $params['name'] ?? $this->room_name;
        $this->room_type = $params['type'] ?? $this->room_type;
        $this->price = $params['price'] ?? $this->price;

        // Validate the model
        if (!$this->validate()) {
            return false;
        }

        // Save the model
        return $this->save();
    }


    public function getRoomById($params)
    {
        // Extract the room ID from the request parameters
        $roomId = $params['id'] ?? null;

        if (!$roomId) {
            return [
                'error' => 'Room ID is required',
            ];
        }

        // Fetch the room from the database
        $room = Room::find($roomId);

        if (!$room) {
            return [
                'error' => 'Room not found',
            ];
        }

        // Return the room data
        return [
            'rooms' => $room,
        ];
    }

    public function addRooms()
    {
        $query = "INSERT INTO {$this->table} (name, type, capacity, price, status, created_at, updated_at)
              VALUES (:name, :type, :capacity, :price, :status, NOW(), NOW())";

        $stmt = self::$db->prepare($query);

        // Bind the room attributes to the query
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':capacity', $this->capacity, PDO::PARAM_INT);
        $stmt->bindParam(':price', $this->price, PDO::PARAM_STR); // Use string for decimal values
        $stmt->bindParam(':status', $this->status);

        // Execute the query and return true if successful, false otherwise
        return $stmt->execute();
    }
}

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
    // public function updateRoom($id, $data)
    // {
    //     // Prepare the SQL query for updating the room
    //     $query = "UPDATE {$this->table} 
    //           SET name = :name, 
    //               type = :type, 
    //               price = :price, 
    //               capacity = :capacity, 
    //               status = :status
    //           WHERE id = :id";

    //     // Prepare the statement
    //     $stmt = self::$db->prepare($query);

    //     // Bind the parameters
    //     $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    //     $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
    //     $stmt->bindParam(':type', $data['type'], PDO::PARAM_STR);
    //     $stmt->bindParam(':price', $data['price'], PDO::PARAM_STR); // float stored as string
    //     $stmt->bindParam(':capacity', $data['capacity'], PDO::PARAM_INT);
    //     $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);

    //     // Execute the query and return the result
    //     return $stmt->execute();
    // }

    public function getRoomById($params)
{
    $roomId = $params['id'] ?? null;

    if (!$roomId) {
        error_log('Room ID not provided in request.');
        return [
            'error' => 'Room ID is required',
        ];
    }

    $room = Room::find($roomId);

    if (!$room) {
        error_log("Room with ID $roomId not found.");
        return [
            'error' => 'Room not found',
        ];
    }

    return [
        'rooms' => $room,
    ];
}


    public function addRooms($params)
    {
        // Initialize an array for errors
        $errors = [];
        $imagePath = null;

        // Handle file upload for room image
        if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] === UPLOAD_ERR_OK) {
            $imageName = basename($_FILES['room_image']['name']);
            $imageTmpName = $_FILES['room_image']['tmp_name'];
            $uploadDir = __DIR__ . '/../../storage/uploads/rooms/';

            // Ensure the upload directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate a unique file name to prevent conflicts
            $uniqueImageName = uniqid('room_') . '.' . pathinfo($imageName, PATHINFO_EXTENSION);

            // Define the target file path
            $imagePath = $uploadDir . $uniqueImageName;

            // Move the uploaded file to the uploads directory
            if (move_uploaded_file($imageTmpName, $imagePath)) {
                // Save only the filename for storing in the database
                $imagePath = $uniqueImageName;
            } else {
                $errors[] = "Failed to upload the room image.";
            }
        }

        // Assign room attributes from the parameters
        $this->name = trim($params['name'] ?? '');
        $this->type = trim($params['type'] ?? '');
        $this->capacity = isset($params['capacity']) ? (int)$params['capacity'] : 0;
        $this->price = isset($params['price']) ? (float)$params['price'] : 0.0;
        $this->status = trim($params['status'] ?? 'available');
        $this->image = $imagePath; // Assign the uploaded image filename

        // Insert the room into the database
        $query = "INSERT INTO {$this->table} (name, type, capacity, price, status, room_image, created_at, updated_at)
              VALUES (:name, :type, :capacity, :price, :status, :image, NOW(), NOW())";

        $stmt = self::$db->prepare($query);

        // Bind parameters to the query
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':capacity', $this->capacity, PDO::PARAM_INT);
        $stmt->bindParam(':price', $this->price, PDO::PARAM_STR); // Use string for decimals
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':image', $this->image);

        // Execute the query
        if ($stmt->execute()) {
            return true;
        } else {
            $errors[] = "Failed to save the room to the database.";
            return false;
        }
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

    public function editRooms($id, $data)
{
    // Basic validation
    if (empty($data['name']) || empty($data['type']) || empty($data['price']) || empty($data['status'])) {
        throw new \InvalidArgumentException('Missing required parameters for updating room.');
    }

    $query = "UPDATE {$this->table} 
              SET name = :name, 
                  type = :type, 
                  price = :price, 
                  capacity = :capacity, 
                  status = :status 
              WHERE id = :id";

    $stmt = self::$db->prepare($query);

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
    $stmt->bindParam(':type', $data['type'], PDO::PARAM_STR);
    $stmt->bindParam(':price', $data['price'], PDO::PARAM_STR); 
    $stmt->bindParam(':capacity', $data['capacity'], PDO::PARAM_INT);
    $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);

    return $stmt->execute();
}

}

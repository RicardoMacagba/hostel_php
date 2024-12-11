<?php

namespace App\Models;

class Room extends Model
{
    public function __construct()
    {
        // Specify the table name
        parent::__construct('rooms');
        
        // Specify the primary key
        $this->primaryKey = 'id';

        // Set validation rules for the model
        $this->setRules([
            'name' => ['required' => true, 'maxLength' => 255],
            'type' => ['required' => true, 'maxLength' => 255],
            'price'     => ['required' => true, 'numeric' => true, 'minValue' => 0],
        ]);
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

        return $types[$this->room_type] ?? 'Unknown';
    }

    /**
     * Format the room price for display.
     *
     * @return string
     */
    public function getFormattedPrice()
    {
        return "$" . number_format($this->price, 2);
    }

    /**
     * Get all rooms of a specific type.
     *
     * @param string $type
     * @return Room[]
     */
    public static function getRoomsByType($type)
    {
        return self::filter(['room_type =' => $type]);
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
}

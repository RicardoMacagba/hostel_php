<?php

namespace App\Controllers;

use App\Models\Room;
use App\Models\User;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->layout = "main"; //set default layout for all actions
    }
    public function index()
    {
        $userCount = User::count();
        //$bookingCount = Booking::count();
        $availableRoomCount = Room::count(['status' => 'available']);


        $recentActivities = [
            ['icon' => 'person_add', 'description' => 'New user Ricardo Macagba registered.', 'time' => '2 hours ago'],
            ['icon' => 'check_circle', 'description' => 'Room #101 booked by Jane Smith.', 'time' => '1 day ago'],
            ['icon' => 'cancel', 'description' => 'Booking #1203 was canceled.', 'time' => '3 days ago'],
        ];

        $this->pageTitle = "Home";

        //$data = "some data";
        $rooms = Room::findAll();

        $this->view("home/index", [
            // "data" => $data,
            "rooms" => $rooms,
            'userCount' => $userCount,
            //'bookingCount' => $bookingCount,
            'availableRooms' => $availableRoomCount,
            'recentActivities' => $recentActivities,
        ]);
    }

    // Sample web page to use api request
    public function sampleApiRequest()
    {
        //$this->layout = 'simple';
        $this->pageTitle = "Sample Api Request From Web";
        $this->view('home/apiRequestSample');
    }
}

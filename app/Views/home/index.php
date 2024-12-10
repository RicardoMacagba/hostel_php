<?php
//Set menu items
$menuItems = [
    ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => route('/'), 'active' => true],
    ['label' => 'Users', 'icon' => 'group', 'route' => route('/users'), 'active' => false],
    ['label' => 'Sample API Request', 'icon' => 'api', 'route' => route('/sample-api-request'), 'active' => false],
    ['label' => 'Room', 'icon' => 'hotel', 'route' => route('/table_room'), 'active' => false],
];

?>


<!-- This will go to the content area -->

<div class="py-6">
    <h1 class="text-3xl font-bold mb-6">Welcome to Hostel Dashboard</h1>

    <!-- Overview Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card: Users -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="flex items-center">
                <span class="material-icons text-blue-500 text-4xl mr-4">group</span>
                <div>
                    <h2 class="text-xl font-semibold">Users</h2>
                    <p class="text-gray-600 text-sm">Total registered users</p>
                </div>
            </div>
            <p class="mt-4 text-2xl font-bold"><?= htmlspecialchars($userCount ?? 0) ?></p>
        </div>

        <!-- Card: Bookings -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="flex items-center">
                <span class="material-icons text-green-500 text-4xl mr-4">event</span>
                <div>
                    <h2 class="text-xl font-semibold">Bookings</h2>
                    <p class="text-gray-600 text-sm">Total bookings</p>
                </div>
            </div>
            <p class="mt-4 text-2xl font-bold"><?= htmlspecialchars($bookingCount ?? 0) ?></p>
        </div>

        <!-- Card: Available Rooms -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="flex items-center">
                <span class="material-icons text-red-500 text-4xl mr-4">hotel</span>
                <div>
                    <h2 class="text-xl font-semibold">Available Rooms</h2>
                    <p class="text-gray-600 text-sm">Rooms ready for booking</p>
                </div>
            </div>
            <p class="mt-4 text-2xl font-bold"><?= htmlspecialchars($availableRooms ?? 0) ?></p>
        </div>
    </div>

    <!-- Recent Activities Section -->
    <div class="mt-10 bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-semibold mb-4">Recent Activities</h2>
        <ul class="divide-y divide-gray-200">
            <?php if (!empty($recentActivities)): ?>
                <?php foreach ($recentActivities as $activity): ?>
                    <li class="py-4 flex items-center">
                        <span class="material-icons text-blue-500 mr-4"><?= htmlspecialchars($activity['icon']) ?></span>
                        <p class="flex-1"><?= htmlspecialchars($activity['description']) ?></p>
                        <span class="text-gray-500 text-sm"><?= htmlspecialchars($activity['time']) ?></span>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="py-4 text-gray-500">No recent activities found.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>
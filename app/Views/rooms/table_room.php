<?php
// Menu items
$menuItems = [
    ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => route('/'), 'active' => true],
    ['label' => 'Users', 'icon' => 'group', 'route' => route('/users'), 'active' => false],
    ['label' => 'Sample API Request', 'icon' => 'api', 'route' => route('/sample-api-request'), 'active' => false],
    ['label' => 'Room', 'icon' => 'hotel', 'route' => route('/listRooms'), 'active' => true],
];

// $rooms: list of rooms passed from the controller
?>

<div class="container mx-auto py-6">
    <h1 class="text-3xl font-bold mb-6">Room Management</h1>

    <!-- Add Room button -->
    <div class="mb-4">
        <a href="<?= route('/add_rooms') ?>" class="text-white bg-green-500 py-2 px-4 rounded-md hover:bg-green-600 inline-flex items-center">
            <span class="material-icons mr-2">add</span>
            Add Room
        </a>
    </div>

    <!-- Search field aligned to the right -->
    <form method="GET" action="<?= route('/listRooms') ?>">
        <div class="flex justify-end items-center mb-4 space-x-2">
            <div class="relative w-64">
                <input
                    type="text"
                    id="searchInput"
                    name="q"
                    value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                    class="border border-gray-300 p-2 pl-2 pr-12 rounded-md w-full"
                    placeholder="Search...">
            </div>
            <button type="submit"
                class="flex items-center text-white bg-blue-500 border border-blue-500 py-2 px-4 rounded-md hover:bg-blue-600 hover:border-blue-600">
                <span class="material-icons mr-2">search</span>
                <span>Search</span>
            </button>
        </div>
    </form>

    <!-- Table -->
    <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
        <thead>
            <tr class="bg-gray-200 text-left">
                <th class="py-3 px-4 text-sm font-medium text-gray-700">No.</th>
                <th class="py-3 px-4 text-sm font-medium text-gray-700">Room Name</th>
                <th class="py-3 px-4 text-sm font-medium text-gray-700">Room Type</th>
                <th class="py-3 px-4 text-sm font-medium text-gray-700">Capacity</th>
                <th class="py-3 px-4 text-sm font-medium text-gray-700">Status</th>
                <th class="py-3 px-4 text-sm font-medium text-gray-700">Created At</th>
                <th class="py-3 px-4 text-sm font-medium text-gray-700">Actions</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php if (!empty($rooms)): ?>
                <?php foreach ($rooms as $key => $room): ?>
                    <tr class="<?= $key % 2 === 0 ? 'bg-gray-50' : 'bg-white' ?>">
                        <td class="py-3 px-4 text-sm text-gray-700"><?= $key + 1 ?></td>
                        <td class="py-3 px-4 text-sm text-gray-700"><?= htmlspecialchars($room->name) ?></td>
                        <td class="py-3 px-4 text-sm text-gray-700"><?= htmlspecialchars($room->type) ?></td>
                        <td class="py-3 px-4 text-sm text-gray-700"><?= htmlspecialchars($room->capacity) ?></td>
                        <td class="py-3 px-4 text-sm text-gray-700"><?= htmlspecialchars($room->status) ?></td>
                        <td class="py-3 px-4 text-sm text-gray-700"><?= date('Y-m-d H:i', strtotime($room->created_at)) ?></td>
                        <td class="py-3 px-4 text-sm text-gray-700 space-x-2">
                            <a href="<?= route('/edit_rooms', ['id' => $room->id]) ?>"
                                class="inline-flex items-center text-white bg-green-500 border border-green-500 py-2 px-4 rounded-md hover:bg-green-600 hover:border-green-600">
                                <span class="material-icons mr-2">edit</span>
                                Edit
                            </a>
                            <form method="POST" class="inline-block">
                                <!-- CSRF token -->
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES) ?>" />
                                <input type="hidden" name="delete_room_id" value="<?= $room->id ?>" />
                                <button type="submit"
                                    onclick="return confirm('Are you sure you want to delete this room?');"
                                    class="inline-flex items-center text-white bg-red-500 border border-red-500 py-2 px-4 rounded-md hover:bg-red-600 hover:border-red-600">
                                    <span class="material-icons mr-2">delete</span>
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="py-3 px-4 text-sm text-center text-gray-500">No rooms found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
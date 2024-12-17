<?php
// Menu items
$menuItems = [
    ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => route('/'), 'active' => false],
    ['label' => 'Users', 'icon' => 'group', 'route' => route('/users'), 'active' => false],
    ['label' => 'Sample API Request', 'icon' => 'api', 'route' => route('/sample-api-request'), 'active' => false],
    ['label' => 'Rooms', 'icon' => 'hotel', 'route' => route('/listRooms'), 'active' => true],
];
?>

<div class="container mx-auto py-6">
    <h1 class="text-3xl font-bold mb-6">Edit Room</h1>

    <?php if (!empty($rooms)): ?>
        <!-- Room Edit Form -->
        <form method="PUT" enctype="multipart/form-data" class="space-y-4">
            <!-- CSRF token for security -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES); ?>" />
            <input type="hidden" name="id" value="<?= htmlspecialchars($rooms->id ?? '', ENT_QUOTES); ?>" />

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Room Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($rooms->name ?? '', ENT_QUOTES); ?>"
                    class="border border-gray-300 p-2 rounded-md w-full" required>
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Room Type</label>
                <select id="type" name="type" class="border border-gray-300 p-2 rounded-md w-full" required>
                    <option value="Standard" <?= ($rooms->type === 'Standard') ? 'selected' : '' ?>>Standard</option>
                    <option value="Luxury" <?= ($rooms->type === 'Luxury') ? 'selected' : '' ?>>Luxury</option>
                    <option value="Suite" <?= ($rooms->type === 'Suite') ? 'selected' : '' ?>>Suite</option>
                </select>
            </div>

            <div>
                <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity</label>
                <input type="number" id="capacity" name="capacity" value="<?= htmlspecialchars($rooms->capacity ?? '', ENT_QUOTES); ?>"
                    class="border border-gray-300 p-2 rounded-md w-full" required>
            </div>

            <div>
                <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                <input type="number" id="price" name="price" value="<?= htmlspecialchars($rooms->price ?? '', ENT_QUOTES); ?>"
                    class="border border-gray-300 p-2 rounded-md w-full" step="0.01" required>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" class="border border-gray-300 p-2 rounded-md w-full" required>
                    <option value="available" <?= ($rooms->status === 'available') ? 'selected' : '' ?>>Available</option>
                    <option value="occupied" <?= ($rooms->status === 'occupied') ? 'selected' : '' ?>>Occupied</option>
                    <option value="under_maintenance" <?= ($rooms->status === 'under_maintenance') ? 'selected' : '' ?>>Under Maintenance</option>
                </select>
            </div>

            <div>
                <label for="room_image" class="block text-sm font-medium text-gray-700">Room Image (Optional)</label>
                <input type="file" id="room_image" name="room_image"
                    class="border border-gray-300 p-2 rounded-md w-full">
                <?php if (!empty($rooms->image)): ?>
                    <p class="text-sm text-gray-500 mt-1">Current Image: <img src="<?= BASE_URL . 'storage/images/rooms/' . htmlspecialchars($rooms->image, ENT_QUOTES); ?>" alt="Room Image" class="w-32 h-32 rounded-md mt-2"></p>
                <?php endif; ?>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="<?= route('/listRooms') ?>" class="text-gray-700 bg-gray-200 py-2 px-4 rounded-md hover:bg-gray-300">Cancel</a>
                <button type="submit" class="text-white bg-blue-500 py-2 px-4 rounded-md hover:bg-blue-600">Save Changes</button>
            </div>
        </form>
    <?php else: ?>
        <p class="text-red-500 text-lg">Room not found.</p>
        <a href="<?= route('/table_room') ?>" class="text-gray-700 bg-gray-200 py-2 px-4 rounded-md hover:bg-gray-300">Go Back</a>
    <?php endif; ?>
</div>

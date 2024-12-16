<?php
// Menu items
$menuItems = [
    ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => route('/'), 'active' => false],
    ['label' => 'Users', 'icon' => 'group', 'route' => route('/users'), 'active' => false],
    ['label' => 'Sample API Request', 'icon' => 'api', 'route' => route('/sample-api-request'), 'active' => false],
    ['label' => 'Room', 'icon' => 'hotel', 'route' => route('/listRooms'), 'active' => true],
];
?>

<div class="container mx-auto py-6">
    <h1 class="text-3xl font-bold mb-6">Edit Room</h1>

    <!-- Room Edit Form -->
    <form method="POST" class="space-y-4">
        <!-- CSRF token for security -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES); ?>" />

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Room Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($room->name ?? '', ENT_QUOTES) ?>"
                class="border border-gray-300 p-2 rounded-md w-full" required>
        </div>

        <div>
            <label for="type" class="block text-sm font-medium text-gray-700">Room Type</label>
            <select id="type" name="type" class="border border-gray-300 p-2 rounded-md w-full" required>
                <option value="Single" <?= ($room->type ?? '') === 'Single' ? 'selected' : '' ?>>Single</option>
                <option value="Double" <?= ($room->type ?? '') === 'Double' ? 'selected' : '' ?>>Double</option>
                <option value="Suite" <?= ($room->type ?? '') === 'Suite' ? 'selected' : '' ?>>Suite</option>
            </select>
        </div>

        <div>
            <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
            <input type="number" id="price" name="price" value="<?= htmlspecialchars($room->price ?? '', ENT_QUOTES) ?>"
                class="border border-gray-300 p-2 rounded-md w-full" step="0.01" required>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="<?= route('/table_user') ?>" class="text-gray-700 bg-gray-200 py-2 px-4 rounded-md hover:bg-gray-300">Cancel</a>
            <button type="submit" class="text-white bg-blue-500 py-2 px-4 rounded-md hover:bg-blue-600">Save Changes</button>
        </div>
    </form>
</div>
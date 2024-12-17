<?php
// Menu items
$menuItems = [
    ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => route('/'), 'active' => false],
    ['label' => 'Users', 'icon' => 'group', 'route' => route('/users'), 'active' => false],
    ['label' => 'Sample API Request', 'icon' => 'api', 'route' => route('/sample-api-request'), 'active' => false],
    ['label' => 'Room', 'icon' => 'hotel', 'route' => route('/listRooms'), 'active' => true],
];
?>


<form method="POST" class="max-w-md mt-20 mx-auto p-6 bg-white rounded shadow-md" enctype="multipart/form-data">
    <h2 class="text-3xl font-semibold mb-4">Add Room</h2>

    <!-- Display error messages if necessary -->
    <?php if (!empty($errors)) { ?>
        <div role="alert">
            <div class="border-l-4 border-red-400 rounded bg-red-100 px-4 py-3 text-red-700 mb-4">
                <?php foreach ($errors as $error) { ?>
                    <span class="inline-block"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></span>
                <?php } ?>
            </div>
        </div>
    <?php } ?>

    <!-- Always include the CSRF token in a hidden input field for POST method only -->
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES); ?>" />

    <!-- Room Name input -->
    <div class="mb-4">
        <label for="room_name" class="block text-sm font-medium">Room Name</label>
        <input type="text" name="name" id="name" required maxlength="255"
            class="w-full mt-1 p-2 border border-gray-300 rounded-md"
            value="<?php echo htmlspecialchars($model->name ?? '', ENT_QUOTES); ?>"
            placeholder="Enter room name" />
    </div>

    <!-- Room Type input -->
    <div class="mb-4">
        <label for="room_type" class="block text-sm font-medium">Room Type</label>
        <select name="type" id="type" required
            class="w-full mt-1 p-2 border border-gray-300 rounded-md">
            <option value="" disabled <?php echo empty($model->type) ? 'selected' : ''; ?>>Select room type</option>
            <option value="standard" <?php echo ($model->type ?? '') === 'standard' ? 'selected' : ''; ?>>Standard Room</option>
            <option value="luxury" <?php echo ($model->type ?? '') === 'luxury' ? 'selected' : ''; ?>>Luxury Room</option>
            <option value="suite" <?php echo ($model->type ?? '') === 'suite' ? 'selected' : ''; ?>>Suite</option>
        </select>
    </div>

    <!-- Capacity input -->
    <div class="mb-4">
        <label for="capacity" class="block text-sm font-medium">Capacity</label>
        <input type="number" name="capacity" id="capacity" required min="1" step="1"
            class="w-full mt-1 p-2 border border-gray-300 rounded-md"
            value="<?php echo htmlspecialchars($model->capacity ?? '', ENT_QUOTES); ?>"
            placeholder="Enter room capacity" />
    </div>

    <!-- Price input -->
    <div class="mb-4">
        <label for="price" class="block text-sm font-medium">Price</label>
        <input type="number" name="price" id="price" required min="0" step="0.01"
            class="w-full mt-1 p-2 border border-gray-300 rounded-md"
            value="<?php echo htmlspecialchars($model->price ?? '', ENT_QUOTES); ?>"
            placeholder="Enter price" />
    </div>

    <!-- Status input -->
    <div class="mb-4">
        <label for="status" class="block text-sm font-medium">Status</label>
        <select name="status" id="status" required
            class="w-full mt-1 p-2 border border-gray-300 rounded-md">
            <option value="" disabled <?php echo empty($model->status) ? 'selected' : ''; ?>>Select room status</option>
            <option value="available" <?php echo ($model->status ?? '') === 'available' ? 'selected' : ''; ?>>Available</option>
            <option value="occupied" <?php echo ($model->status ?? '') === 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
        </select>
    </div>

    <!-- Image upload -->
    <div class="mb-4">
        <label for="room_image" class="block text-sm font-medium">Room Image</label>
        <input type="file" name="room_image" id="room_image" class="w-full mt-1 p-2 border border-gray-300 rounded-md" />
    </div>

    <!-- Submit button -->
    <button type="submit" name="add_room" class="w-full py-2 mt-4 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
        Add Room
    </button>

    <!-- Link to room list -->
    <div class="pt-2 text-sm">
        <p class="text-gray-600">
            Want to view all rooms? <a href="<?=BASE_URL."/table_room"?>" class="text-blue-500 hover:text-blue-700">Go to Rooms list</a>.
        </p>
    </div>
</form>

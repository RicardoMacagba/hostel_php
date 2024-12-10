<form method="POST" action="add_room_handler.php" class="max-w-md mt-20 mx-auto p-6 bg-white rounded shadow-md">
    <h2 class="text-3xl font-semibold mb-4">Add Room</h2>

    <!-- Display error messages -->
    <?php if (!empty($errors)) { ?>
        <div role="alert" class="border-l-4 border-red-400 rounded bg-red-100 px-4 py-3 text-red-700 mb-4">
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $error) { ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES); ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>

    <!-- CSRF token for security -->
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES); ?>" />

    <!-- Room Name input -->
    <div class="mb-4">
        <label for="room_name" class="block text-sm font-medium">Room Name</label>
        <input
            type="text"
            name="room_name"
            id="room_name"
            required
            maxlength="255"
            class="w-full mt-1 p-2 border border-gray-300 rounded-md"
            value="<?php echo htmlspecialchars($model->room_name ?? '', ENT_QUOTES); ?>"
            placeholder="Enter room name" />
    </div>

    <!-- Room Type input -->
    <div class="mb-4">
        <label for="room_type" class="block text-sm font-medium">Room Type</label>
        <select
            name="room_type"
            id="room_type"
            required
            class="w-full mt-1 p-2 border border-gray-300 rounded-md">
            <option value="" disabled <?php echo empty($model->room_type) ? 'selected' : ''; ?>>Select room type</option>
            <option value="standard" <?php echo ($model->room_type ?? '') === 'standard' ? 'selected' : ''; ?>>Standard Room</option>
            <option value="luxury" <?php echo ($model->room_type ?? '') === 'luxury' ? 'selected' : ''; ?>>Luxury Room</option>
            <option value="suite" <?php echo ($model->room_type ?? '') === 'suite' ? 'selected' : ''; ?>>Suite</option>
        </select>
    </div>

    <!-- Price input -->
    <div class="mb-4">
        <label for="price" class="block text-sm font-medium">Price</label>
        <input
            type="number"
            name="price"
            id="price"
            required
            min="0"
            step="0.01"
            class="w-full mt-1 p-2 border border-gray-300 rounded-md"
            value="<?php echo htmlspecialchars($model->price ?? '', ENT_QUOTES); ?>"
            placeholder="Enter price in USD" />
    </div>

    <!-- Submit button -->
    <button type="submit" name="add_room" class="w-full py-2 mt-4 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
        Add Room
    </button>
</form>
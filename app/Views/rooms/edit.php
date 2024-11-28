<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Room</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <h1>Edit Room</h1>
    <a href="/rooms">Back to Room List</a>

    <form action="/rooms/<?= $room['room_id'] ?>" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="room_type">Room Type</label>
            <input type="text" id="room_type" name="room_type" value="<?= htmlspecialchars($room['room_type']) ?>" required>
        </div>

        <div class="form-group">
            <label for="room_description">Description</label>
            <textarea id="room_description" name="room_description" required><?= htmlspecialchars($room['room_description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="room_price">Price</label>
            <input type="number" id="room_price" name="room_price" value="<?= htmlspecialchars($room['room_price']) ?>" required>
        </div>

        <div class="form-group">
            <label for="room_image">Image</label>
            <input type="file" id="room_image" name="room_image">
        </div>

        <button type="submit" class="button">Save Changes</button>
    </form>
</body>
</html>

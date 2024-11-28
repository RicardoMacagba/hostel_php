<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Room Details</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <h1>Room Details</h1>
    <a href="/rooms">Back to Room List</a>

    <div class="room-details">
        <img src="<?= htmlspecialchars($room['room_image']) ?>" alt="<?= htmlspecialchars($room['room_type']) ?>" class="room-image">
        <h2><?= htmlspecialchars($room['room_type']) ?></h2>
        <p><?= htmlspecialchars($room['room_description']) ?></p>
        <p>Price: $<?= htmlspecialchars($room['room_price']) ?> per night</p>
    </div>

    <a href="/rooms/<?= $room['room_id'] ?>/edit" class="button">Edit</a>
    <form method="POST" action="/rooms/<?= $room['room_id'] ?>/delete" style="display:inline;">
        <button type="submit" class="button danger">Delete</button>
    </form>
</body>
</html>

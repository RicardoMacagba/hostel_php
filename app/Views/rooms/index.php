<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Room List</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <h1>Available Rooms</h1>
    <a href="/rooms/create">Add New Room</a>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Description</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rooms as $room): ?>
                <tr>
                    <td><?= htmlspecialchars($room['room_id']) ?></td>
                    <td><?= htmlspecialchars($room['room_type']) ?></td>
                    <td><?= htmlspecialchars($room['room_description']) ?></td>
                    <td>$<?= htmlspecialchars($room['room_price']) ?></td>
                    <td>
                        <a href="/rooms/<?= $room['room_id'] ?>">View</a>
                        <a href="/rooms/<?= $room['room_id'] ?>/edit">Edit</a>
                        <form method="POST" action="/rooms/<?= $room['room_id'] ?>/delete" style="display:inline;">
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>

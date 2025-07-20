<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$uid = $_SESSION['user_id'];

$res = $conn->query("
    SELECT r.*, s.title, u.name as requester_name
    FROM requests r
    JOIN skills s ON r.skill_id = s.id
    JOIN users u ON r.requester_id = u.id
    WHERE r.receiver_id = $uid AND r.status = 'pending'
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-50 to-teal-50 min-h-screen">
    <div class="max-w-xl mx-auto p-8 bg-white rounded-xl shadow-xl mt-16">
        <h1 class="text-2xl font-bold text-blue-700 mb-6">Connection Requests</h1>
        <?php if ($res->num_rows > 0): ?>
            <?php while($req = $res->fetch_assoc()): ?>
                <div class="border rounded-xl p-4 mb-4 flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-2 md:mb-0">
                        <strong><?php echo htmlspecialchars($req['requester_name']); ?></strong> requested to connect for <em><?php echo htmlspecialchars($req['title']); ?></em>
                    </div>
                    <div class="flex gap-2">
                        <a href="accept_request.php?id=<?php echo $req['id']; ?>" class="bg-green-500 text-white px-4 py-1 rounded-full hover:bg-green-600">Accept</a>
                        <a href="decline_request.php?id=<?php echo $req['id']; ?>" class="bg-red-500 text-white px-4 py-1 rounded-full hover:bg-red-600">Decline</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-gray-500">No connection requests.</div>
        <?php endif; ?>
    </div>
</body>
</html>

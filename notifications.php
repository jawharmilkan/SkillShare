<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$uid = $_SESSION['user_id'];

// Get recent requests where the user is skill owner (received notifications)
$sql = "
    SELECT r.*, u.name as requester_name, s.title as skill_title
    FROM requests r
    JOIN users u ON r.requester_id = u.id
    JOIN skills s ON r.skill_id = s.id
    WHERE s.user_id = $uid AND r.status != 'pending'
    ORDER BY r.created_at DESC
    LIMIT 10
";
$res = $conn->query($sql);

// Get recent requests you have sent (sent notifications)
$sentSql = "
    SELECT r.*, u.name as owner_name, s.title as skill_title
    FROM requests r
    JOIN skills s ON r.skill_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE r.requester_id = $uid AND r.status != 'pending'
    ORDER BY r.created_at DESC
    LIMIT 10
";
$sent = $conn->query($sentSql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Notifications - SkillShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-50 to-teal-50 min-h-screen">
<header class="flex items-center justify-between px-6 py-4 bg-white shadow-lg">
    <div class="text-2xl font-bold text-blue-700">SkillShare</div>
    <nav class="space-x-4 hidden md:flex">
      <a href="dashboard.php" class="hover:text-blue-500">Home</a>
      <a href="skills.php" class="hover:text-blue-500">Browse Skills</a>
      <a href="post_skill.php" class="hover:text-blue-500">Post Skill</a>
      <a href="profile.php" class="hover:text-blue-500">Profile</a>
      <a href="notifications.php" class="text-blue-700 font-bold">Notifications</a>
    </nav>
</header>
<main class="max-w-4xl mx-auto py-12 px-4">
    <h1 class="text-3xl font-bold text-blue-700 mb-8">Notifications</h1>
    
    <section class="mb-10">
      <h2 class="text-xl font-semibold mb-4">Requests you received</h2>
      <?php if ($res && $res->num_rows > 0): ?>
        <ul class="space-y-3">
        <?php while($row = $res->fetch_assoc()): ?>
          <li class="bg-white rounded-xl shadow px-6 py-3 flex items-center justify-between">
            <span>
              <span class="font-bold"><?php echo htmlspecialchars($row['requester_name']); ?></span>
              requested to connect for 
              <span class="text-blue-700 font-semibold"><?php echo htmlspecialchars($row['skill_title']); ?></span>
              <span class="ml-2 text-sm text-gray-500">(Status: <?php echo htmlspecialchars($row['status']); ?>)</span>
            </span>
            <span class="text-xs text-gray-400"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></span>
          </li>
        <?php endwhile; ?>
        </ul>
      <?php else: ?>
        <div class="text-gray-500">No notifications received yet.</div>
      <?php endif; ?>
    </section>
    <section>
      <h2 class="text-xl font-semibold mb-4">Requests you sent</h2>
      <?php if ($sent && $sent->num_rows > 0): ?>
        <ul class="space-y-3">
        <?php while($row = $sent->fetch_assoc()): ?>
          <li class="bg-white rounded-xl shadow px-6 py-3 flex items-center justify-between">
            <span>
              You requested 
              <span class="text-blue-700 font-semibold"><?php echo htmlspecialchars($row['skill_title']); ?></span>
              (Skill owner: <span class="font-bold"><?php echo htmlspecialchars($row['owner_name']); ?></span>)
              <span class="ml-2 text-sm text-gray-500">(Status: <?php echo htmlspecialchars($row['status']); ?>)</span>
            </span>
            <span class="text-xs text-gray-400"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></span>
          </li>
        <?php endwhile; ?>
        </ul>
      <?php else: ?>
        <div class="text-gray-500">No sent requests notifications yet.</div>
      <?php endif; ?>
    </section>
</main>
<footer class="mt-16 py-8 bg-white text-center shadow-inner text-gray-500">
    &copy; 2025 SkillShare. Built for your local community.
</footer>
</body>
</html>

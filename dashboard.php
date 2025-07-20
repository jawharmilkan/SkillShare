<?php
session_start();
require 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$uid = $_SESSION['user_id'];

// Unread chat message count
$unread_count = 0;
$res = $conn->query("
    SELECT COUNT(m.id) AS cnt
    FROM messages m
    JOIN requests r ON m.request_id = r.id
    WHERE m.receiver_id = $uid AND m.is_read = 0 AND r.status = 'accepted'
");
if ($res && $row = $res->fetch_assoc()) {
    $unread_count = $row['cnt'];
}

// User's name for greeting
$resUser = $conn->query("SELECT name FROM users WHERE id='$uid' LIMIT 1");
$user = $resUser ? $resUser->fetch_assoc() : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SkillShare - Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-50 to-teal-50 min-h-screen">
  <!-- Header -->
  <header class="flex items-center justify-between px-6 py-4 bg-white shadow-lg">
    <div class="text-2xl font-bold text-blue-700">SkillShare</div>
    <nav class="space-x-4 hidden md:flex items-center">
      <a href="dashboard.php" class="text-blue-600 font-semibold">Home</a>
      <a href="skills.php" class="hover:text-blue-500">Browse Skills</a>
      <a href="post_skill.php" class="hover:text-blue-500">Post Skill</a>
      <a href="profile.php" class="hover:text-blue-500">Profile</a>
      <a href="logout.php" class="bg-red-100 text-red-600 font-bold px-5 py-2 rounded-full shadow hover:bg-red-200 transition">Logout</a>
    </nav>
    <button id="menu-btn" class="block md:hidden text-2xl text-blue-700 focus:outline-none">&#9776;</button>
  </header>
  <!-- Mobile Menu -->
  <div id="mobile-menu" class="md:hidden bg-white px-6 py-4 space-y-2 hidden shadow-lg">
    <a href="dashboard.php" class="block text-blue-600 font-semibold">Home</a>
    <a href="skills.php" class="block hover:text-blue-500">Browse Skills</a>
    <a href="post_skill.php" class="block hover:text-blue-500">Post Skill</a>
    <a href="profile.php" class="block hover:text-blue-500">Profile</a>
    <a href="logout.php" class="block bg-red-100 text-red-600 font-bold px-5 py-2 rounded-full shadow hover:bg-red-200 transition">Logout</a>
  </div>

  <!-- Main Dashboard -->
  <main class="max-w-6xl mx-auto py-16 px-4">
    <div class="bg-white rounded-3xl shadow-xl p-10 text-center">
      <h1 class="text-4xl font-extrabold text-blue-700 mb-2">
        Welcome<?php if ($user) echo ', ' . htmlspecialchars($user['name']); ?>!
      </h1>
      <p class="text-lg text-gray-500 mb-8">
        This is your SkillShare dashboard. Use the menu below to explore all features.
      </p>
      <div class="grid md:grid-cols-4 gap-8 mt-8">
        <a href="skills.php" class="bg-gradient-to-br from-blue-100 to-blue-300 p-6 rounded-2xl shadow hover:scale-105 transition-transform text-blue-900 font-bold text-lg flex flex-col items-center">
          <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"></path><path d="M12 14l6.16-3.422A12.083 12.083 0 0012 21.5a12.083 12.083 0 00-6.16-10.922L12 14z"></path></svg>
          Browse Skills
        </a>
        <a href="post_skill.php" class="bg-gradient-to-br from-teal-100 to-teal-300 p-6 rounded-2xl shadow hover:scale-105 transition-transform text-teal-900 font-bold text-lg flex flex-col items-center">
          <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
          Post a Skill
        </a>
        <a href="profile.php" class="bg-gradient-to-br from-purple-100 to-purple-300 p-6 rounded-2xl shadow hover:scale-105 transition-transform text-purple-900 font-bold text-lg flex flex-col items-center relative">
          <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 14a4 4 0 01-8 0m8 0V8a4 4 0 00-8 0v6m8 0a4 4 0 01-8 0"></path></svg>
          Profile
        </a>
        <a href="notifications.php" class="bg-gradient-to-br from-pink-100 to-pink-300 p-6 rounded-2xl shadow hover:scale-105 transition-transform text-pink-900 font-bold text-lg flex flex-col items-center relative">
          <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-5-5.917V5a2 2 0 10-4 0v.083A6.002 6.002 0 004 11v3.159c0 .538-.214 1.055-.595 1.436L2 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
          Notifications
          <?php if ($unread_count > 0): ?>
            <span class="absolute -top-2 -right-3 bg-red-600 text-white rounded-full text-xs px-2 py-0.5 font-bold animate-pulse"><?php echo $unread_count; ?></span>
          <?php endif; ?>
        </a>
        <a href="connections.php" class="bg-gradient-to-br from-yellow-100 to-yellow-300 p-6 rounded-2xl shadow hover:scale-105 transition-transform text-yellow-900 font-bold text-lg flex flex-col items-center">
          <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 8a4 4 0 108 0 4 4 0 00-8 0zM3 20a8 8 0 0118 0"></path></svg>
          My Connections
        </a>
        <a href="messages.php" class="bg-gradient-to-br from-cyan-100 to-cyan-300 p-6 rounded-2xl shadow hover:scale-105 transition-transform text-cyan-900 font-bold text-lg flex flex-col items-center">
          <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8s-9-3.582-9-8a9 9 0 1118 0z"></path></svg>
          Messages
          <?php if ($unread_count > 0): ?>
            <span class="absolute -top-2 -right-3 bg-red-600 text-white rounded-full text-xs px-2 py-0.5 font-bold animate-pulse"><?php echo $unread_count; ?></span>
          <?php endif; ?>
        </a>
        <a href="leaderboard.php" class="bg-gradient-to-br from-green-100 to-green-300 p-6 rounded-2xl shadow hover:scale-105 transition-transform text-green-900 font-bold text-lg flex flex-col items-center">
          <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 17v-5H7v5H3v2h18v-2h-4v-8h-2v8H9z"></path></svg>
          Leaderboard
        </a>
        <a href="my_reviews.php" class="bg-gradient-to-br from-indigo-100 to-indigo-300 p-6 rounded-2xl shadow hover:scale-105 transition-transform text-indigo-900 font-bold text-lg flex flex-col items-center">
          <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7"></path></svg>
          My Reviews
        </a>
        <a href="settings.php" class="bg-gradient-to-br from-gray-100 to-gray-300 p-6 rounded-2xl shadow hover:scale-105 transition-transform text-gray-900 font-bold text-lg flex flex-col items-center">
          <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 6V4m0 16v-2m8-8h2M4 12H2m15.364 6.364l1.414 1.414M6.343 6.343l-1.414-1.414m12.728 0l1.414 1.414M6.343 17.657l-1.414 1.414"></path></svg>
          Settings
        </a>
      </div>
      <?php if ($unread_count > 0): ?>
        <div class="mt-8 text-center">
          <span class="bg-red-100 text-red-700 rounded px-3 py-1 font-semibold text-sm shadow">You have <?php echo $unread_count; ?> unread chat message<?php echo $unread_count > 1 ? 's' : ''; ?>!</span>
        </div>
      <?php endif; ?>
    </div>
  </main>
  <footer class="mt-16 py-8 bg-white text-center shadow-inner text-gray-500">
    &copy; 2025 SkillShare. Built for your local community.
  </footer>
  <script>
    document.getElementById('menu-btn').addEventListener('click', function() {
      const menu = document.getElementById('mobile-menu');
      menu.classList.toggle('hidden');
    });
  </script>
</body>
</html>

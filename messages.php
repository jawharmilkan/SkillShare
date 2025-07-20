<?php
session_start();
require 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch accepted requests involving the user (either as requester or skill owner)
$sql = "
    SELECT 
        r.*, 
        s.title AS skill_title, 
        u1.name AS requester_name, 
        u2.name AS owner_name,
        u1.photo AS requester_photo,
        u2.photo AS owner_photo,
        (SELECT COUNT(*) FROM messages m WHERE m.request_id = r.id AND m.receiver_id = '$user_id' AND m.is_read = 0) AS unread_count
    FROM requests r
    JOIN skills s ON r.skill_id = s.id
    JOIN users u1 ON r.requester_id = u1.id
    JOIN users u2 ON s.user_id = u2.id
    WHERE (r.requester_id = '$user_id' OR s.user_id = '$user_id')
      AND r.status = 'accepted'
    ORDER BY r.created_at DESC
";
$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SkillShare - Messages</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-50 to-teal-50 min-h-screen">
  <!-- Header -->
  <header class="flex items-center justify-between px-6 py-4 bg-white shadow-lg">
    <div class="text-2xl font-bold text-blue-700">SkillShare</div>
    <nav class="space-x-4 hidden md:flex">
      <a href="dashboard.php" class="hover:text-blue-500">Home</a>
      <a href="skills.php" class="hover:text-blue-500">Browse Skills</a>
      <a href="post_skill.php" class="hover:text-blue-500">Post Skill</a>
      <a href="profile.php" class="hover:text-blue-500">Profile</a>
      <a href="logout.php" class="bg-red-100 text-red-600 font-bold px-5 py-2 rounded-full shadow hover:bg-red-200 transition">Logout</a>
    </nav>
    <button id="menu-btn" class="block md:hidden text-2xl text-blue-700 focus:outline-none">&#9776;</button>
  </header>
  <div id="mobile-menu" class="md:hidden bg-white px-6 py-4 space-y-2 hidden shadow-lg">
    <a href="dashboard.php" class="block hover:text-blue-500">Home</a>
    <a href="skills.php" class="block hover:text-blue-500">Browse Skills</a>
    <a href="post_skill.php" class="block hover:text-blue-500">Post Skill</a>
    <a href="profile.php" class="block hover:text-blue-500">Profile</a>
    <a href="logout.php" class="block bg-red-100 text-red-600 font-bold px-5 py-2 rounded-full shadow hover:bg-red-200 transition">Logout</a>
  </div>

  <main class="max-w-4xl mx-auto py-16 px-4">
    <div class="bg-white rounded-3xl shadow-xl p-8">
      <h1 class="text-3xl font-extrabold text-blue-700 mb-6 text-center">My Conversations</h1>
      <?php if ($res && $res->num_rows > 0): ?>
        <div class="space-y-6">
          <?php while ($row = $res->fetch_assoc()): 
            $is_me_requester = $row['requester_id'] == $user_id;
            $friend_name = $is_me_requester ? $row['owner_name'] : $row['requester_name'];
            $friend_photo = $is_me_requester ? $row['owner_photo'] : $row['requester_photo'];
            $unread = $row['unread_count'];
            ?>
            <div class="flex items-center gap-5 bg-cyan-50 rounded-xl p-5 shadow">
              <img src="<?php echo $friend_photo ? htmlspecialchars($friend_photo) : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png'; ?>" class="w-14 h-14 rounded-full border shadow" alt="">
              <div class="flex-1">
                <div class="font-bold text-lg text-blue-700"><?php echo htmlspecialchars($friend_name); ?></div>
                <div class="text-gray-500">Skill: <span class="font-semibold"><?php echo htmlspecialchars($row['skill_title']); ?></span></div>
              </div>
              <a href="chat.php?request_id=<?php echo $row['id']; ?>" class="bg-blue-600 text-white px-5 py-2 rounded-full shadow hover:bg-blue-700 font-bold relative">
                Chat
                <?php if ($unread > 0): ?>
                  <span class="absolute -top-3 -right-4 bg-red-600 text-white rounded-full text-xs px-2 py-0.5 font-bold animate-pulse"><?php echo $unread; ?></span>
                <?php endif; ?>
              </a>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="text-center text-gray-500 py-10 text-xl">You don't have any messages yet.</div>
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

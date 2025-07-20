<?php
session_start();
require 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Unread chat message count for badge
$unread_count = 0;
$unread_res = $conn->query("
    SELECT COUNT(m.id) AS cnt
    FROM messages m
    JOIN requests r ON m.request_id = r.id
    WHERE m.receiver_id = $user_id AND m.is_read = 0 AND r.status = 'accepted'
");
if ($unread_res && $row = $unread_res->fetch_assoc()) {
    $unread_count = $row['cnt'];
}

// Handle delete skill
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $conn->query("DELETE FROM skills WHERE id='$del_id' AND user_id='$user_id'");
    header("Location: profile.php");
    exit();
}
// Handle accept/reject request
if (isset($_GET['accept']) && is_numeric($_GET['accept'])) {
    $rid = intval($_GET['accept']);
    $conn->query("UPDATE requests SET status='accepted' WHERE id='$rid'");
    header("Location: profile.php");
    exit();
}
if (isset($_GET['reject']) && is_numeric($_GET['reject'])) {
    $rid = intval($_GET['reject']);
    $conn->query("UPDATE requests SET status='rejected' WHERE id='$rid'");
    header("Location: profile.php");
    exit();
}

// Fetch user info
$resUser = $conn->query("SELECT * FROM users WHERE id='$user_id' LIMIT 1");
$user = $resUser ? $resUser->fetch_assoc() : null;

// Handle user not found (deleted from DB)
if (!$user) {
    session_destroy();
    header('Location: login.php?error=user_not_found');
    exit();
}

// Fetch user's skills
$resSkills = $conn->query("SELECT * FROM skills WHERE user_id='$user_id' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SkillShare - Profile</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-50 to-teal-50 min-h-screen">
  <!-- Header -->
  <header class="flex items-center justify-between px-6 py-4 bg-white shadow-lg">
    <div class="text-2xl font-bold text-blue-700">SkillShare</div>
    <!-- Desktop Nav -->
    <nav class="space-x-4 hidden md:flex relative">
      <a href="dashboard.php" class="hover:text-blue-500">Home</a>
      <a href="skills.php" class="hover:text-blue-500">Browse Skills</a>
      <a href="post_skill.php" class="hover:text-blue-500">Post Skill</a>
      <a href="profile.php" class="relative text-blue-600 font-semibold">
        Profile
        <?php if ($unread_count > 0): ?>
          <span class="absolute -top-2 -right-3 bg-red-600 text-white rounded-full text-xs px-2 py-0.5 shadow font-bold animate-pulse"><?php echo $unread_count; ?></span>
        <?php endif; ?>
      </a>
    </nav>
    <button id="menu-btn" class="block md:hidden text-2xl text-blue-700 focus:outline-none">&#9776;</button>
  </header>
  <!-- Mobile Menu -->
  <div id="mobile-menu" class="md:hidden bg-white px-6 py-4 space-y-2 hidden shadow-lg">
    <a href="dashboard.php" class="block hover:text-blue-500">Home</a>
    <a href="skills.php" class="block hover:text-blue-500">Browse Skills</a>
    <a href="post_skill.php" class="block hover:text-blue-500">Post Skill</a>
    <a href="profile.php" class="block relative <?php echo $unread_count > 0 ? 'font-bold text-blue-700' : 'font-semibold text-blue-600'; ?>">
      Profile
      <?php if ($unread_count > 0): ?>
        <span class="absolute ml-2 bg-red-600 text-white rounded-full text-xs px-2 py-0.5 shadow font-bold"><?php echo $unread_count; ?></span>
      <?php endif; ?>
    </a>
  </div>

  <main class="max-w-5xl mx-auto py-12 px-4">
    <!-- Profile Card -->
    <div class="bg-white rounded-3xl shadow-xl p-8 flex flex-col md:flex-row items-center gap-8 mb-12">
      <img src="<?php echo isset($user['photo']) && $user['photo'] ? htmlspecialchars($user['photo']) : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png'; ?>" alt="Profile" class="w-32 h-32 rounded-full border-4 border-blue-100 shadow-lg mb-4 md:mb-0">
      <div class="flex-1 text-center md:text-left">
        <h2 class="text-3xl font-extrabold text-blue-800 mb-1"><?php echo htmlspecialchars($user['name']); ?></h2>
        <div class="text-gray-500 mb-2"><?php echo htmlspecialchars($user['location']); ?></div>
        <div class="mb-4"><?php echo htmlspecialchars($user['about']); ?></div>
        <a href="edit_profile.php" class="bg-blue-500 text-white px-4 py-1 rounded-full text-xs shadow hover:bg-blue-600 transition mt-2 inline-block">Edit Profile</a>
      </div>
    </div>

    <!-- Show unread chat message banner -->
    <?php if ($unread_count > 0): ?>
      <div class="mb-8 text-center">
        <span class="bg-red-100 text-red-700 rounded px-3 py-1 font-semibold text-sm shadow">You have <?php echo $unread_count; ?> unread chat message<?php echo $unread_count > 1 ? 's' : ''; ?>!</span>
      </div>
    <?php endif; ?>

    <!-- My Posted Skills -->
    <section>
      <h3 class="text-2xl font-bold text-blue-700 mb-6">My Posted Skills</h3>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8" id="my-skills">
        <?php if ($resSkills && $resSkills->num_rows > 0): ?>
          <?php while ($row = $resSkills->fetch_assoc()): ?>
            <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-col items-center hover:scale-105 transition-transform duration-300 group">
              <img src="<?php echo htmlspecialchars($row['image']); ?>" class="w-16 h-16 mb-3 rounded-full group-hover:rotate-6 transition-transform duration-300" alt="skill">
              <h4 class="font-bold text-lg mb-1"><?php echo htmlspecialchars($row['title']); ?></h4>
              <span class="bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full font-semibold mb-2"><?php echo htmlspecialchars($row['category']); ?></span>
              <div class="text-gray-500 text-sm mb-3 text-center"><?php echo htmlspecialchars($row['description']); ?></div>
              <div class="flex gap-2">
                <a href="profile.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?');" class="bg-red-500 text-white px-4 py-1 rounded-full text-xs shadow hover:bg-red-600 transition">Delete</a>
                <a href="edit_skill.php?id=<?php echo $row['id']; ?>" class="bg-yellow-500 text-white px-4 py-1 rounded-full text-xs shadow hover:bg-yellow-600 transition">Edit</a>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="col-span-3 text-center text-gray-500 py-10 text-xl">You haven't posted any skills yet.</div>
        <?php endif; ?>
      </div>
    </section>

    <!-- Connection Requests Section (Notifications) -->
    <section class="mt-12">
      <h3 class="text-2xl font-bold text-blue-700 mb-6">Connection Requests</h3>
      <div class="space-y-4">
        <?php
        $requests = $conn->query(
          "SELECT r.*, u.name as requester_name, s.title as skill_title 
           FROM requests r 
           JOIN users u ON r.requester_id = u.id 
           JOIN skills s ON r.skill_id = s.id 
           WHERE s.user_id='$user_id' ORDER BY r.created_at DESC"
        );
        if ($requests && $requests->num_rows > 0):
          while ($req = $requests->fetch_assoc()):
        ?>
        <div class="bg-white shadow rounded-xl p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
          <div>
            <span class="font-semibold"><?php echo htmlspecialchars($req['requester_name']); ?></span>
            wants to connect for 
            <span class="text-blue-700 font-bold"><?php echo htmlspecialchars($req['skill_title']); ?></span>
            <span class="ml-2 text-sm text-gray-500">(<?php echo htmlspecialchars($req['message']); ?>)</span>
          </div>
          <div class="flex gap-2">
            <?php if ($req['status'] == 'pending'): ?>
              <a href="profile.php?accept=<?php echo $req['id']; ?>" class="bg-green-500 text-white px-3 py-1 rounded-full text-xs hover:bg-green-600 transition">Accept</a>
              <a href="profile.php?reject=<?php echo $req['id']; ?>" class="bg-red-500 text-white px-3 py-1 rounded-full text-xs hover:bg-red-600 transition">Reject</a>
            <?php elseif ($req['status'] == 'accepted'): ?>
              <a href="chat.php?request_id=<?php echo $req['id']; ?>" class="bg-blue-600 text-white px-3 py-1 rounded-full text-xs hover:bg-blue-700 transition">Chat</a>
              <span class="text-green-600 font-bold text-xs">Accepted</span>
            <?php elseif ($req['status'] == 'rejected'): ?>
              <span class="text-red-600 font-bold text-xs">Rejected</span>
            <?php endif; ?>
          </div>
        </div>
        <?php endwhile; else: ?>
          <div class="text-center text-gray-500 py-10 text-xl">No connection requests yet.</div>
        <?php endif; ?>
      </div>
    </section>

    <!-- Requests You Have Sent Section -->
    <section class="mt-12">
      <h3 class="text-2xl font-bold text-blue-700 mb-6">Requests You Have Sent</h3>
      <div class="space-y-4">
        <?php
        $sent = $conn->query(
          "SELECT r.*, u.name as skill_owner, s.title as skill_title 
           FROM requests r 
           JOIN skills s ON r.skill_id = s.id 
           JOIN users u ON s.user_id = u.id 
           WHERE r.requester_id='$user_id' ORDER BY r.created_at DESC"
        );
        if ($sent && $sent->num_rows > 0):
          while ($req = $sent->fetch_assoc()):
        ?>
        <div class="bg-white shadow rounded-xl p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
          <div>
            For 
            <span class="text-blue-700 font-bold"><?php echo htmlspecialchars($req['skill_title']); ?></span>
            by <span class="font-semibold"><?php echo htmlspecialchars($req['skill_owner']); ?></span>
            <span class="ml-2 text-sm text-gray-500">(<?php echo htmlspecialchars($req['message']); ?>)</span>
          </div>
          <div>
            <?php if ($req['status'] == 'pending'): ?>
              <span class="text-orange-600 font-bold text-xs">Pending</span>
            <?php elseif ($req['status'] == 'accepted'): ?>
              <a href="chat.php?request_id=<?php echo $req['id']; ?>" class="bg-blue-600 text-white px-3 py-1 rounded-full text-xs hover:bg-blue-700 transition">Chat</a>
              <span class="text-green-600 font-bold text-xs">Accepted</span>
            <?php elseif ($req['status'] == 'rejected'): ?>
              <span class="text-red-600 font-bold text-xs">Rejected</span>
            <?php endif; ?>
          </div>
        </div>
        <?php endwhile; else: ?>
          <div class="text-center text-gray-500 py-10 text-xl">No requests sent yet.</div>
        <?php endif; ?>
      </div>
    </section>
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

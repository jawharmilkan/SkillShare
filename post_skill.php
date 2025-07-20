<?php
session_start();
require 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $description = trim($_POST['description']);
    $video_path = null;

    // Handle video upload
    if (!empty($_FILES['video']['name'])) {
        $video_folder = 'uploads/videos/';
        if (!file_exists($video_folder)) {
            mkdir($video_folder, 0777, true); // Create directory if not exists
        }
        $video_name = 'video_' . uniqid() . '.' . pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
        $target_file = $video_folder . $video_name;
        if (move_uploaded_file($_FILES['video']['tmp_name'], $target_file)) {
            $video_path = $target_file;
        } else {
            $msg = "Failed to upload video.";
        }
    }

    // Save to DB
    $stmt = $conn->prepare("INSERT INTO skills (user_id, title, category, description, video) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('issss', $user_id, $title, $category, $description, $video_path);
    if ($stmt->execute()) {
        $msg = "Skill posted successfully!";
    } else {
        $msg = "Error posting skill.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SkillShare - Post Skill</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-50 to-teal-50 min-h-screen">
  <!-- Header -->
  <header class="flex items-center justify-between px-6 py-4 bg-white shadow-lg">
    <div class="text-2xl font-bold text-blue-700">SkillShare</div>
    <nav class="space-x-4 hidden md:flex items-center">
      <a href="dashboard.php" class="hover:text-blue-500">Home</a>
      <a href="skills.php" class="hover:text-blue-500">Browse Skills</a>
      <a href="post_skill.php" class="text-blue-600 font-semibold">Post Skill</a>
      <a href="profile.php" class="hover:text-blue-500">Profile</a>
      <a href="logout.php" class="bg-red-100 text-red-600 font-bold px-5 py-2 rounded-full shadow hover:bg-red-200 transition">Logout</a>
    </nav>
    <button id="menu-btn" class="block md:hidden text-2xl text-blue-700 focus:outline-none">&#9776;</button>
  </header>
  <!-- Mobile Menu -->
  <div id="mobile-menu" class="md:hidden bg-white px-6 py-4 space-y-2 hidden shadow-lg">
    <a href="dashboard.php" class="block hover:text-blue-500">Home</a>
    <a href="skills.php" class="block hover:text-blue-500">Browse Skills</a>
    <a href="post_skill.php" class="block text-blue-600 font-semibold">Post Skill</a>
    <a href="profile.php" class="block hover:text-blue-500">Profile</a>
    <a href="logout.php" class="block bg-red-100 text-red-600 font-bold px-5 py-2 rounded-full shadow hover:bg-red-200 transition">Logout</a>
  </div>

  <main class="max-w-xl mx-auto py-12 px-4">
    <div class="bg-white rounded-3xl shadow-xl p-10 text-center">
      <h2 class="text-3xl font-extrabold text-blue-700 mb-6">Post a New Skill</h2>
      <?php if ($msg): ?>
        <div class="mb-4 font-bold <?php echo (strpos($msg, 'success') !== false) ? 'text-green-600' : 'text-red-600'; ?>">
          <?php echo htmlspecialchars($msg); ?>
        </div>
      <?php endif; ?>
      <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-4 text-left">
        <div>
          <label class="block font-semibold mb-1" for="title">Skill Name</label>
          <input type="text" name="title" id="title" class="w-full px-4 py-2 rounded-xl border border-gray-300 focus:outline-blue-400" required />
        </div>
        <div>
          <label class="block font-semibold mb-1" for="category">Category</label>
          <select name="category" id="category" class="w-full px-4 py-2 rounded-xl border border-gray-300 focus:outline-blue-400" required>
            <option value="">Select Category</option>
            <option value="Design">Design</option>
            <option value="Music">Music</option>
            <option value="Cooking">Cooking</option>
            <option value="IT">IT</option>
            <option value="Tech">Tech</option>
            <option value="Art">Art</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div>
          <label class="block font-semibold mb-1" for="description">Description</label>
          <textarea name="description" id="description" rows="3" class="w-full px-4 py-2 rounded-xl border border-gray-300 focus:outline-blue-400"></textarea>
        </div>
        <div>
          <label class="block font-semibold mb-1">Upload Your Skill Video <span class="text-gray-400 text-xs">(optional)</span></label>
          <input type="file" name="video" accept="video/*" class="w-full block border border-gray-200 rounded-lg px-2 py-1" />
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white font-bold px-4 py-2 rounded-xl shadow hover:bg-blue-700 transition mt-4">
          Submit Skill
        </button>
      </form>
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

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
    $uid = $_SESSION['user_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $category = $conn->real_escape_string($_POST['category']);
    $description = $conn->real_escape_string($_POST['description']);
    $video = null;

    // If user provided a video link
    if (!empty($_POST['video_link'])) {
        $video = $conn->real_escape_string($_POST['video_link']);
    }

    // Or if user uploaded a video file
    if (!empty($_FILES['video_upload']['name'])) {
        $file = $_FILES['video_upload'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['mp4','mov','avi','webm'];
        if (in_array($ext, $allowed) && $file['size'] <= 50*1024*1024) { // 50MB limit
            $filename = uniqid("video_") . '.' . $ext;
            move_uploaded_file($file['tmp_name'], "uploads/videos/$filename");
            $video = "uploads/videos/$filename";
        } else {
            $msg = "Invalid video file type or file too large.";
        }
    }

    // Save to DB if no error
    if (!$msg) {
        $sql = "INSERT INTO skills (user_id, title, category, description, video) VALUES ('$uid', '$title', '$category', '$description', ".($video ? "'$video'" : "NULL").")";
        if ($conn->query($sql)) {
            $msg = "Skill posted successfully!";
        } else {
            $msg = "Error posting skill.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Post a Skill - SkillShare</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-50 to-teal-50 min-h-screen">
  <header class="flex items-center justify-between px-6 py-4 bg-white shadow-lg">
    <div class="text-2xl font-bold text-blue-700">SkillShare</div>
    <nav class="space-x-4 hidden md:flex">
      <a href="dashboard.php" class="hover:text-blue-500">Home</a>
      <a href="skills.php" class="hover:text-blue-500">Browse Skills</a>
      <a href="post_skill.php" class="text-blue-600 font-semibold">Post Skill</a>
      <a href="profile.php" class="hover:text-blue-500">Profile</a>
      <a href="logout.php" class="bg-red-100 text-red-600 font-bold px-5 py-2 rounded-full shadow hover:bg-red-200 transition">Logout</a>
    </nav>
  </header>
  <main class="flex items-center justify-center min-h-[75vh]">
    <div class="bg-white rounded-3xl shadow-xl p-10 max-w-md w-full mx-auto">
      <h1 class="text-3xl font-bold text-blue-700 text-center mb-6">Post a New Skill</h1>
      <?php if ($msg): ?>
        <div class="mb-4 text-center font-semibold text-<?php echo strpos($msg, 'success') ? 'green' : 'red'; ?>-600"><?php echo $msg; ?></div>
      <?php endif; ?>
      <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
          <label class="font-semibold">Skill Name</label>
          <input type="text" name="title" required class="mt-1 w-full px-4 py-2 rounded-xl border border-gray-300 focus:outline-blue-400 shadow-sm"/>
        </div>
        <div>
          <label class="font-semibold">Category</label>
          <select name="category" required class="mt-1 w-full px-4 py-2 rounded-xl border border-gray-300 focus:outline-blue-400 shadow-sm">
            <option value="">Select Category</option>
            <option value="Design">Design</option>
            <option value="Music">Music</option>
            <option value="Cooking">Cooking</option>
            <option value="IT">IT</option>
            <option value="Art">Art</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div>
          <label class="font-semibold">Description</label>
          <textarea name="description" required rows="4" class="mt-1 w-full px-4 py-2 rounded-xl border border-gray-300 focus:outline-blue-400 shadow-sm"></textarea>
        </div>
        <div>
          <label class="font-semibold block mb-1">Upload Your Skill Video <span class="text-gray-500 font-normal">(optional)</span></label>
          <input type="file" name="video_upload" accept="video/*" class="block w-full text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
          <div class="text-center my-2 text-gray-400">OR</div>
          <input type="url" name="video_link" placeholder="Paste a YouTube/Vimeo link" class="w-full px-4 py-2 rounded-xl border border-gray-300 focus:outline-blue-400 shadow-sm"/>
        </div>
        <button class="w-full bg-blue-600 text-white px-4 py-2 rounded-xl font-bold shadow hover:bg-blue-700 transition mt-2">Submit Skill</button>
      </form>
    </div>
  </main>
  <footer class="mt-16 py-8 bg-white text-center shadow-inner text-gray-500">
    &copy; 2025 SkillShare. Built for your local community.
  </footer>
</body>
</html>

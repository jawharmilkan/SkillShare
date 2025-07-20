<?php
session_start();
require 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data
$resUser = $conn->query("SELECT * FROM users WHERE id='$user_id' LIMIT 1");
$user = $resUser ? $resUser->fetch_assoc() : null;

if (!$user) {
    session_destroy();
    header('Location: login.php?error=user_not_found');
    exit();
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $about = trim($_POST['about']);
    $photo = trim($_POST['photo']); // If you want to allow a URL for photo, else skip

    if ($name && $location && $about) {
        $stmt = $conn->prepare("UPDATE users SET name=?, location=?, about=?, photo=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $location, $about, $photo, $user_id);
        $stmt->execute();
        header('Location: profile.php?success=Profile+updated');
        exit();
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Profile - SkillShare</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-50 to-teal-50 min-h-screen">
  <header class="flex items-center justify-between px-6 py-4 bg-white shadow-lg">
    <div class="text-2xl font-bold text-blue-700">SkillShare</div>
    <nav class="space-x-4 hidden md:flex">
      <a href="dashboard.php" class="hover:text-blue-500">Home</a>
      <a href="skills.php" class="hover:text-blue-500">Browse Skills</a>
      <a href="post_skill.php" class="hover:text-blue-500">Post Skill</a>
      <a href="profile.php" class="text-blue-600 font-semibold">Profile</a>
    </nav>
  </header>
  <main class="max-w-xl mx-auto py-12 px-4">
    <div class="bg-white rounded-3xl shadow-xl p-8 mb-12">
      <h2 class="text-3xl font-extrabold text-blue-800 mb-6 text-center">Edit Profile</h2>
      <?php if (!empty($error)): ?>
        <div class="mb-4 text-red-500 font-semibold text-center"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <form method="post">
        <div class="mb-4">
          <label class="block font-bold mb-2" for="name">Name</label>
          <input class="w-full px-4 py-2 border rounded" type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="mb-4">
          <label class="block font-bold mb-2" for="location">Location</label>
          <input class="w-full px-4 py-2 border rounded" type="text" id="location" name="location" value="<?php echo htmlspecialchars($user['location']); ?>" required>
        </div>
        <div class="mb-4">
          <label class="block font-bold mb-2" for="about">About</label>
          <textarea class="w-full px-4 py-2 border rounded" id="about" name="about" rows="4" required><?php echo htmlspecialchars($user['about']); ?></textarea>
        </div>
        <div class="mb-6">
          <label class="block font-bold mb-2" for="photo">Profile Photo URL (optional)</label>
          <input class="w-full px-4 py-2 border rounded" type="text" id="photo" name="photo" value="<?php echo htmlspecialchars($user['photo']); ?>">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-full font-bold hover:bg-blue-700 w-full">Save Changes</button>
      </form>
    </div>
  </main>
</body>
</html>
